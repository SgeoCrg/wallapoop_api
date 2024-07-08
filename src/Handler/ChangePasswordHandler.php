<?php

namespace App\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ChangePasswordHandler
{
    public function __construct(private UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entity)
    {
    }

    public function updatePassword($data)
    {
        $hashedPassword = $this->userPasswordHasher->hashPassword(
            $data,
            $data->getPassword()
        );
        $data->setPassword($hashedPassword);
        return $data;
    }
}