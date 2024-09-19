<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Repository\UserRepository;

class CurrentUserController extends AbstractController
{
    private $userRepository;
    private $tokenStorage;

    public function __construct(UserRepository $userRepository, TokenStorageInterface $tokenStorage)
    {
        $this->userRepository = $userRepository;
        $this->tokenStorage = $tokenStorage;
    }

    #[Route('/api/users/me', name: 'user_me', methods:['GET'])]
    public function __invoke(): JsonResponse
    {
        $currentUser = $this->tokenStorage->getToken()->getUser();

        if (!$currentUser) {
            return new JsonResponse(['error' => 'User not authenticated'], 401);
        }

        $user = $this->userRepository->find($currentUser->getId());

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $formattedUser = [
            'user' => [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'products' => $user->getProducts()->map(function($product) {
                    return '/api/products/'. $product->getId();
                })->toArray(),
                'roles' => $user->getRoles()
            ]
        ];

        return new JsonResponse($formattedUser);
    }
}
