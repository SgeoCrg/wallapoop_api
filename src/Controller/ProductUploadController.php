<?php

namespace App\Controller;

use App\Entity\Status;
use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
//use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\SerializerInterface;

class ProductUploadController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private Security $security;
    private SerializerInterface $serialize;

    public function __construct(EntityManagerInterface  $entityManager, Security $security, SerializerInterface $serializer) {
        $this->entityManager = $entityManager;
	$this->security = $security;
	$this->serializer = $serializer;
    }

	#[ROUTE('/api/products', name: 'create_product', methods: ['POST'])]
	#[MaxDepth(1)]
    public function __invoke(Request $request)
    {
        //dd($request->files->get('file'));
        $product = new Product();

	error_log('recibido el request'. print_r($request, true));
	ini_set('memory_limit', '512M');
	//parte nueva
	$data = json_decode($request->getContent(), true);
        $jsondata = $request->getContent();

        error_log('json recibido: '. $jsondata);

//        try {
//            $product = $this->serializer->deserialize($jsondata, Product::class, 'json');
//        } catch (NotEncodableValueException $e) {
//            return new JsonResponse(['error' => 'Invalid JSON data: ' . $e->getMessage()],
//                JsonResponse::HTTP_BAD_REQUEST);
//        }

        if(json_last_error() !== JSON_ERROR_NONE) {
            return JsonResponse(['error' => 'Invalid JSON'], JsonResponse::HTTP_BAD_REQUEST);
        }

	$product->setName($data['name'] ?? '')
            ->setPrice($data['price'] ?? 0)
            ->setUpdatedAt(new \DateTimeImmutable());

        if(isset($data['description'])) { $product->setDescription($data['description']); }

        if(isset($data['status'])) { 
	    $statusId =  (int) basename($data['status']);
            $status= $this->entityManager->getRepository(Status::class)->find($statusId);
	     if($status) {
		$product->setStatus($status); 
	     }
	}
	/* PARA DESPUES*/
	//$user = $this->security->getUser();
	error_log('antes de coger user ' . print_r($product, true));
        $user =  $this->getUser();
	if($user instanceOf UserInterface) {/*\App\Entity\User*/
	    $product->setUser($user);
	} else {
	    throw new \Exception('user not logged');
	}
	//$product->setUser($user);

	error_log('persistiendo producto' . print_r($product, true));
	$this->entityManager->persist($product);
	$this->entityManager->flush();
	error_log(print_r($user, true));
/*
        $product->setName($request->request->get('name'))
            ->setPrice($request->request->get('price'))
            ->setUpdatedAt(new \DateTimeImmutable());
        if($request->files->get('imageFile')) $product->setImageFile($request->files->get('imageFile'));
        if($request->request->get('description')) $product->setDescription($request->request->get('description'));
        if($request->request->get('status')) $product->setStatus($request->request->get('status'));
        if($request->request->get('height')) $product->setHeight($request->request->get('height'));
        if($request->request->get('width')) $product->setWidth($request->request->get('width'));
        if($request->request->get('length')) $product->setLength($request->request->get('length'));
	*/
       

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
