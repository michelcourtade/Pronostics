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
use Dwf\PronosticsBundle\Form\Type\ContestFormType;
use Dwf\PronosticsBundle\Entity\Contest;

/**
 * Contest controller.
 *
 * @Route("/")
 */
class ContestController extends Controller
{

    /**
     * Lists all Contests entities for a user in an event
     *
     * @Route("/{event}/contests", name="contests")
     * @Method({"GET","PUT"})
     * @Template()
     */
    public function listAction($event)
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $event = $em->getRepository('DwfPronosticsBundle:Event')->find($event);
        $contests = $em->getRepository('DwfPronosticsBundle:Contest')->findAllByUserAndEvent($this->getUser(), $event);
        $contestType = new ContestFormType("Dwf\PronosticsBundle\Entity\User");
        $contest = new Contest('');
//             $contest->setName('');
        $contest->setOwner($this->getUser());
        $contest->setEvent($event);
        $form = $this->createForm($contestType, $contest, array(
                'action' => $this->generateUrl('contests', array('event' => $event->getId())),
                'method' => 'PUT',
        ));
        $form->add('submit', 'submit', array('label' => 'Create'));
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em->persist($contest);
            $em->flush();
            return $this->redirect($this->generateUrl('events'));
        }
        $contestForm = $form->createView();

        return array("contests" => $contests,
                     "contestForm" => $contestForm);
    }
}