<?php

namespace Dwf\PronosticsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Dwf\PronosticsBundle\Entity\Game;
use Dwf\PronosticsBundle\Entity\Pronostic;
use Dwf\PronosticsBundle\Form\GameType;
use Dwf\PronosticsBundle\Form\SimplePronosticType;

/**
 * Contest controller.
 *
 * @Route("/contests")
 */
class ContestController extends Controller
{

    /**
     * Lists all Contests entities for a user in an event
     *
     * @Route("/", name="contests")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Dwf\PronosticsBundle\Entity\Event $event)
    {
        $em = $this->getDoctrine()->getManager();

        $contests = $em->getRepository('DwfPronosticsBundle:Contest')->findAllByUserAndEvent($this->getUser(), $event);
        
        return array("contests" => $contests);
    }
}