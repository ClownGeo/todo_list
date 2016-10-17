<?php

// src/AppBundle/Controller/TodoListIndexController.php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class TodoListIndexController extends Controller
{
    /**
     * @Route("/todo")
     */
    public function indexAction()
    {
        $number = mt_rand(0, 100);

        return $this->render('todo/index.html.twig', array(
            'number' => 213
        ));
    }
}