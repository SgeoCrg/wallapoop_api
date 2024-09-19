<?php

namespace App\Controller;

use App\Entity\Product;
use Psr\Log\LoggerInterface;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SingleProductController extends AbstractController
{
    private $productRepository;
    private $serializer;
    private $logger;
    private $tokenStorage;

    public function __construct(ProductRepository $productRepository, SerializerInterface $serializer, LoggerInterface $logger, TokenStorageInterface $tokenStorage)
    {
        $this->productRepository = $productRepository;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->tokenStorage = $tokenStorage;
    }

    #[Route('/api/products/{id}', name: 'product_get', methods:['GET'])]
    public function __invoke(int $id): JsonResponse {
        $product = $this->productRepository->find($id);

        $currentUser = $this->tokenStorage->getToken()->getUser();

        if(!$product) {
            return new JsonResponse(['error' => 'Product not found'], 404);
        }
        $productOwner = $product->getUser();
        $mine = $currentUser && $productOwner && $currentUser->getId() === $productOwner->getId();

        $formattedProduct = [
            'product' => 
                [
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'price' => $product->getPrice(),
                    'description' => $product->getDescription(),
                    'user' => $product->getUser() ? '/api/user/'. $product->getUser()->getId() : null,
                    'status' => $product->getStatus() ? '/api/statuses/'. $product->getStatus()->getId() : null,
                    'hashtags' => $product->getHashtags()->map(function($hashtag) {
                        return '/api/hashtags/'. $hashtag->getId();
                    })->toArray(),
                    'width' => $product->getWidth(),
                    'length' => $product->getLength(),
                    'height' => $product->getHeight(),
                    'imagename' => $product->getImageName(),
                    'mine' => $mine,
                ]
        ];

        return new JsonResponse($formattedProduct);
    }
}
