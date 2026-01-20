<?php

namespace App\Controller;

use Nzo\UrlEncryptorBundle\Annotations\ParamDecryptor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/{id}', name:'app_user_index')]
    #[ParamDecryptor]
    public function index(){
        return $this->render('pages/user/index.html.twig');
    }

}
