<?php

namespace App\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ChangePasswordHandler
{
    public function __construct(private UserPasswordHasherInterface $userPasswordHasher,
    private EntityManagerInterface $entityManager)
    {
    }

    public function updatePassword($data, $plainPassword) //añado password
    {
        $hashedPassword = $this->userPasswordHasher->hashPassword(
            $data,
            $plainPassword
        );
        $data->setPassword($hashedPassword);
        $this->entityManager->flush();

        /*$hashedPassword = $this->userPasswordHasher->hashPassword(
            $data,
            $data->getPassword()
        );
        $data->setPassword($hashedPassword);*/
        return $data;
    }

    public function updateName($data, $name)
    {
        $data->setName($name);
        $this->entityManager->flush();
        return $data;
    }
}