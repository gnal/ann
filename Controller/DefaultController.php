<?php

namespace Abc\AnnBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('AbcAnnBundle:Default:index.html.twig');
    }
}
