<?php

namespace Dwf\PronosticsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="home")
     * @Template()
     */
    public function indexAction()
    {
        return $this->redirect($this->generateUrl('events'));
//         $em = $this->getDoctrine()->getManager();
        
//         $nextGame = $em->getRepository('DwfPronosticsBundle:Game')->findNextGame();
//         if($nextGame[0])
//             $nextGame = $nextGame[0];
//         return array('user' => $this->getUser(),
//                     'nextGame' => $nextGame,
//                     );
    }
}
