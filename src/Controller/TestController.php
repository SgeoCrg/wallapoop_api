<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Dotenv\Dotenv;

class TestController extends AbstractController
{
    #[Route('/test-cors', name:'test_cors')]
    public function testCors(): Response
    {
        $dotenv = new Dotenv();
        $dotenv->loadEnv(dirname(__DIR__).'/../.env');
        $corsAllowOrigin = $_ENV['CORS_ALLOW_ORIGIN'];

        return new Response("CORS_ALLOW_ORIGIN: " . $corsAllowOrigin);
    }
}
