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
    		if($event->getChampionship()) {
    			$lastGamePlayed = $em->getRepository('DwfPronosticsBundle:Game')->findLastGamePlayedByEvent($event);
    			if(count($lastGamePlayed) > 0) {
    				$lastGamePlayed = $lastGamePlayed[0];
    				$gamesLeftInChampionshipDay = $em->getRepository('DwfPronosticsBundle:Game')->findGamesLeftByEventAndGameType($event, $lastGamePlayed->getType());
    				if($gamesLeftInChampionshipDay)
    					$currentChampionshipDay = $em->getRepository('DwfPronosticsBundle:GameType')->find($lastGamePlayed->getType());
    				else {
    					$currentChampionshipDay = $em->getRepository('DwfPronosticsBundle:GameType')->getByEventAndPosition($event, $lastGamePlayed->getType()->getPosition() + 1);
	    				if($currentChampionshipDay)
	    					$currentChampionshipDay = $currentChampionshipDay[0];
	    				else $currentChampionshipDay = '';
    				}
    			}
    		}
    		else $currentChampionshipDay = '';
    		$groupResults = array();
    		$user = $this->getUser();
    		$groups = $user->getGroups();
    		$userGroup = false;
    		if($groups) {
    			foreach ($groups as $key => $group) {
    				//$entities = $em->getRepository('DwfPronosticsBundle:Pronostic')->getResultsByEventAndGroup($event, $group);
    				$entities = $em->getRepository('DwfPronosticsBundle:Standing')->getByEventAndGroup($event, $group);
    				array_push($groupResults, array('group' => $group, 'results' => $entities));
    				//$pronosByGroup[$group->getId()] = $entities;
    			}
    			if($user->getId() == 1) {
	    			if(sizeof($groupResults) == 0) {
	    				
	    				$groups = $em->getRepository('ApplicationSonataUserBundle:Group')->findAll();
	    				foreach ($groups as $key => $group) {
	    					$entities = $em->getRepository('DwfPronosticsBundle:Pronostic')->getResultsByEventAndGroup($event, $group);
	    					array_push($groupResults, array('group' => $group, 'results' => $entities));
	    				}
	    			}
    			}
//     			else {
//     				$group = $em->getRepository('ApplicationSonataUserBundle:Group')->find(4);
//     				$entities = $em->getRepository('DwfPronosticsBundle:Pronostic')->getResultsByEventAndGroup($event, $group);
//     				array_push($groupResults, array('group' => $group, 'results' => $entities));
//     			}
    		}
    		else {
    			// groupe Autres
   				$group = $em->getRepository('ApplicationSonataUserBundle:Group')->find(4);
				$entities = $em->getRepository('DwfPronosticsBundle:Pronostic')->getResultsByEventAndGroup($event, $group);
   					array_push($groupResults, array('group' => $group, 'results' => $entities));
    		}
    
    		//var_dump($entities);
    		return array(
    				'user'      => $user,
    				'event'		=> $event,
    				'currentChampionshipDay' => $currentChampionshipDay,
    				'entities' => $entities,
    				'groups'	=> $groups,
    				'groupResults' => $groupResults,
    		);
    	}
    	else return $this->redirect($this->generateUrl('events'));
    }
}
