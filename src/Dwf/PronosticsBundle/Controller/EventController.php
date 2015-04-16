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
 * Game controller.
 *
 * @Route("/events")
 */
class EventController extends Controller
{

    /**
     * Lists all Events entities.
     *
     * @Route("/", name="events")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('DwfPronosticsBundle:Event')->findAllOrderedByDate();
        if(count($entities) == 1) {
            $event = $entities[0];
            return $this->redirect($this->generateUrl('event_home', array('event' => $event->getId())));
        }
        
        return array(
        	'event' => "",
            'user' => $this->getUser(),
            'events' => $entities,
        );
    }
    
    /**
     * Lists all passed Events entities.
     *
     * @Route("/old", name="events_old")
     * @Method("GET")
     * @Template("")
     */
    public function oldAction()
    {
    	$em = $this->getDoctrine()->getManager();
    
    	$entities = $em->getRepository('DwfPronosticsBundle:Event')->getOldEventsOrderedByDate();
    	if(count($entities) == 1) {
    		$event = $entities[0];
    		return $this->redirect($this->generateUrl('event_home', array('event' => $event->getId())));
    	}
    
    	return array(
    			'event' => "",
    			'user' => $this->getUser(),
    			'events' => $entities,
    	);
    }
    
    /**
     * Finds and displays a Event entity.
     *
     * @Route("/{id}", name="event_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DwfPronosticsBundle:Event')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Event entity.');
        }
        else {
            $this->getRequest()->getSession()->set('event', $entity);
            return $this->redirect($this->generateUrl('event_home', array('event' => $entity->getId())));
            
        }
    }

    /**
     * Lists all Events entities.
     *
     * @Route("/{event}/pronostics", name="event_pronostics")
     * @Method("GET")
     * @Template()
     */
    public function pronosticsAction($event)
    {
        $em = $this->getDoctrine()->getManager();
    
        $event = $em->getRepository('DwfPronosticsBundle:Event')->find($event);
        
        $pronostic = $em->getRepository('DwfPronosticsBundle:BestScorerPronostic')->findOneByUserAndEvent($this->getUser(), $event);
        if($pronostic)
            $bestscorer_pronostic = $pronostic[0];
        
        $pronostics = $em->getRepository('DwfPronosticsBundle:Pronostic')->findByUserAndEvent($this->getUser(), $event);
                
        return array(
                'user' => $this->getUser(),
                'event' => $event,
                'bestscorer_pronostic' => $pronostic ? $bestscorer_pronostic : '',
                'pronostics' => $pronostics,
        );
    }
    
    /**
     * Lists all Events entities.
     *
     * @Route("/{event}/home", name="event_home")
     * @Method({"GET","POST", "PUT"})
     * @Template()
     */
    public function homeAction($event)
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $event = $em->getRepository('DwfPronosticsBundle:Event')->find($event);
    
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
        
        $games = $em->getRepository('DwfPronosticsBundle:Game')->findAllByEventAndDate($event, date("Y/m/d"));
        if($event->getSimpleBet()) {
        	$forms_games = array();
        	$pronostics_games = array();
        	$i = 0;
        	foreach($games as $entity)
        	{
        		$pronostic = $em->getRepository('DwfPronosticsBundle:Pronostic')->findOneBy(array('user' => $this->getUser(), 'game' => $entity));
        		if($pronostic) {
        			$simpleType = new SimplePronosticType();
        			$simpleType->setName($entity->getId());
        			$form = $this->createForm($simpleType, $pronostic, array(
        					'action' => '',
        					'method' => 'PUT',
        			));
        			$form->handleRequest($request);
        			if ($form->isValid()) {
        				$em->persist($pronostic);
        				$em->flush();
        			}
        		}
        		else {
        			$simplePronostic = new Pronostic();
        			$simplePronostic->setGame($entity);
        			$simplePronostic->setUser($this->getUser());
        			$simplePronostic->setEvent($entity->getEvent());
        			$simpleType = new SimplePronosticType();
        			$simpleType->setName($entity->getId());
        			$form = $this->createForm($simpleType, $simplePronostic, array(
        					'action' => '',
        					'method' => 'POST',
        			));
        			$form->handleRequest($request);
        			if ($form->isValid()) {
        				$em->persist($simplePronostic);
        				$em->flush();
        			}
        		}
        		array_push($forms_games, $form->createView());
        		array_push($pronostics_games, $pronostic);
        		$i++;
        	}
        }
        else {
            $forms_games = "";
            $pronostics_games = "";
        }
        
