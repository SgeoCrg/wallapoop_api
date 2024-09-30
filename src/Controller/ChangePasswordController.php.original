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
        $requestData = json_decode($request->getContent(), true);

        if (isset($requestData['password'])) {
            return $this->changePasswordHandler->updatePassword($data, $requestData['password']);
        }

        if (isset($requestData['name'])) {
            return $this->changePasswordHandler->updateName($data, $requestData['name']);
        }

       // if (isset($requestData['avatar'])) {
       if (isset($files['avatar'])) {
            return $this->changePasswordHandler->updateAvatar($data, $requestData['avatar']);
        //$file = $request->files->get('avatar');
        //if($file instanceof UploadedFile) {
        //    return $this->changePasswordHandler->updateAvatar($data, $file);
        }

        return $data;
        /*if (null === $request->request->get('name')) {
            if (null === $request->request->get('password'))
                return $this->changePasswordHandler->updatePassword($data);

            return $this->changePasswordHandler->updatePassword($request->request->get('password'));
        }

        return $this->changePasswordHandler->updateName($request->request->get('name'));*/
    }
}
