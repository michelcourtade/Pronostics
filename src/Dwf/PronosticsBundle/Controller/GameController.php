<?php

namespace Dwf\PronosticsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Dwf\PronosticsBundle\Entity\Game;
use Dwf\PronosticsBundle\Form\GameType;
use Dwf\PronosticsBundle\Form\SimplePronosticType;
use Dwf\PronosticsBundle\Entity\Pronostic;

/**
 * Game controller.
 *
 * @Route("/games")
 */
class GameController extends Controller
{

    /**
     * Lists all Game entities.
     *
     * @Route("/", name="games")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('DwfPronosticsBundle:Game')->findAllOrderedByDate();
        $types = $em->getRepository('DwfPronosticsBundle:GameType')->findAll();
        return array(
            'user' => $this->getUser(),
            'entities' => $entities,
            'types'     => $types,
        );
    }
    
    /**
     * Finds and displays a Game entity.
     *
     * @Route("/{id}", name="game_show")
     * @Method({"GET","POST", "PUT"})
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();
		$request = $this->getRequest();
        $entity = $em->getRepository('DwfPronosticsBundle:Game')->find($id);
        if($entity->getScoreTeam1() > 0 || $entity->getScoreTeam1Overtime() > 0)
            $scorersTeam1 = $em->getRepository('DwfPronosticsBundle:Scorer')->findScorersByGameAndTeam($entity, $entity->getTeam1());
        else $scorersTeam1 = "";
        if($entity->getScoreTeam2() > 0 || $entity->getScoreTeam2Overtime() > 0)
            $scorersTeam2 = $em->getRepository('DwfPronosticsBundle:Scorer')->findScorersByGameAndTeam($entity, $entity->getTeam2());
        else $scorersTeam2 = "";
        $pronostic = $em->getRepository('DwfPronosticsBundle:Pronostic')->findOneBy(array('user' => $this->getUser(), 'game' => $entity));
        if($pronostic) {
            $nextGame = $em->getRepository('DwfPronosticsBundle:Game')->findNextGameAfter($entity);
            $pronosticNextGame = $em->getRepository('DwfPronosticsBundle:Pronostic')->findOneBy(array('user' => $this->getUser(), 'game' => $nextGame));
            
            if($entity->getEvent()->getSimpleBet()) {
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
        }
        else {
            $nextGame = "";
            $pronosticNextGame = "";
            if($entity->getEvent()->getSimpleBet()) {
	            $simplePronostic = new Pronostic();
	            $simplePronostic->setGame($entity);
			    $simplePronostic->setUser($this->getUser());
			    $simplePronostic->setEvent($entity->getEvent());
			    $simpleType = new SimplePronosticType();
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
        }

        if($entity->hasBegan() || $entity->getPlayed()) {
            $user = $this->getUser();
            $groups = $user->getGroups();
            $pronostics = $em->getRepository('DwfPronosticsBundle:Pronostic')->findAllByGame($entity, $groups);
        }
        else $pronostics = "";
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Game entity.');
        }
		$event = $entity->getEvent();
		if($event->getChampionship()) {
			$lastGamePlayed = $em->getRepository('DwfPronosticsBundle:Game')->findLastGamePlayedByEvent($event);
			if(count($lastGamePlayed) > 0) {
				$lastGamePlayed = $lastGamePlayed[0];
				$gamesLeftInChampionshipDay = $em->getRepository('DwfPronosticsBundle:Game')->findGamesLeftByEventAndGameType($event, $lastGamePlayed->getType());
				if($gamesLeftInChampionshipDay)
					$currentChampionshipDay = $em->getRepository('DwfPronosticsBundle:GameType')->find($lastGamePlayed->getType());
				else {
					$currentChampionshipDay = $em->getRepository('DwfPronosticsBundle:GameType')->getByEventAndPosition($event, $lastGamePlayed->getType()->getPosition() + 1);
				}
// 				if($currentChampionshipDay)
// 					$currentChampionshipDay = $currentChampionshipDay[0];
// 				else $currentChampionshipDay = '';
			}
		}
		else $currentChampionshipDay = '';
        return array(
            'event'                 => $event,
        	'currentChampionshipDay' => $currentChampionshipDay,
            'entity'                => $entity,
            'pronostic'             => $pronostic,
            'nextGame'              => $nextGame,
            'pronosticNextGame'     => $pronosticNextGame,
        	'pronostics'            => $pronostics,
            'scorersTeam1'          => $scorersTeam1,
            'scorersTeam2'          => $scorersTeam2,
        	'form'					=> $entity->getEvent()->getSimpleBet() ? $form->createView():'',
            //'delete_form' => $deleteForm->createView(),
        );
    }
    
    /**
     * Lists all Game entities.
     *
     * @Route("/event/{event}", name="games_event")
     * @Method({"GET","POST", "PUT"})
     * @Template("DwfPronosticsBundle:Game:index.html.twig")
     */
    public function showByEventAction($event)
    {
    	$em = $this->getDoctrine()->getManager();
    	$request = $this->getRequest();
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
	    	$entities = $em->getRepository('DwfPronosticsBundle:Game')->findAllByEventOrderedByDate($event);
	    	$types = $em->getRepository('DwfPronosticsBundle:GameType')->findAllByEvent($event);
	    	if($event->getChampionship()) {
	    	  $gameTypeId = $em->getRepository('DwfPronosticsBundle:GameTypeResult')->getMaxGameTypeIdByEvent($event);
	    	  $gameType = $em->getRepository('DwfPronosticsBundle:GameType')->find($gameTypeId);
	    	  $results = $em->getRepository('DwfPronosticsBundle:GameTypeResult')->getResultsByGameTypeAndEvent($gameType, $event);
	    	}
	    	else $results = $em->getRepository('DwfPronosticsBundle:GameTypeResult')->getResultsByEvent($event);
	    	$teams = $em->getRepository('DwfPronosticsBundle:Team')->findAll();
	    	foreach ($teams as $team)
	    	{
	    		$arrayTeams[$team->getId()] = $team;
	    	}
	    	if($event->getSimpleBet()) {
	    		$forms = array();
	    		$i = 0;
	    		foreach($entities as $entity)
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
	    			array_push($forms, $form->createView());
	    			$i++;
	    		}
	    	}
	    	else $forms = "";
	    	return array(
	    			'event'		=> $event,
	    			'currentChampionshipDay' => $currentChampionshipDay,
	    			'user' 		=> $this->getUser(),
	    			'entities' 	=> $entities,
	    			'types'     => $types,
	    			'forms'	=> $forms,
	    			'teams'	=> $arrayTeams,
	    			'results' => $results,
	    	);
    	}
    	else return $this->redirect($this->generateUrl('events'));
    }

    /**
     * Lists all Game entities by GameType
     *
     * @Route("/event/{event}/type/{type}", name="game_by_type")
     * @Method({"GET","POST", "PUT"})
     * @Template("DwfPronosticsBundle:Game:showByGameType.html.twig")
     */
    public function showByGameTypeAndEventAction($type, $event)
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
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
            $results = $em->getRepository('DwfPronosticsBundle:GameTypeResult')->getResultsByGameTypeAndEvent($type, $event);
            $teams = $em->getRepository('DwfPronosticsBundle:Team')->findAll();
            foreach ($teams as $team)
            {
            	$arrayTeams[$team->getId()] = $team;
            }
            $gameType = $em->getRepository('DwfPronosticsBundle:GameType')->find($type);
            $entities = $em->getRepository('DwfPronosticsBundle:Game')->findAllByTypeAndEvent($type, $event);
            $types = $em->getRepository('DwfPronosticsBundle:GameType')->findAllByEvent($event);
        
            if($event->getSimpleBet()) {
            	$forms = array();
            	$i = 0;
	            foreach($entities as $entity)
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
	            	array_push($forms, $form->createView());
	            	$i++;
	            }
            }
            else $forms = "";
            return array(
            		'teams'	=> $arrayTeams,
                    'results' => $results,
                    'event' => $event,
            		'currentChampionshipDay' => $currentChampionshipDay,
                    'user' => $this->getUser(),
                    'entity'    => $gameType,
                    'entities' => $entities,
	    			'types'     => $types,
            		'forms'	=> $forms,
            );
        }
        else return $this->redirect($this->generateUrl('events'));
    }
}