        $nextGames = $em->getRepository('DwfPronosticsBundle:Game')->findNextGames($event);
        if($event->getSimpleBet()) {
        	$forms_nextgames = array();
        	$pronostics_nextgames = array();
        	$i = 0;
        	foreach($nextGames as $entity)
        	{
        		$pronostic = $em->getRepository('DwfPronosticsBundle:Pronostic')->findOneBy(array('user' => $this->getUser(), 'game' => $entity));
        		if($pronostic) {
        			$simpleType = new SimplePronosticType();
        			$simpleType->setName($entity->getId());
        			$form = $this->createForm($simpleType, $pronostic, array(
        					'action' => '',
        					'method' => 'PUT',
        			));
        			$form->handleRequest($request);
        			if ($form->isValid()) {
        				$em->persist($pronostic);
        				$em->flush();
        			}
        		}
        		else {
        			$simplePronostic = new Pronostic();
        			$simplePronostic->setGame($entity);
        			$simplePronostic->setUser($this->getUser());
        			$simplePronostic->setEvent($entity->getEvent());
        			$simpleType = new SimplePronosticType();
        			$simpleType->setName($entity->getId());
        			$form = $this->createForm($simpleType, $simplePronostic, array(
        					'action' => '',//$this->generateUrl('pronostics_create_simple'),
        					'method' => 'POST',
        			));
        			$form->handleRequest($request);
        			if ($form->isValid()) {
        				$em->persist($simplePronostic);
        				$em->flush();
        			}
        		}
        		array_push($forms_nextgames, $form->createView());
        		array_push($pronostics_nextgames, $pronostic);
        		$i++;
        	}
        }
        else {
            $forms_nextgames = "";
            $pronostics_nextgames = "";
        }
        $scorers = $em->getRepository('DwfPronosticsBundle:Scorer')->findBestScorersByEvent($event, 25);
        $players = $em->getRepository('DwfPronosticsBundle:Player')->findAll();
        foreach ($players as $player)
        {
            $arrayPlayers[$player->getId()] = $player;
        }
        $bestOffenses = $em->getRepository('DwfPronosticsBundle:Scorer')->findBestOffensesByEvent($event);
        $bestDefenses = $em->getRepository('DwfPronosticsBundle:GameTypeResult')->findBestDefensesByEvent($event);
        //$pronostics = $em->getRepository('DwfPronosticsBundle:Pronostic')->findByUserAndEvent($this->getUser(), $event);

        $teams = $em->getRepository('DwfPronosticsBundle:Team')->findAll();
        foreach ($teams as $team)
        {
            $arrayTeams[$team->getId()] = $team;
        }
        
        return array(
                'user' => $this->getUser(),
                'event' => $event,
        		'currentChampionshipDay' => $currentChampionshipDay,
                //'bestscorer_pronostic' => $pronostic ? $bestscorer_pronostic : '',
                'games' => $games,
                'nextGames' => $nextGames,
                'scorers'   => $scorers,
                'teams'     => $arrayTeams,
                'players'   => $arrayPlayers,
                'bestOffenses' => $bestOffenses,
                'bestDefenses' => $bestDefenses,
        		'forms_games' => $forms_games,
        		'forms_nextgames' => $forms_nextgames,
                'pronostics_games' => $pronostics_games,
                'pronostics_nextgames' => $pronostics_nextgames,
        );
    }
    
    /**
     * Lists all Scorers entities.
     *
     * @Route("/{event}/scorers", name="event_scorers")
     * @Method("GET")
     * @Template()
     */
    public function scorersAction($event)
    {
        $em = $this->getDoctrine()->getManager();
    
        $event = $em->getRepository('DwfPronosticsBundle:Event')->find($event);
        $scorers = $em->getRepository('DwfPronosticsBundle:Scorer')->findBestScorersByEvent($event);
        $players = $em->getRepository('DwfPronosticsBundle:Player')->findAll();
        foreach ($players as $player)
        {
            $arrayPlayers[$player->getId()] = $player;
        }
        return array(
                'event' => $event,
                'user' => $this->getUser(),
                'scorers'   => $scorers,
                'players'   => $arrayPlayers,
        );
    }
    
}
