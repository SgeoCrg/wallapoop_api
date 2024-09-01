<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Status;
use App\Entity\Hashtag;
use App\Repository\ProductRepository;
use App\Repository\StatusRepository;
use App\Repository\HashtagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\SecurityBundle\Security;

class ProductModifyController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ProductRepository $prodcutRepository;
    private Security $security;
    private StatusRepository $statusRepository;
    private HashtagRepository $hashtagRepository;

    public function __construct(EntityManagerInterface $entityManager, ProductRepository $productRepository,
           Security $security, StatusRepository $statusRepository, HashtagRepository $hashtagRepository) {
        $this->entityManager = $entityManager;
        $this->productRepository = $productRepository;
        $this->security = $security;
        $this->statusRepository = $statusRepository;
        $this->hashtagRepository = $hashtagRepository;
    }

    #[Route('api/products/{id}', name: 'product_update', methods: ['PATCH'])]
    #[MaxDepth(1)]
    public function __invoke(Request $request, SerializerInterface $serializer, int $id) //: Product
    {
        $product = $this->productRepository->find($id);
        $user = $this->security->getUser();

        if(!$product) return new JsonResponse(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);

        if ($user && $user === $product->getUser()) {

            $data = json_decode($request->getContent(), true);

            if(!$data) return new JsonResponse(['error' => 'no data']);

            if(isset($data['name'])) $product->setName($data['name']);
            if(isset($data['price'])) $product->setPrice($data['price']);
            if(isset($data['description'])) $product->setDescription($data['description']);

            if(isset($data['status'])) {
                $statusId = $data['status'];
                $status = $this->statusRepository->find($statusId);

                if($status) $product->setStatus($status);
            }

            if(isset($data['hashtags'])) {
                $hashtagArray = $data['hashtags'];
                $hashData = array_filter(explode('#', $hashtagArray));

                $this->updateHashtags($product, $hashData);

            }

            if(isset($data['width'])) $product->setWidth($data['width']);
            if(isset($data['length'])) $product->setLength($data['length']);
            if(isset($data['height'])) $product->setHeight($data['height']);

            $this->entityManager->persist($product);
            $this->entityManager->flush();
        }
        return $product;
    }

    private function updateHashtags(Product $product, array $hashtags) {
        $productHashtags = $product->getHashtags();
        $productHashtagNames = array_map(fn($hashtag) => $hashtag->getHashtag(), $productHashtags->toArray());

        foreach($hashtags as $hashtagName) {

            $hashtagName = trim($hashtagName);
            if(strpos($hashtagName, "#") === 0) $hashtagName = substr($hashtagName, 1);

            if(!in_array($hashtagName, $productHashtagNames)) {
                $hashtag = $this->hashtagRepository->findOneBy(['hashtag' => $hashtagName]);
                if(!$hashtag) {
                    $hashtag = new Hashtag();
                    $hashtag->setName($hashtagName);
                    $this->entityManager->persist($hashtag);
                }
                $product->addHashtag($hashtag);
                $hashtag->addProduct($product);
            }
        }
    }


    #[Route('/product/modify', name: 'app_product_modify')]
    public function index(): Response
    {
        return $this->render('product_modify/index.html.twig', [
            'controller_name' => 'ProductModifyController',
        ]);
    }
}
