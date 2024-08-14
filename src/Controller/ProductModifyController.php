<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

class ProductModifyController extends AbstractController
{
    private $entityManager;
    private $prodcutRepository;

    public function __construct(EntityManagerInterface $entityManager, ProductRepository $productRepository) {
        $this->entityManager = $entityManager;
        $this->productRepository = $productRepository;
    }

    #[Route('api/products/{id}', name: 'product_update', methods: ['PATCH'])]
    public function __invoke(Request $request, int $id) //: Product
    {

        $product = $this->productRepository->find($id);

        if(!$product) return new JsonResponse(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);

        $contentType = $request->headers->get('Content-Type');

        $data = null;

        if($contentType && strpos($contentType, 'multipart/form-data') !== false) {
return new JsonResponse(['message' => $request]);
            $data = $request->request->all();
            $files = $request->files->all();
        } else {
            $data = json_decode($request->getContent(), true);
            if(json_last_error() !== JSON_ERROR_NONE) return new JsonResponse(['error' => 'Invalida json: ' . json_last_error_msg()], response::HTTP_BAD_REQUEST);
        }
            if(!$data) return new JsonResponse(['error' => 'no data']);

            if(isset($data['name'])) $product->setName($data['name']);
/*        if($request->request->get('price')) $product->setPrice($request->request->get('price'));
        if($request->files->get('imageFile')) $product->setImageFile($request->files->get('imageFile'));
        if($request->request->get('description')) $product->setDescription($request->request->get('description'));
        if($request->request->get('status')) $product->setStatus($request->request->get('status'));
        if($request->request->get('height')) $product->setHeight($request->request->get('height'));
        if($request->request->get('width')) $product->setWidth($request->request->get('width'));
        if($request->request->get('length')) $product->setLength($request->request->get('length'));
*/
            $this->entityManager->persist($product);
            $this->entityManager->flush();

        return $product;
    }

    #[Route('/product/modify', name: 'app_product_modify')]
    public function index(): Response
    {
        return $this->render('product_modify/index.html.twig', [
            'controller_name' => 'ProductModifyController',
        ]);
    }
}
