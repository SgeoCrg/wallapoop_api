<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

trait CommonDate
{
    #[ORM\Column(nullable: true)]
    #[Groups(['read'])]
    private ?\DateTimeImmutable $publicatedAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getPublicatedAt(): ?\DateTimeImmutable
    {
        return $this->publicatedAt;
    }

    public function setPublicatedAt(?\DateTimeImmutable $publicatedAt): static
    {
        $this->publicatedAt = $publicatedAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updateDate(): void
    {
        if($this->getPublicatedAt() == null)
        {
            $this->setPublicatedAt(new \DateTimeImmutable());
        }
        $this->setUpdatedAt(new \DateTimeImmutable());
    }

}
