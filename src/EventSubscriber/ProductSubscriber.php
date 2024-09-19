<?php

namespace App\EventSubscriber;

use App\Entity\Product;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ProductSubscriber implements EventSubscriberInterface
{
    private $tokenStorage;
    private $logger;

    public function __construct(LoggerInterface $logger, TokenStorageInterface $tokenStorage) {
        $this->tokenStorage = $tokenStorage;
        $this->logger = $logger;
    }

    public function onKernelView(ViewEvent $event): void
    {
        $this->logger->info('subscriber is working');
        $controllerResult = $event->getControllerResult();

        if($controllerResult instanceof Product) {
            $this->logger->info('single product');
            $this->setMineFlag($controllerResult);
        } elseif(is_iterable($controllerResult)) {
            foreach($controllerResult as $product) {
                $this->logger->info('iterable products');
                if($product instanceof Product) {
                    $this->setMineFlag($product);
                }
            }
        }
    }

    public function setMineFlag(Product $product): void {
        try {
            $currentUser = $this->tokenStorage->getToken()->getUser();
            $this->logger->info($currentUser);
        } catch(\Exception $e) {
            $this->logger->error('Error retrieving current user: ' . $e->getMessage());
            throw $e;
        }
        $productOwner = $product->getUser();

        $this->logger->info($currentUser, $productOwner);
        if($currentUser && $productOwner && $currentUser->getId() === $productOwner->getId()) {
            $product->setMine(true);
        } else {
            $product->setMine(false);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => 'onKernelView',
        ];
    }
}
