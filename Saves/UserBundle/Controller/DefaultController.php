<?php

namespace APR\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('APRUserBundle:Default:index.html.twig', array('name' => $name));
    }
}
