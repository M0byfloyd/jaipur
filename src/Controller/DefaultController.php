<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="default")
     */
    public function index()
    {
        return $this->render('default/index.html.twig', [
            'controller_name' => 'DefaultController',
            'joueurConnected' => $this->getUser(),

        ]);
    }

    /**
     * @Route("/admin", name="admin_index")
     */
    public function admin()
    {
        return $this->render('admin/index.html.twig', [
                'joueurConnected' => $this->getUser(),
            ]
        );
    }
}
