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
use Ob\HighchartsBundle\Highcharts\Highchart;

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
     				if($currentChampionshipDay)
 	  				    $currentChampionshipDay = $currentChampionshipDay[0];
// 				else $currentChampionshipDay = '';
				}
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
        $types = $em->getRepository('DwfPronosticsBundle:GameType')->findAllByEvent($event);
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
    			$categories = array();
                $results = array();
                
                $users = $em->getRepository('DwfPronosticsBundle:User')->findAll();
                foreach ($users as $user)
                {
                    $results[$user->getUsername()] = array();
                    $usersResultsGroupedByType = $em->getRepository('DwfPronosticsBundle:Pronostic')->getResultsByEventAndUserGroupedByType($event, $user);
                    foreach ($usersResultsGroupedByType as $userResultGrouped)
                    {
                        array_push($results[$user->getUsername()], array($userResultGrouped['type'], intval($userResultGrouped['total'])));
                    }
                }
                $positions = array();
                foreach ($types as $championshipDay)
                {
                    array_push($positions, $championshipDay->getPosition());
                    if($championshipDay->getPosition() <= $currentChampionshipDay->getPosition())
                        array_push($categories, $championshipDay->getName());
                }
                $finalResult = array();
                foreach ($results as $user => $userResults)
                {
                    if(sizeof($userResults)) {
                        $finalResult[$user] = array();
                        foreach ($positions as $position)
                        {
                            if($position <= $currentChampionshipDay->getPosition()) {
                            $dayOk = false;
                            foreach ($userResults as $points)
                            {
                                if($position == $points[0]) {
                                    $dayOk = true;
                                    array_push($finalResult[$user], $points[1]);
                                }
                                
                            }
                            if($dayOk == false)
                                array_push($finalResult[$user],  0);
                            }
                        }
                    }
                }

                $series = array();
                foreach ($finalResult as $user => $userResults)
                {
                    $serie = array(
                        'name'  => $user,
                        'data'  => $userResults,
                        'dataLabels' => array('enabled' => false),
                    );
                    array_push($series, $serie);
                }
                $yData = array(
                        array(
                                'min' => 0,
                                'title' => array(
                                'text'  => 'Score',
                             ),
                        ),
                );
                $ob = new Highchart();
                $ob->chart->renderTo('chart');
                $ob->chart->type('spline');
                $ob->title->text('Resultats');
                $ob->xAxis(array(array('categories' => $categories, 'labels' => array('rotation' => -45))));
                $ob->yAxis($yData);
                $ob->series($series);

            }
            else {
                $currentChampionshipDay = '';
                $ob = '';
            }
	    	$entities = $em->getRepository('DwfPronosticsBundle:Game')->findAllByEventOrderedByDate($event);
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
	    		$pronostics = array();
	    		$nbPointsWonByChampionshipDay = 0;
	    		$nbPronostics = 0;
	    		$nbPerfectScore = $nbGoodScore = $nbBadScore = 0;
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
	    				$nbPronostics++;
	    				if($pronostic->getResult() && $event->getChampionship())
	    				    $nbPointsWonByChampionshipDay += $pronostic->getResult();
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
	    			array_push($pronostics, $pronostic);
	    			$i++;
	    		}
	    	}
	    	else $forms = "";
	    	return array(
	    			'event'		=> $event,
	    			'currentChampionshipDay' => $currentChampionshipDay,
	    	        'nbPointsWonByChampionshipDay' => $nbPointsWonByChampionshipDay,
	    			'user' 		=> $this->getUser(),
	    			'entities' 	=> $entities,
	    			'types'     => $types,
	    			'forms'	=> $forms,
	    			'teams'	=> $arrayTeams,
	    			'results' => $results,
	    	        'pronostics' => $pronostics,
	    	        'nbPronostics' => $nbPronostics,
	    	        'nbPerfectScore' => $nbPerfectScore,
	    	        'nbGoodScore' => $nbGoodScore,
	    	        'nbBadScore' => $nbBadScore,
	    	        'chart' => $ob
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
                $championshipManager = $this->get('dwf_pronosticbundle.championshipmanager');
                $championshipManager->setEvent($event);
                $currentChampionshipDay = $championshipManager->getCurrentChampionshipDay();

                $usersResultsByType = $em->getRepository('DwfPronosticsBundle:Pronostic')->getResultsByEventAndType($event, $type);
                $results = array();
                $pronos = array();
                foreach ($usersResultsByType as $userResult)
                {
                    $result = array($userResult['username'], intval($userResult['total']));
                    $nbPronos = array($userResult['username'], intval($userResult['nb_pronostics']));
                    array_push($results, $result);
                    array_push($pronos, $nbPronos);
                }

                $chart = $this->get('dwf_pronosticbundle.highchartmanager');
                $chart->setYdata(array('min' => 0, 'title' => 'Score'));
                $chart->addSerie(array('name'=> 'Points gagnés', 'object' => $results, 'color' => ''));
                $chart->addSerie(array('name'=> 'Nb Pronostics', 'object' => $pronos, 'color' => '#4572A7'));
                $ob = $chart->chart();

            }
            else {
                $currentChampionshipDay = '';
                $ob = '';
            }
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
                $pronostics = array();
                $nbPointsWonByChampionshipDay = 0;
                $nbPronostics = 0;
                $nbPerfectScore = $nbGoodScore = $nbBadScore = 0;
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
                        $nbPronostics++;
                        if($pronostic->getResult() && $event->getChampionship())
                            $nbPointsWonByChampionshipDay += $pronostic->getResult();
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
                    array_push($pronostics, $pronostic);
                   $i++;
                }
            }
            else $forms = "";
            return array(
                    'teams'	=> $arrayTeams,
                    'results' => $results,
                    'event' => $event,
                    'currentChampionshipDay' => $currentChampionshipDay,
                    'nbPointsWonByChampionshipDay' => $nbPointsWonByChampionshipDay,
                    'user' => $this->getUser(),
                    'entity'    => $gameType,
                    'entities' => $entities,
                    'types'     => $types,
                    'forms'	=> $forms,
                    'pronostics' => $pronostics,
                    'nbPronostics' => $nbPronostics,
                    'nbPerfectScore' => $nbPerfectScore,
                    'nbGoodScore' => $nbGoodScore,
                    'nbBadScore' => $nbBadScore,
                    'chart' => $ob
            );
        }
        else return $this->redirect($this->generateUrl('events'));
    }
}
