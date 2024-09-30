<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\UserController;
use App\Controller\SingleUserController;
use App\Controller\ChangePasswordController;
use App\Controller\RegisterController;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
//use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            controller: UserController::class,
            normalizationContext: ['groups' => ['read:user']],
            denormalizationContext: ['groups' => ['read:user']],
            deserialize: false
        ),
        new Get(
            controller: SingleUserController::class,
            normalizationContext: ['groups' => ['read:user']],
            denormalizationContext: ['groups' => ['read:user']],
            deserialize: false
        ),
        new Post(
            uriTemplate: 'users',
            controller: RegisterController::class,
            normalizationContext: ['groups' => ['post:user']],
            denormalizationContext: ['groups' => ['post:user']],
            name: 'user_post',
            deserialize: false
        ),
        new Put(
            uriTemplate: 'users/{id}',
            controller: ChangePasswordController::class,
            normalizationContext: ['groups' => ['patch:user']],
            denormalizationContext: ['groups' => ['patch:user']],
            validate: false,
            name: 'user_update',
            deserialize: false
        ),
        new Delete()
    ],
    normalizationContext: ['groups' => ['read:user']],
    denormalizationContext: ['groups' => ['write:user']]
)]
#[Vich\Uploadable]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['write:user','read:user'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['write:user','read:user', 'post:user', 'patch:user'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['write:user','read:user','post:user'])]
    private ?string $email = null;

    /**
     * @var Collection<int, Product>
     */
    #[ORM\OneToMany(targetEntity: Product::class, mappedBy: 'user', orphanRemoval: true)]
    #[Groups(['read:user'])]
    #[MaxDepth(2)]
    private Collection $products;

    #[ORM\Column]
    #[Assert\NotBlank(groups: ['write:user','post:user','patch:user'])]
    #[Assert\Length(
        min: 4,
        max: 20,
        minMessage: 'Your password must be {{ limit }} chars long',
        maxMessage: 'Your password cannot be longer than {{ limit }} characters',
        groups: ['write:user','patch']
    )]
    #[Groups(['write:user','post:user','read:user','patch:user'])]
    private ?string $password = null;

    #[ORM\Column(type: 'json')]
    #[Groups(['read:user'])]
    private $roles = [];

    #[ORM\Column(nullable: true)]
    #[Groups(['write:user','post:user','read:user'])]
    private ?float $lat = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['write:user','post:user','read:user'])]
    private ?float $lng = null;

    #[ORM\Column(nullable:true)]
    #[Groups(['write:user', 'post:user', 'read:user', 'patch:user'])]
    private ?string $avatar = null;

    #[Vich\UploadableField(mapping: 'user_avatar', fileNameProperty: 'avatar')]
    #[Groups(['write:user', 'post:user', 'patch:user'])]
    private ?File $avatarFile = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function setAvatarFile(?File $imageFile = null): static
    {
        $this->avatarFile = $imageFile;

        if(null !== $imageFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getAvatarFile(): ?File 
    {
        return $this->avatarFile;
    }

    public function setAvatar(?string $imageName): void
    {
        $this->avatar = $imageName;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function __construct()
    {
        $this->products = new ArrayCollection();
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            if($product->getUser() !== $this)
               $product->setUser($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getUser() === $this) {
                $product->setUser(null);
            }
        }

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getRoles(): array
    {
        //return $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getSalt(): ?string
    {
        // No necesitas un salt si usas bcrypt o sodium
        return null;
    }

    public function eraseCredentials(): void
    {
        // Si guardas datos temporales sensibles en el usuario, límpialos aquí
    }

    // Método requerido por PasswordAuthenticatedUserInterface
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getLat(): ?float
    {
        return $this->lat;
    }

    public function setLat(?float $lat): static
    {
        $this->lat = $lat;

        return $this;
    }

    public function getLng(): ?float
    {
        return $this->lng;
    }

    public function setLng(?float $lng): static
    {
        $this->lng = $lng;

        return $this;
    }

}
