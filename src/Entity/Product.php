<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Controller\ProductController;
use App\Controller\ProductModifyController;
use App\Controller\ProductUploadController;
use App\Controller\SingleProductController;
use App\Entity\Traits\CommonDate;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            controller: ProductController::class,
            deserialize: false,
        ),
        new Get(
            controller: SingleProductController::class,
            deserialize: false,
        ),
        new Post(
            controller: ProductUploadController::class,
            deserialize: false,
        ),
        new Patch(
            controller: ProductModifyController::class,
            deserialize: false,
),
        new Delete()
    ],
    normalizationContext: ['groups' => ['read:product']],
    denormalizationContext: ['groups' => ['write:product']],
)]
#[ORM\HasLifecycleCallbacks]
#[Vich\Uploadable]
#[ApiFilter(SearchFilter::class, properties: [
    'name'=> 'partial',
    'status' => 'exact'
])]
#[ApiFilter(DateFilter::class, properties: ['publicatedAt'])]
#[ApiFilter(OrderFilter::class, properties: ['publicatedAt' => 'DESC'])]
class Product
{
    use CommonDate;
    private $security;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('read:product')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank()]
    #[Assert\Length(
        min : 5,
        minMessage : 'Name must be {{ limit }} chars long',
    )]
    #[Groups(['write:product','read:product'])]
    private ?string $name = null;

    #[ORM\Column]
    #[Assert\GreaterThan(0)]
    #[Groups(['write:product','read:product'])]
    private ?int $price = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['write:product','read:product'])]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['read:product'])]
    #[MaxDepth(2)]
    private ?User $user = null;

    /**
     * @var Collection<int, Hashtag>
     */
    #[ORM\ManyToMany(targetEntity: Hashtag::class, inversedBy: 'products')]
    #[Groups(['write:product', 'read:product'])]
    #[MaxDepth(2)]
    private Collection $hashtags;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[Groups(['write:product', 'read:product'])]
    #[MaxDepth(2)]
    private ?Status $status = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['write:product', 'read:product'])]
    private ?int $width = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['write:product', 'read:product'])]
    private ?int $length = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['write:product', 'read:product'])]
    private ?int $height = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['read:product'])]
    private ?string $imageName = null;

    #[Vich\UploadableField(mapping: 'product_images', fileNameProperty: 'imageName')]
    #[Groups(['write:product'])]
    private ?File $imageFile = null;

    #[Groups(['read:product'])]
    private ?bool $mine = false;

    public function getMine(): bool {
        return $this->mine;
    }

    public function setMine(bool $mine): bool {
        $this->mine = mine;
    }

    public function __construct() {
        $this->hashtags = new ArrayCollection();
        $this->mine = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        if($this->user !== $user)
           $this->user = $user;

           if($user !== null && !$user->getProducts()->contains($this)) 
               $user->addProduct($this);

        return $this;
    }

    /**
     * @return Collection<int, Hashtag>
     */
    public function getHashtags(): Collection
    {
        return $this->hashtags;
    }

    public function addHashtag(Hashtag $hashtag): static
    {
        if (!$this->hashtags->contains($hashtag)) {
            $this->hashtags->add($hashtag);

            if(!$hashtag->getProducts()->contains($this))
                $hashtag->addProduct($this);
        }

        return $this;
    }

    public function removeHashtag(Hashtag $hashtag): static
    {
        $this->hashtags->removeElement($hashtag);

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): static
    {
         if($this->status !== $status) 
             $this->status = $status;

             if($status !== null && !$status->getProducts()->contains($this)) {
                 $status->addProduct($this);
             }

        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(?int $width): static
    {
        $this->width = $width;

        return $this;
    }

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function setLength(?int $length): static
    {
        $this->length = $length;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(?int $height): static
    {
        $this->height = $height;

        return $this;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|null $imageFile
     * @return Product
     */
    public function setImageFile(?File $imageFile = null): static
    {
        $this->imageFile = $imageFile;

        if(null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }

}
