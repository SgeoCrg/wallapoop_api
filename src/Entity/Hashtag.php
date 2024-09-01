<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\HashtagRepository;
use App\Controller\HashtagModifyController;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\Entity(repositoryClass: HashtagRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['read:hashtag']],
    denormalizationContext: ['groups' => ['write:hashtag']]
)]
#[Get()]
#[GetCollection()]
#[Post(
    normalizationContext: ['groups' => ['read:hashtag']],
    denormalizationContext: ['groups' => ['write:hashtag']]
)]
#[Delete(
    security: "is_granted('ROLE_ADMIN')"
)]
#[Patch(
   // controller: HashtagModifyController::class,
    denormalizationContext: ['groups' => ['write:hashtag']]
)]
#[ApiFilter(SearchFilter::class, properties: [
    'hashtag' => 'exact'
])]
class Hashtag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['write:hashtag','read:hashtag'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['write:hashtag','read:hashtag'])]
    private ?string $hashtag = null;

    /**
     * @var Collection<int, Product>
     */
    #[ORM\ManyToMany(targetEntity: Product::class, mappedBy: 'hashtags')]
    #[Groups(['read:hashtag'])]
    #[MaxDepth(1)]
    private Collection $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHashtag(): ?string
    {
        return $this->hashtag;
    }

    public function setHashtag(string $hashtag): static
    {
        $this->hashtag = $hashtag;

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
            $product->addHashtag($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        if ($this->products->removeElement($product)) {
            $product->removeHashtag($this);
        }

        return $this;
    }

}
