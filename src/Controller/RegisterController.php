<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Ramsey\Uuid\Uuid;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegisterController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;
    private LoggerInterface $logger;
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $userPasswordHasher;

    private $uploadDirectory;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer, UserRepository $userRepository, 
      LoggerInterface $logger, ParameterBagInterface $params, UserPasswordHasherInterface $userPasswordHasher) {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->userRepository = $userRepository;
        $this->uploadDirectory = $params->get('kernel.project_dir') . '/public/images/avatars';
        $this->logger = $logger;
        $this->userPasswordHasher = $userPasswordHasher;
    }

    #[Route('api/users', name:'register_user', methods: ['POST'])]
    public function __invoke(Request $request)
    {
        $user = new User();

        if($request->headers->get('Content-Type') && strpos($request->headers->get('Content-Type'), 'multipart/form-data') !== false) {
            $data = $request->request->all();
            $files = $request->files->all();
            //$hashedPassword;

            if(!$data) { return new JsonResponse(['error' => 'Invalid Data'], JsonResponse::HTTP_BAD_REQUEST); }

            if(isset($data['password'])) { $hashedPassword = $this->userPasswordHasher->hashPassword($user, $data['password']); }

            $user->setName($data['name'] ?? '')
                ->setPassword($hashedPassword ?? '')
                ->setEmail($data['email'] ?? '')
                ->setLat($data['lat'] ?? '')
                ->setLng($data['lng'] ?? '')
                ->setRoles(["ROLE_USER"])
                ->setUpdatedAt(new \DateTimeImmutable());

            if(isset($files['avatar'])) {
                $probando = $request->files->get('avatar');
                $temporal = $probando->getRealPath();
                $avatarName = $this->generateUniqueName($files['avatar']->getClientOriginalName());

                $image = $files['avatar'];

                $user->setAvatarFile($request->files->get('avatar'));
                $user->setAvatar($avatarName);

                copy($temporal, $this->uploadDirectory . '/' . $avatarName);
            } else {
                if(!isset($files['avatar'])) {
                    $user->setAvatar('c0cab341-d8c6-4c5a-affd-53ad4bee65f0.jpeg');
                } else {
                    return new JsonResponse(['error' => 'Content-Type must be multipart/form-data on IMAGE_SET'], JsonResponse::HTTP_BAD_REQUeST);
                }
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $user;

        } else {
            return new JsonResponse(['error' => 'Content-type must be multipart/form-data on END_FILE'], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    public function generateUniqueName($originalFileName) {
        $uuid = Uuid::uuid4()->toString();
        $extension = pathinfo($originalFileName, PATHINFO_EXTENSION);
        return $uuid . '.' . $extension;
    }

}
