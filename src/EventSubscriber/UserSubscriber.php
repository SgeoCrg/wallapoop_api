<?php

namespace App\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Bundle\SecurityBundle\Security;

class UserSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;
    private Security $security;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    public function onUserView(ViewEvent $event): void
    {
        $entity = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();
//return new JsonResponse(['event' => JSON.parse($event, true)]);

        if(!$entity instanceof Product || (Request::METHOD_POST !== $method))
        {
            return;
        }
        $user = $this->security->getUser();
        //$user = $this->entityManager->getRepository(User::class)->find(1);
        if(!$user instanceof User){ throw new \Exception('User not found'); }

        $entity->setUser($user);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['onUserView', EventPriorities::PRE_WRITE],
        ];
    }

}
