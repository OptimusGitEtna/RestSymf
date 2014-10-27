<?php

namespace Acme\DemoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class WelcomeController extends Controller
{
    public function indexAction()
    {

        $response = new Response('Content', 200, array('content-type' => 'text/html'));

        return $response;
    }
}
