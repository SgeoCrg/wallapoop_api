<?php

namespace App\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordSubscriber implements EventSubscriberInterface
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {

    }

    public function hashPassword(ViewEvent $event): void
    {
        $entity = $event->getControllerResult();

        $method = $event->getRequest()->getMethod();

        if(!$entity instanceof User || !(in_array($method, [Request::METHOD_POST, Request::METHOD_PATCH]))) return;

        $hashedPassword = $this->hasher->hashPassword($entity, $entity->getPassword());

        $entity->setPassword($hashedPassword);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['hashPassword', EventPriorities::PRE_WRITE],
        ];
    }
}
