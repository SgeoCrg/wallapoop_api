<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;

class UserController extends AbstractController
{
    private $userRepository;
    private $serializer;
    private $security;
    private $user;

    public function __construct(SerializerInterface $serializer, UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->serializer = $serializer;
    }

    #[Route('/api/users', name: 'user_list', methods: ['GET'])]
    public function __invoke(): JsonResponse
//    public function getUsers() : JasonResponse
    {
        $users = $this->userRepository->findAll();

        $formattedUsers = ['users' => []];

        foreach ($users as $user) {
            $products = $user->getProducts()->map(function($product) {
                return '/api/products/'.$product->getId();
           })->toArray();

            $formattedUsers['users'][] = [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'password' => $user->getPassword(),
                'products' => $products, /* => array_map(function($product) {
                    return [
                         'id' => $product->getId(),
                         'name' => $product->getName(),
                         //'/api/products/'.$product->getId(),
                    ];
                }, $user->getProducts()->toArray()),*/
                'roles' => $user->getRoles(),
                'lat' => $user->getLat(),
                'lng' => $user->getLng(),
                'avatar' => $user->getAvatar()
            ];
        }

        return new JsonResponse($formattedUsers);
    }

}
