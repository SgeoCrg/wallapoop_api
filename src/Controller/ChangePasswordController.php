<?php

namespace App\Controller;

use App\Controller\RegisterController;
use App\Handler\ChangePasswordHandler;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ChangePasswordController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;
    private UserPasswordHasherInterface $userPasswordHasher;
    private RegisterController $registerController;

    private $uploadDirectory;

    public function __construct(public ChangePasswordHandler $changePasswordHandler, EntityManagerInterface $entityManager, SerializerInterface $serializer, UserPasswordHasherInterface $userPasswordHasher,
      ParameterBagInterface $params, RegisterController $registerController)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->uploadDirectory = $params->get('kernel.project_dir') . '/public/images/avatars';
        $this->registerController = $registerController;
    }

    #[Route('api/users/{id}', name:'user_update', methods: ['POST'])]
    public function __invoke(Request $request, User $data)
    {
        //dump($request);
        //if(!$data) { return new JsonResponse(['error' => 'User not found'], JsonResponse::HTTP_BAD_REQUEST); } else { return new JsonResponse(['user' => $data->getName()]); }

        if($request->headers->get('Content-Type') && strpos($request->headers->get('Content-Type'), 'multipart/form-data') !== false) {
            $newUserData = $request->request->all();
            $files = $request->files->all();
            $name = $request->request->get('name');
            $oldPassword = $request->request->get('oldPassword');
            $password = $request->request->get('password');
            //if(empty($newUserData) && empty($files)) { return new JsonResponse(['error' => 'empty newUserData', $request->files->all()], JsonResponse::HTTP_BAD_REQUEST);  }

            //if(!$newUserData) { return new JsonResponse(['error' => 'Invalid data',$request, $files], JsonResponse::HTTP_BAD_REQUEST); }

            if ($oldPassword && $password) {
                if(!$this->userPasswordHasher->isPasswordValid($data, $oldPassword)) {
                    return new JsonResponse(['error' => 'old pasword is incorrect']);
                }
                $hashedPassword = $this->userPasswordHasher->hashPassword($data, $password);
                $data->setPassword($hashedPassword);
            }

            if($name) { // if (isset($newUserData['name'])) {
                //return $this->changePasswordHandler->updateName($data, $newUserData['name']);
                $data->setName($newUserData['name']);
            }

            if (isset($files['avatar']) && $files['avatar'] instanceof UploadedFile) {
                $probando = $request->files->get('avatar');
                $temporal = $probando->getRealPath();
                $avatarName = $this->registerController->generateUniqueName($files['avatar']->getClientOriginalName());

                $image = $files['avatar'];

                $data->setAvatarFile($request->files->get('avatar'));
                $data->setAvatar($avatarName);

                copy($temporal, $this->uploadDirectory . '/' . $avatarName);
            }

           $this->entityManager->persist($data);
           $this->entityManager->flush();

           
        $formattedUser = [
            'user' =>
                [
                    'id' => $data->getId(),
                    'name' => $data->getName(),
                    'email' => $data->getEmail(),
                    'password' => $data->getPassword(),
                    'products' => $data->getProducts()->map(function($product) {return '/api/products/'.$product->getId(); })->toArray(),
                    'roles' => $data->getRoles(),
                    'lat' => $data->getLat(),
                    'lng' => $data->getLng(),
                    'avatar' =>  $data->getAvatar()
                ]
        ];
        return new JsonResponse($formattedUser);

        } else {
            return new JsonResponse(['error' => 'Content-Type must be multipart/form-data on END FILE'], JsonResponse::HTTP_BAD_REQUEST);
        }
    }
}
