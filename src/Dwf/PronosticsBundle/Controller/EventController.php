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
        $adminMessage = $em->getRepository('DwfPronosticsBundle:AdminMessage')->findLast();

        return array(
            'event'         => "",
            'user'          => $this->getUser(),
            'events'        => $entities,
            'adminMessage'  => $adminMessage,
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
        $adminMessage = $em->getRepository('DwfPronosticsBundle:AdminMessage')->findLast();
        
        return array(
                'event'             => "",
                'user'              => $this->getUser(),
                'events'            => $entities,
                'adminMessage'      => $adminMessage,
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
     * Lists all Game entities of an event
     *
     * @Route("/{event}/games", name="event_games")
     * @Method({"GET","POST", "PUT"})
     * @Template("DwfPronosticsBundle:Game:index.html.twig")
     */
    public function gamesAction($event)
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $event = $em->getRepository('DwfPronosticsBundle:Event')->find($event);
        $types = $em->getRepository('DwfPronosticsBundle:GameType')->findAllByEvent($event);
        if($event) {
            if($event->getChampionship()) {
                $championshipManager = $this->get('dwf_pronosticbundle.championshipmanager');
                $championshipManager->setEvent($event);
                $currentChampionshipDay = $championshipManager->getCurrentChampionshipDay();
            }
            else {
                $currentChampionshipDay = '';
            }
            $games = $em->getRepository('DwfPronosticsBundle:Game')->findAllByEventOrderedByDate($event);
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
            else {
                $forms = "";
                $pronostics = "";
                $nbPronostics = $nbPerfectScore = $nbGoodScore = $nbBadScore = 0;
                $nbPointsWonByChampionshipDay = 0;
            }
            $adminMessage = $em->getRepository('DwfPronosticsBundle:AdminMessage')->findLast();
            return array(
                    'event'                         => $event,
                    'currentChampionshipDay'        => $currentChampionshipDay,
                    'nbPointsWonByChampionshipDay'  => $nbPointsWonByChampionshipDay,
                    'user'                          => $this->getUser(),
                    'games'                         => $games,
                    'types'                         => $types,
                    'forms'                         => $forms,
                    'teams'                         => $arrayTeams,
                    'results'                       => $results,
                    'pronostics'                    => $pronostics,
                    'nbPronostics'                  => $nbPronostics,
                    'nbPerfectScore'                => $nbPerfectScore,
                    'nbGoodScore'                   => $nbGoodScore,
                    'nbBadScore'                    => $nbBadScore,
                    'chart'                         => '',
                    'contest'                       => '',
                    'gameType'                      => '',
                    'adminMessage'                  => $adminMessage,
            );
        }
        else return $this->redirect($this->generateUrl('events'));
    }
    
    /**
     * Lists all Game entities by GameType
     *
     * @Route("/{event}/type/{type}", name="event_games_by_type")
     * @Method({"GET","POST", "PUT"})
     * @Template("DwfPronosticsBundle:Game:showByGameType.html.twig")
     */
    public function showByGameTypeAction($type, $event)
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
            $games = $em->getRepository('DwfPronosticsBundle:Game')->findAllByTypeAndEvent($type, $event);
            $types = $em->getRepository('DwfPronosticsBundle:GameType')->findAllByEvent($event);
    
            if($event->getSimpleBet()) {
                $forms = array();
                $pronostics = array();
                $nbPointsWonByChampionshipDay = 0;
                $nbPronostics = 0;
                $nbPerfectScore = $nbGoodScore = $nbBadScore = 0;
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
            else {
                $forms = "";
                $pronostics = array();
                $nbPointsWonByChampionshipDay = 0;
                $nbPronostics = 0;
                $nbPerfectScore = $nbGoodScore = $nbBadScore = 0;
                foreach($games as $entity)
                {
                    $pronostic = $em->getRepository('DwfPronosticsBundle:Pronostic')->findOneBy(array('user' => $this->getUser(), 'game' => $entity));
                    array_push($pronostics, $pronostic);
                }
            }
            $adminMessage = $em->getRepository('DwfPronosticsBundle:AdminMessage')->findLast();
            return array(
                    'teams'                         => $arrayTeams,
                    'results'                       => $results,
                    'event'                         => $event,
                    'currentChampionshipDay'        => $currentChampionshipDay,
                    'nbPointsWonByChampionshipDay'  => $nbPointsWonByChampionshipDay,
                    'user'                          => $this->getUser(),
                    'entity'                        => $gameType,
                    'games'                         => $games,
                    'types'                         => $types,
                    'forms'                         => $forms,
                    'pronostics'                    => $pronostics,
                    'nbPronostics'                  => $nbPronostics,
                    'nbPerfectScore'                => $nbPerfectScore,
                    'nbGoodScore'                   => $nbGoodScore,
                    'nbBadScore'                    => $nbBadScore,
                    'chart'                         => $ob,
                    'contest'                       => '',
                    'adminMessage'                  => $adminMessage,
            );
        }
        else return $this->redirect($this->generateUrl('events'));
    }
    
    /**
     * Finds and displays a Game entity.
     *
     * @Route("/{event}/games/{id}", name="event_game_show")
     * @Method({"GET","POST", "PUT"})
     * @Template("DwfPronosticsBundle:Game:show.html.twig")
     */
    public function showGameAction($id, $event)
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
            $championshipManager = $this->get('dwf_pronosticbundle.championshipmanager');
            $championshipManager->setEvent($event);
            $currentChampionshipDay = $championshipManager->getCurrentChampionshipDay();
        }
        else $currentChampionshipDay = '';
        $adminMessage = $em->getRepository('DwfPronosticsBundle:AdminMessage')->findLast();
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
                'form'                  => $entity->getEvent()->getSimpleBet() ? $form->createView():'',
                'contest'               => '',
                'messageForContest'     => '',
                'adminMessage'          => $adminMessage,
        );
    }
    
    /**
     * Show user pronostics for an event.
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
        $adminMessage = $em->getRepository('DwfPronosticsBundle:AdminMessage')->findLast();
        
        return array(
                'user'                      => $this->getUser(),
                'event'                     => $event,
                'bestscorer_pronostic'      => $pronostic ? $bestscorer_pronostic : '',
                'pronostics'                => $pronostics,
                'adminMessage'              => $adminMessage,
        );
    }

    /**
     * Show infos for an event
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
            $championshipManager = $this->get('dwf_pronosticbundle.championshipmanager');
            $championshipManager->setEvent($event);
            $currentChampionshipDay = $championshipManager->getCurrentChampionshipDay();
        }
        else $currentChampionshipDay = '';

        $players = $em->getRepository('DwfPronosticsBundle:Player')->findAll();
        foreach ($players as $player)
        {
            $arrayPlayers[$player->getId()] = $player;
        }

        $teams = $em->getRepository('DwfPronosticsBundle:Team')->findAll();
        foreach ($teams as $team)
        {
            $arrayTeams[$team->getId()] = $team;
        }
        $nb = $em->getRepository('DwfPronosticsBundle:Pronostic')->getNbByUserAndEvent($this->getUser(), $event);
        $nbPerfectScore = $em->getRepository('DwfPronosticsBundle:Pronostic')->getNbScoreByUserAndEventAndResult($this->getUser(), $event, 5);
        $nbGoodScore = $em->getRepository('DwfPronosticsBundle:Pronostic')->getNbScoreByUserAndEventAndResult($this->getUser(), $event, 3);
        $nbBadScore = $em->getRepository('DwfPronosticsBundle:Pronostic')->getNbScoreByUserAndEventAndResult($this->getUser(), $event, 1);
        $total = $em->getRepository('DwfPronosticsBundle:Pronostic')->getResultsByEventAndUser($event, $this->getUser());
        $adminMessage = $em->getRepository('DwfPronosticsBundle:AdminMessage')->findLast();
        
        return array(
                'user'                      => $this->getUser(),
                'event'                     => $event,
                'currentChampionshipDay'    => $currentChampionshipDay,
                'teams'                     => $arrayTeams,
                'players'                   => $arrayPlayers,
                'nbPronostics'              => $nb,
                'nbPerfectScore'            => $nbPerfectScore,
                'nbGoodScore'               => $nbGoodScore,
                'nbBadScore'                => $nbBadScore,
                'total'                     => $total,
                'adminMessage'              => $adminMessage,
        );
    }

    /**
     * Show best offenses for an event
     *
     * @Route("/{event}/bestoffense", name="event_bestoffense")
     * @Method({"GET","POST", "PUT"})
     * @Template()
     */
    public function bestOffenseAction($event)
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $event = $em->getRepository('DwfPronosticsBundle:Event')->find($event);

        if($event->getChampionship()) {
            $championshipManager = $this->get('dwf_pronosticbundle.championshipmanager');
            $championshipManager->setEvent($event);
            $currentChampionshipDay = $championshipManager->getCurrentChampionshipDay();
        }
        else $currentChampionshipDay = '';
        $players = $em->getRepository('DwfPronosticsBundle:Player')->findAll();
        foreach ($players as $player)
        {
            $arrayPlayers[$player->getId()] = $player;
        }
        $bestOffenses = $em->getRepository('DwfPronosticsBundle:Scorer')->findBestOffensesByEvent($event);

        $teams = $em->getRepository('DwfPronosticsBundle:Team')->findAll();
        foreach ($teams as $team)
        {
            $arrayTeams[$team->getId()] = $team;
        }
        $adminMessage = $em->getRepository('DwfPronosticsBundle:AdminMessage')->findLast();
        
        return array(
                'user'                      => $this->getUser(),
                'event'                     => $event,
                'currentChampionshipDay'    => $currentChampionshipDay,
                'teams'                     => $arrayTeams,
                'players'                   => $arrayPlayers,
                'bestOffenses'              => $bestOffenses,
                'adminMessage'              => $adminMessage,
        );
    }

    /**
     * Show best defenses for an event
     *
     * @Route("/{event}/bestdefense", name="event_bestdefense")
     * @Method({"GET","POST", "PUT"})
     * @Template()
     */
    public function bestDefenseAction($event)
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $event = $em->getRepository('DwfPronosticsBundle:Event')->find($event);

        if($event->getChampionship()) {
            $championshipManager = $this->get('dwf_pronosticbundle.championshipmanager');
            $championshipManager->setEvent($event);
            $currentChampionshipDay = $championshipManager->getCurrentChampionshipDay();
        }
        else $currentChampionshipDay = '';

        $players = $em->getRepository('DwfPronosticsBundle:Player')->findAll();
        foreach ($players as $player)
        {
            $arrayPlayers[$player->getId()] = $player;
        }
        $bestDefenses = $em->getRepository('DwfPronosticsBundle:GameTypeResult')->findBestDefensesByEvent($event);

        $teams = $em->getRepository('DwfPronosticsBundle:Team')->findAll();
        foreach ($teams as $team)
        {
            $arrayTeams[$team->getId()] = $team;
        }
        $adminMessage = $em->getRepository('DwfPronosticsBundle:AdminMessage')->findLast();
        
        return array(
                'user'                      => $this->getUser(),
                'event'                     => $event,
                'currentChampionshipDay'    => $currentChampionshipDay,
                'teams'                     => $arrayTeams,
                'bestDefenses'              => $bestDefenses,
                'adminMessage'              => $adminMessage,
        );
    }

    /**
     * Show games of the day for an event
     *
     * @Route("/{event}/gameday", name="event_gameday")
     * @Method({"GET","POST", "PUT"})
     * @Template()
     */
    public function gameDayAction($event)
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $event = $em->getRepository('DwfPronosticsBundle:Event')->find($event);

        if($event->getChampionship()) {
            $championshipManager = $this->get('dwf_pronosticbundle.championshipmanager');
            $championshipManager->setEvent($event);
            $currentChampionshipDay = $championshipManager->getCurrentChampionshipDay();
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
            $pronostics_games = array();
            foreach($games as $entity)
            {
                $pronostic = $em->getRepository('DwfPronosticsBundle:Pronostic')->findOneBy(array('user' => $this->getUser(), 'game' => $entity));
                array_push($pronostics_games, $pronostic);
            }
        }

        $teams = $em->getRepository('DwfPronosticsBundle:Team')->findAll();
        foreach ($teams as $team)
        {
            $arrayTeams[$team->getId()] = $team;
        }
        $adminMessage = $em->getRepository('DwfPronosticsBundle:AdminMessage')->findLast();
        
        return array(
                'user'                      => $this->getUser(),
                'event'                     => $event,
                'currentChampionshipDay'    => $currentChampionshipDay,
                'games'                     => $games,
                'teams'                     => $arrayTeams,
                'forms_games'               => $forms_games,
                'pronostics_games'          => $pronostics_games,
                'adminMessage'              => $adminMessage,
                'contest'                   => '',
                'messageForContest'         => '',
        );
    }

    /**
     * Show next games for an event
     *
     * @Route("/{event}/nextgames", name="event_nextgames")
     * @Method({"GET","POST", "PUT"})
     * @Template()
     */
    public function nextGamesAction($event)
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $event = $em->getRepository('DwfPronosticsBundle:Event')->find($event);

        if($event->getChampionship()) {
            $championshipManager = $this->get('dwf_pronosticbundle.championshipmanager');
            $championshipManager->setEvent($event);
            $currentChampionshipDay = $championshipManager->getCurrentChampionshipDay();
        }
        else $currentChampionshipDay = '';

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
            $pronostics_nextgames = array();
            foreach($nextGames as $entity)
            {
                $pronostic = $em->getRepository('DwfPronosticsBundle:Pronostic')->findOneBy(array('user' => $this->getUser(), 'game' => $entity));
                array_push($pronostics_nextgames, $pronostic);
            }
        }

        $teams = $em->getRepository('DwfPronosticsBundle:Team')->findAll();
        foreach ($teams as $team)
        {
            $arrayTeams[$team->getId()] = $team;
        }
        $adminMessage = $em->getRepository('DwfPronosticsBundle:AdminMessage')->findLast();

        return array(
                'user'                      => $this->getUser(),
                'event'                     => $event,
                'currentChampionshipDay'    => $currentChampionshipDay,
                'nextGames'                 => $nextGames,
                'teams'                     => $arrayTeams,
                'forms_nextgames'           => $forms_nextgames,
                'pronostics_nextgames'      => $pronostics_nextgames,
                'contest'                   => '',
                'messageForContest'         => '',
                'adminMessage'              => $adminMessage,
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
        $adminMessage = $em->getRepository('DwfPronosticsBundle:AdminMessage')->findLast();
        return array(
                'event'         => $event,
                'user'          => $this->getUser(),
                'scorers'       => $scorers,
                'players'       => $arrayPlayers,
                'adminMessage'  => $adminMessage,
        );
    }
    
    /**
     * Statistics for event
     *
     * @Route("/{event}/statistics", name="event_statistics")
     * @Method("GET")
     * @Template()
     */
    public function statisticsAction($event)
    {
        $em = $this->getDoctrine()->getManager();
    
        $event = $em->getRepository('DwfPronosticsBundle:Event')->find($event);
        
        $adminMessage = $em->getRepository('DwfPronosticsBundle:AdminMessage')->findLast();
        return array(
                'event'         => $event,
                'user'          => $this->getUser(),
                'adminMessage'  => $adminMessage,
        );
    }
    
    /**
     * Informations for event
     *
     * @Route("/{event}/informations", name="event_informations")
     * @Method("GET")
     * @Template()
     */
    public function informationsAction($event)
    {
        $em = $this->getDoctrine()->getManager();
    
        $event = $em->getRepository('DwfPronosticsBundle:Event')->find($event);
    
        $adminMessage = $em->getRepository('DwfPronosticsBundle:AdminMessage')->findLast();
        return array(
                'event'         => $event,
                'user'          => $this->getUser(),
                'adminMessage'  => $adminMessage,
        );
    }

}
