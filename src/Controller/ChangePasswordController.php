<?php

namespace App\Controller;

use App\Handler\ChangePasswordHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class ChangePasswordController extends AbstractController
{

    public function __construct(public ChangePasswordHandler $changePasswordHandler)
    {
    }

    public function __invoke(Request $request, $data)
    {
        if(null === $request->request->get('password'))
            return $this->changePasswordHandler->updatePassword($data);

        return $this->changePasswordHandler->updatePassword($request->request->get('password'));
    }
}
