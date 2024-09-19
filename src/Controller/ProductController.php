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

class ProductController extends AbstractController
{
    private $productRepository;
    private $serializer;
    private $tokenStorage;
    private $logger;

    public function __construct(ProductRepository $productRepository, SerializerInterface $serializer, TokenStorageInterface $tokenStorage, LoggerInterface $logger)
    {
        $this->productRepository = $productRepository;
        $this->serializer = $serializer;
        $this->tokenStorage = $tokenStorage;
        $this->logger = $logger;
    }

    #[Route('/api/products', name: 'list_products', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        $this->logger->error('arranca ProductCcontroller');
        $products = $this->productRepository->findAll();
        $currentUser = $this->tokenStorage->getToken()->getUser();
        $this->logger->info((string) $currentUser->getId());

        $formattedProducts = ['products' => []];

        foreach($products as $product) {
            $hashtags = $product->getHashtags()->map(function($hashtag) {
                return '/api/hashtags/'.$hashtag->getId(); 
            })->toArray();

            $productOwner = $product->getUser();

            $mine = $currentUser && $productOwner && $currentUser->getId() === $productOwner->getId();

            $formattedProducts['products'][] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'price' => $product->getPrice(),
                'description' => $product->getdescription(),
                'user' => $product->getUser() ? '/api/user/'. $product->getUser()->getId() : null,
                'hashtags' => $hashtags,
                'status' => $product->getStatus() ? '/api/statuses/'. $product->getStatus()->getId() : null,
                'width' => $product->getWidth(),
                'length' => $product->getLength(),
                'height' => $product->getHeight(),
                'imagename' => $product->getImageName(),
                'publicatedAt' => $product->getPublicatedAt(),
                'updatedAt' => $product->getUpdatedAt(),
                'mine' => $mine,
            ];
        }

        //$data =  $this->serializer->normalize($products, null, ['groups' => 'read:product']);
        return new JsonResponse($formattedProducts);
    }

    #[Route('/api/products', name: 'create_product', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        return $this->json(['message' => 'OK']);
    }

}
