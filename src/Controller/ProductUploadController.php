<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Status;
use App\Entity\Product;
use App\Entity\Hashtag;
use App\Repository\StatusRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Ramsey\Uuid\Uuid;

class ProductUploadController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private Security $security;
    private SerializerInterface $serialize;
    private LoggerInterface $logger;
    private StatusRepository $statusRepository;
    private UserRepository $userRepository;
    private ProductRepository $productRepository;

    private $uploadDirectory;

    public function __construct(EntityManagerInterface  $entityManager, Security $security, SerializerInterface $serializer, LoggerInterface $logger, 
            StatusRepository $statusRepository, UserRepository $userRepository, ProductRepository $productRepository, ParameterBagInterface $params) {
        $this->entityManager = $entityManager;
	$this->security = $security;
	$this->serializer = $serializer;
        $this->logger = $logger;
        $this->statusRepository = $statusRepository;
        $this->userRepository = $userRepository;
        $this->productRepository = $productRepository;
        $this->uploadDirectory = $params->get('kernel.project_dir') . '/public/images/products';
    }

	#[ROUTE('/api/products', name: 'create_product', methods: ['POST'])]
	#[MaxDepth(1)]
    public function __invoke(Request $request)
    {
        $product = new Product();

	//parte nueva nueva

        if($request->headers->get('Content-Type') && strpos($request->headers->get('Content-Type'), 'multipart/form-data') !== false) {
           $data = $request->request->all();
           $files = $request->files->all();

           if(!$data) { return new JsonResponse(['error' => 'Invalid data'], JsonResponse::HTTP_BAD_REQUEST); }

	$product->setName($data['name'] ?? '')
            ->setPrice($data['price'] ?? 0)
            ->setUpdatedAt(new \DateTimeImmutable());

        if(isset($data['description'])) { $product->setDescription($data['description']); }

        if(isset($data['hashtag'])) {
            $hashtagArray = $data['hashtag'];
            $hashData = array_filter(explode('#', $hashtagArray));

            foreach($hashData as $hashtagName) {
                $hashtagName = trim($hashtagName);

                if($hashtagName) {
                    if(strpos($hashtagName, '#') === 0) {
                        $hashtagName = substr($hashtagName, 1);
                    }

                    $hashtag = $this->entityManager->getRepository(Hashtag::class)->findOneBy(['hashtag' => $hashtagName]);

                    if(!$hashtag) {
                        $hashtag = new Hashtag();
                        $hashtag->setHashtag($hashtagName);
                        $this->entityManager->persist($hashtag);
                    }

                    $product->addHashtag($hashtag);
                    $hashtag->addProduct($product);
                }
            }
        }

        if(isset($data['status'])) { 
	    //$statusId =  (int) basename($data['status']);
            //$status= $this->entityManager->getRepository(Status::class)->find($statusId);
            $statusId = $data['status'];
            $status = $this->statusRepository->find($statusId);
	    if(!$status) {
                return $this->json(['error' => 'Invalid status id']);
            }
            $product->setStatus($status);
	}

        if(isset($data['height'])) { $product->setHeight($data['height']); }
        if(isset($data['width'])) { $product->setWidth($data['width']); }
        if(isset($data['length'])) { $product->setLength($data['length']); }

        if(isset($files['imageFile'])) {

            $probando = $request->files->get('imageFile'); 
            $temporal = $probando->getRealPath();
            $fileName = $this->generateUniqueName($files['imageFile']->getClientOriginalName());

            $image = $files['imageFile'];

            $product->setImageFile($request->files->get('imageFile'));
            //$product->setImageName($image->getClientOriginalName());
            $product->setImageName($fileName);

            copy($temporal, $this->uploadDirectory . '/' . $fileName); //$image->getClientOriginalName());
        } else {
            if(!isset($files['imageFile'])) {
                $product->setImageFile(null);
            } else {
                return new JsonResponse(['error' => 'Content-type must be multipart/form-data on IMAGE_SET'], JsonResponse::HTTP_BAD_REQUEST);
            }
        }

	$user = $this->security->getUser();

        $product->setUser($user);

            $this->entityManager->persist($product);
	    $this->entityManager->flush();

/*
        if($request->files->get('imageFile')) {
            return $this->json(['error' => $request->files->get('imageFile')]);
            $product->setImageFile($request->files->get('imageFile'));
        }*/

        return $product;
        } else {
            return new JsonResponse(['error' => 'COntent-Type must be multipart/form-data on END_FILE'], JsonResponse::HTTP_BAD_REQUEST);
        }

    }

    #[Route('/photo/upload', name: 'app_photo_upload')]
    public function index(): Response
    {
        return $this->render('photo_upload/index.html.twig', [
            'controller_name' => 'ProductUploadController',
        ]);
    }

    public function generateUniqueName($originalFileName) {
        $uuid = Uuid::uuid4()->toString();
        $extension = pathinfo($originalFileName, PATHINFO_EXTENSION);
        return $uuid . '.' . $extension;
    }
}
