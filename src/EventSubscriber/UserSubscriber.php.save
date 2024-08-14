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

class UserSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function onUserView(ViewEvent $event): void
    {
        $entity = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if(!$entity instanceof Product || (Request::METHOD_POST !== $method))
        {
            return;
        }
        $user = $this->entityManager->getRepository(User::class)->find(1);
        if(!$user) throw new \Exception('User not found');

        $entity->setUser($user);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['onUserView', EventPriorities::PRE_WRITE],
        ];
    }

}
