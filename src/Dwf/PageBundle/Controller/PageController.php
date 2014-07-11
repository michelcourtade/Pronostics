<?php

namespace Dwf\PageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class PageController extends Controller
{
    public function indexAction($id)
    {
        $repository = $this->getDoctrine()
                            ->GetManager()
                            ->getRepository('DwfPageBundle:Page');
        
        $page = $repository->find($id);
        
        if($page === null)
            throw $this->createNotFoundException('Page[id='.$id.'] inexistante.');
        
        if(!$page->getActive()) 
            return $this->redirect($this->generateUrl('DwfChBundle_homepage'));

        
        return $this->render('DwfPageBundle:Page:index.html.twig', array('page' => $page));
    }
}
