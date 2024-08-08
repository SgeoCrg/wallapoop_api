<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ProductController extends AbstractController
{
    private $productRepository;
    private $serializer;

    public function __construct(ProductRepository $productRepository, SerializerInterface $serializer)
    {
        $this->productRepository = $productRepository;
        $this->serializer = $serializer;
    }

    #[Route('/api/products', name: 'list_products', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        $products = $this->productRepository->findAll();
        $data =  $this->serializer->normalize($products, null, ['groups' => 'read:product']);
        return new JsonResponse($data);
    }

    #[Route('/api/products', name: 'create_product', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        return $this->json(['message' => 'OK']);
    }

}
