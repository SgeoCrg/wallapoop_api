<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;

class SingleUserController extends AbstractController {

    private $userRepository;
    private $serializer;
    private $security;
    private $user;

    public function __construct(SerializerInterface $serializer, UserRepository $userRepository) {

        $this->userRepository = $userRepository;
        $this->serializer = $serializer;
    }

    #[Route('/api/users/{id}', name: 'user_get', methods:['GET'])]
    public function __invoke(int $id): JsonResponse {
        $user = $this->userRepository->find($id);

        if(!$user) return new Jsonresponse(['error' => 'Prodcut not found'], 404);

        $products = $user->getProducts()->map(function($product) {
            return '/api/products/'.$product->getId();
        })->toArray();

        $formattedUser = [
            'user' =>
                [
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'password' => $user->getPassword(),
                    'products' => $products,
                    'roles' => $user->getRoles(),
                    'lat' => $user->getLat(),
                    'lng' => $user->getLng(),
                    'avatar' =>  $user->getAvatar()
                ]
        ];
        return new JsonResponse($formattedUser);
    }
}
