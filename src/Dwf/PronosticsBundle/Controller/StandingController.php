<?php

namespace Dwf\PronosticsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Dwf\PronosticsBundle\Entity\Pronostic;
use Dwf\PronosticsBundle\Form\PronosticType;
use Dwf\PronosticsBundle\Form\PronosticGameType;

/**
 * Standings controller.
 *
 * @Route("/standings")
 */
class StandingController extends Controller
{

    /**
     * Lists standings.
     *
     * @Route("/", name="standings")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $groups = $user->getGroups();
        foreach ($groups as $group) {
        	$userGroup = $group;
        	break;
        }
        $entities = $em->getRepository('DwfPronosticsBundle:Pronostic')->getResultsForGroup($userGroup);
//var_dump($entities);
        return array(
            'user'      => $user,
            'entities' => $entities,
        );
    }
    
    /**
     * Lists standings.
     * a supprimer
     * @Route("/old/{event}", name="standings_event_old")
     * @Method("GET")
     * @Template("DwfPronosticsBundle:Standing:index.html.twig")
     */
    public function showByEventOldAction($event)
    {
    	$em = $this->getDoctrine()->getManager();
    	$event = $em->getRepository('DwfPronosticsBundle:Event')->find($event);
    	if($event) {
	    	$user = $this->getUser();
	    	$entities = $em->getRepository('DwfPronosticsBundle:Pronostic')->getResultsByEvent($event);
	    	//var_dump($entities);
	    	return array(
	    			'user'      => $user,
	    			'event'		=> $event,
	    			'entities' => $entities,
	    	);
    	}
    	else return $this->redirect($this->generateUrl('events'));
    }
    
    /**
     * Lists standings.
     *
     * @Route("/{event}", name="standings_event")
     * @Method("GET")
     * @Template("DwfPronosticsBundle:Standing:index.html.twig")
     */
    public function showByEventAction($event)
    {
    	$em = $this->getDoctrine()->getManager();
    	$event = $em->getRepository('DwfPronosticsBundle:Event')->find($event);
    	if($event) {
    	    $championshipManager = $this->get('dwf_pronosticbundle.championshipmanager');
    	    $championshipManager->setEvent($event);
    	    $currentChampionshipDay = $championshipManager->getCurrentChampionshipDay();

    		$groupResults = array();
    		$user = $this->getUser();
    		$groups = $user->getGroups();

    		return array(
    				'user'      => $user,
    				'event'		=> $event,
    				'currentChampionshipDay' => $currentChampionshipDay,
    				'groups'	=> $groups,
    		);
    	}
    	else return $this->redirect($this->generateUrl('events'));
    }
    
    /**
     * Lists standings.
     *
     * @Route("/{event}/group/{group}", name="standings_event_group")
     * @Method("GET")
     * @Template("DwfPronosticsBundle:Standing:group.html.twig")
     */
    public function showByEventAndGroupAction($event, $group)
    {
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository('DwfPronosticsBundle:Event')->find($event);
        //$group = $em->getRepository('FOSUserBundle:Group')->find($group);
        if($event) {
            $championshipManager = $this->get('dwf_pronosticbundle.championshipmanager');
            $championshipManager->setEvent($event);
            $currentChampionshipDay = $championshipManager->getCurrentChampionshipDay();
    
            $groupResults = array();
            $user = $this->getUser();
            $groups = $user->getGroups();
            $userGroup = false;
            if($groups) {
                foreach ($groups as $key => $groupUser) {
                    //$entities = $em->getRepository('DwfPronosticsBundle:Pronostic')->getResultsByEventAndGroup($event, $group);
                    if($groupUser->getId() == $group) {
                        $entities = $em->getRepository('DwfPronosticsBundle:Standing')->getByEventAndGroup($event, $groupUser);
                        break;
                    }
                    //array_push($groupResults, array('group' => $group, 'results' => $entities));
                    //$pronosByGroup[$group->getId()] = $entities;
                }
            }
            //var_dump($entities);
            return array(
                    'user'      => $user,
                    'event'		=> $event,
                    'currentChampionshipDay' => $currentChampionshipDay,
                    'entities' => $entities,
                    'group'	=> $groupUser,
                    //'groupResults' => $groupResults,
            );
        }
        else return $this->redirect($this->generateUrl('events'));
    }
}
