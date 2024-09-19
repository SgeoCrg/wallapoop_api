<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class productController extends AbstractController
{
    private $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    #[Route('/api/products', name: 'product_list', methods: ['GET'])]
    public function listProducts(): JsonResponse
    {
        $products = $this->productRepository->findAll();

        $productsArray = [];
            foreach ($products as $product) {
                $productsArray[] = [
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'price' => $product->getPrice(),
                    'description' => $product->getDescription(),
                    'user' => $product->getUser()->getId(),
                    'hashtags' => $product->getHashtags(), // Convierte esto en un array si es necesario
                    'status' => $product->getStatus()->getId(),
                    'width' => $product->getWidth(),
                    'length' => $product->getLength(),
                    'height' => $product->getHeight(),
                    'imageName' => $product->getImageName(),
                ];

        return new JsonResponse(['products' => $productsArray]);
    }

    #[Route('/api/products', name: 'create_product', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        return $this->json(['message' => 'OK']);
    }

}
