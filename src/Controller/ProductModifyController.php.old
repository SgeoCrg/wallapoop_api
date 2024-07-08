<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductModifyController extends AbstractController
{
    public function __invoke(Request $request): Product
    {
        //dd($request->files->get('file'));
        $product = ProductRepository::class->get($request->request->get('id'));
        dump($product);
        if($request->request->get('name')) $product->setName($request->request->get('name'));
        if($request->request->get('price')) $product->setPrice($request->request->get('price'));
        if($request->files->get('imageFile')) $product->setImageFile($request->files->get('imageFile'));
        if($request->request->get('description')) $product->setDescription($request->request->get('description'));
        if($request->request->get('status')) $product->setStatus($request->request->get('status'));
        if($request->request->get('height')) $product->setHeight($request->request->get('height'));
        if($request->request->get('width')) $product->setWidth($request->request->get('width'));
        if($request->request->get('length')) $product->setLength($request->request->get('length'));

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
