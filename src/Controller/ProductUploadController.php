<?php

namespace App\Controller;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductUploadController extends AbstractController
{
    public function __invoke(Request $request): Product
    {
        //dd($request->files->get('file'));
        $product = new Product();

        $product->setName($request->request->get('name'))
            ->setPrice($request->request->get('price'))
            ->setUpdatedAt(new \DateTimeImmutable());
        if($request->files->get('imageFile')) $product->setImageFile($request->files->get('imageFile'));
        if($request->request->get('description')) $product->setDescription($request->request->get('description'));
        if($request->request->get('status')) $product->setStatus($request->request->get('status'));
        if($request->request->get('height')) $product->setHeight($request->request->get('height'));
        if($request->request->get('width')) $product->setWidth($request->request->get('width'));
        if($request->request->get('length')) $product->setLength($request->request->get('length'));

        return $product;
    }

    #[Route('/photo/upload', name: 'app_photo_upload')]
    public function index(): Response
    {
        return $this->render('photo_upload/index.html.twig', [
            'controller_name' => 'ProductUploadController',
        ]);
    }
}
