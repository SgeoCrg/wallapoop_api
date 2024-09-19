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

    public function updateAvatar($data, $avatar)
    {
        $probando = $avatar->files->get('avatar');
        $temporal = $probando->getRealPath();
        $fileName = $this->generateUniqueName($avatar['avatar']->getClientOriginalName());

        $image = $avatar['avatar'];
        $data->setAvatar($avatar->files->get('avatar'));
        $data->setAvatarName($fileName);

        copy($temporal, $this->uploadDirectory . '/' . $fileName);

        $this->entityManager->flush();

        return $data;
    }

}
