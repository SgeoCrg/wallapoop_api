<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\HashtagRepository;
use Component\Routing\Annotation\Route;

class HashtagController extends AbstractController {

    private $repository;

    public function __construct(HashtagRepository $repository) {
        $this->repository = $repository;
    }

    #[Route('/api/hashtags', name: 'list_hashtags', methods:['GET'])]
    public function __invoke(): JsonResponse {

        $hashtags = $this->repository->findAll();

        $data =  [ 
            'hashtags' => []
        ];

        foreach($hashtags as $hashtag) {
            $data['hashtags'][] = [
                'id' => $hashtag->getId(),
                'hashtag' => $hashtag->getHashtag(),
                'products' => array_map(function ($product) {
                    return [
                       'id' => $product->getId(),
                       'name' => $product->getname(),
                    ];
                }, $hashtag->getProducts()->toArray())
            ];
        }

        return new JsonResponse($data);
    }

}
