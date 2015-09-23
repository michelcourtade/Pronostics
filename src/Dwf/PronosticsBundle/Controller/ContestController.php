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
use Dwf\PronosticsBundle\Form\Type\InvitationContestFormType;
use Dwf\PronosticsBundle\Entity\Invitation;

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
        $myContests = $em->getRepository('DwfPronosticsBundle:Contest')->findAllByOwnerAndEvent($this->getUser(), $event);
        //$otherContests = $em->getRepository('DwfPronosticsBundle:Contest')->findInvitedContestByUserAndEvent($this->getUser(), $event);
        $groups = $this->getUser()->getGroups();
        $otherContests = array();
        if($groups) {
            foreach ($groups as $group) {
                if($group->getEvent()->getId() == $event->getId()) {
                    if($group->getOwner()->getId() != $this->getUser()->getId()) {
                        array_push($otherContests, $group);
                    }
                }
            }
        } else {
            $arrayContests = '';
        }
        

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

        return array("myContests" => $myContests,
                        "otherContests" => $otherContests,
                     "contestForm" => $contestForm);
    }

    /**
     * Show a Contest entity
     *
     * @Route("/contest/{contestId}", name="contest_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($contestId)
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $contest = $em->getRepository('DwfPronosticsBundle:Contest')->find($contestId);
        $event = $contest->getEvent();

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

        return array(
                'contest' => $contest,
                'user' => $this->getUser(),
                'event' => $event,
                'currentChampionshipDay' => $currentChampionshipDay,
                'teams'     => $arrayTeams,
                'players'   => $arrayPlayers,
                'nbPronostics' => $nb,
                'nbPerfectScore' => $nbPerfectScore,
                'nbGoodScore' => $nbGoodScore,
                'nbBadScore' => $nbBadScore,
                'total'         => $total,
        );
    }

    /**
     * Standings for a contest
     *
     * @Route("/contests/{contestId}/standings", name="contest_standings")
     * @Method("GET")
     * @Template("DwfPronosticsBundle:Contest:standings.html.twig")
     */
    public function standingsAction($contestId)
    {
        $em = $this->getDoctrine()->getManager();
        $contest = $em->getRepository('DwfPronosticsBundle:Contest')->find($contestId);
        $event = $contest->getEvent();
        if($contest) {
            $championshipManager = $this->get('dwf_pronosticbundle.championshipmanager');
            $championshipManager->setEvent($event);
            $currentChampionshipDay = $championshipManager->getCurrentChampionshipDay();

            $groupResults = array();
            $user = $this->getUser();
            $groups = $user->getGroups();
            $userGroup = false;
            $entities = array();
            $groupUser = array();
            if($groups) {
                foreach ($groups as $key => $groupUser) {
                    if($groupUser->getId() == $contestId) {
                        $entities = $em->getRepository('DwfPronosticsBundle:Standing')->getByEventAndGroup($event, $groupUser);
                        break;
                    }
                }
            }
            else $entities = "";
            return array(
                    'contest'                   => $contest,
                    'user'                      => $user,
                    'event'                     => $event,
                    'currentChampionshipDay'    => $currentChampionshipDay,
                    'entities'                  => $entities,
                    'group'                     => $groupUser,
            );
        }
        else return $this->redirect($this->generateUrl('events'));
    }

    /**
     * Admin for a contest
     *
     * @Route("/contests/{contestId}/configure", name="contest_admin")
     * @Method({"GET","PUT"})
     * @Template("DwfPronosticsBundle:Contest:admin.html.twig")
     */
    public function adminAction($contestId)
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $contest = $em->getRepository('DwfPronosticsBundle:Contest')->find($contestId);
        $event = $contest->getEvent();
        if($contest) {
            $championshipManager = $this->get('dwf_pronosticbundle.championshipmanager');
            $championshipManager->setEvent($event);
            $currentChampionshipDay = $championshipManager->getCurrentChampionshipDay();

            $invitationType = new InvitationContestFormType("Dwf\PronosticsBundle\Entity\Invitation");
            $invitation = new Invitation();
            //             $contest->setName('');
            $invitation->setUser($this->getUser());
            $invitation->setContest($contest);
            $form = $this->createForm($invitationType, $invitation, array(
                    'action' => '',
                    'method' => 'PUT',
            ));
            $form->add('submit', 'submit', array('label' => 'Send'));
            $form->handleRequest($request);
            if ($form->isValid()) {
                $invitation->setInvitationCode();
                $em->persist($invitation);
                $em->flush();
                return $this->redirect($this->generateUrl('contest_admin', array('contestId' => $contest->getId())));
            }
            $invitationForm = $form->createView();
            
            return array(
                    'contest'                   => $contest,
                    'user'                      => $this->getUser(),
                    'event'                     => $event,
                    'currentChampionshipDay'    => $currentChampionshipDay,
                    'invitationForm'            => $invitationForm,
            );
        }
        else return $this->redirect($this->generateUrl('events'));
    }

    /**
     * Lists all Game entities of an event
     *
     * @Route("/contest/{contestId}/games", name="contest_games")
     * @Method({"GET","POST", "PUT"})
     * @Template("DwfPronosticsBundle:Contest:games.html.twig")
     */
    public function gamesAction($contestId)
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $contest = $em->getRepository('DwfPronosticsBundle:Contest')->find($contestId);
        $event = $contest->getEvent();
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
            return array(
                    'contest'                       => $contest,
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
            );
        }
        else return $this->redirect($this->generateUrl('events'));
    }

    /**
     * Lists all Game entities by GameType
     *
     * @Route("/contest/{contestId}/games/type/{type}", name="contest_games_by_type")
     * @Method({"GET","POST", "PUT"})
     * @Template("DwfPronosticsBundle:Contest:games.html.twig")
     */
    public function gamesByGameTypeAction($type, $contestId)
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $contest = $em->getRepository('DwfPronosticsBundle:Contest')->find($contestId);
        $event = $contest->getEvent();
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
                $pronostics = "";
                $nbPointsWonByChampionshipDay = 0;
                $nbPronostics = 0;
                $nbPerfectScore = $nbGoodScore = $nbBadScore = 0;
            }
            return array(
                    'contest' => $contest,
                    'teams'	=> $arrayTeams,
                    'results' => $results,
                    'event' => $event,
                    'currentChampionshipDay' => $currentChampionshipDay,
                    'nbPointsWonByChampionshipDay' => $nbPointsWonByChampionshipDay,
                    'user' => $this->getUser(),
                    'entity'    => $gameType,
                    'games' => $games,
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
    
    /**
     * Finds and displays a Game entity.
     *
     * @Route("/contest/{contestId}/game/{id}", name="contest_game_show")
     * @Method({"GET","POST", "PUT"})
     * @Template("DwfPronosticsBundle:Contest:game-show.html.twig")
     */
    public function showGameAction($id, $contestId)
    {
        $em = $this->getDoctrine()->getManager();
        $contest = $em->getRepository('DwfPronosticsBundle:Contest')->find($contestId);
        $event = $contest->getEvent();
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
    
        return array(
                'contest'               => $contest,
                'event'                 => $event,
                'user'                  => $this->getUser(),
                'currentChampionshipDay' => $currentChampionshipDay,
                'entity'                => $entity,
                'pronostic'             => $pronostic,
                'nextGame'              => $nextGame,
                'pronosticNextGame'     => $pronosticNextGame,
                'pronostics'            => $pronostics,
                'scorersTeam1'          => $scorersTeam1,
                'scorersTeam2'          => $scorersTeam2,
                'form'                  => $entity->getEvent()->getSimpleBet() ? $form->createView():'',
        );
    }
    
    /**
     * Displays Pronostics for a specific contest.
     *
     * @Route("/contests/{contestId}/pronostics", name="contest_pronostics")
     * @Method({"GET","POST", "PUT"})
     * @Template("DwfPronosticsBundle:Contest:pronostics.html.twig")
     */
    public function pronosticsAction($contestId)
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $contest = $em->getRepository('DwfPronosticsBundle:Contest')->find($contestId);
        $event = $contest->getEvent();
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
            $pronostic = $em->getRepository('DwfPronosticsBundle:BestScorerPronostic')->findOneByUserAndEvent($this->getUser(), $event);
            if($pronostic)
                $bestscorer_pronostic = $pronostic[0];

            $entities = $em->getRepository('DwfPronosticsBundle:Pronostic')->findByUserAndEvent($this->getUser(), $event, 0);
            $nb = $em->getRepository('DwfPronosticsBundle:Pronostic')->getNbByUserAndEvent($this->getUser(), $event);
            $nbPerfectScore = $em->getRepository('DwfPronosticsBundle:Pronostic')->getNbScoreByUserAndEventAndResult($this->getUser(), $event, 5);
            $nbGoodScore = $em->getRepository('DwfPronosticsBundle:Pronostic')->getNbScoreByUserAndEventAndResult($this->getUser(), $event, 3);
            $nbBadScore = $em->getRepository('DwfPronosticsBundle:Pronostic')->getNbScoreByUserAndEventAndResult($this->getUser(), $event, 1);
            $total = $em->getRepository('DwfPronosticsBundle:Pronostic')->getResultsByEventAndUser($event, $this->getUser());
            if($event->getSimpleBet()) {
                $forms = array();
                $i = 0;
                foreach($entities as $entity)
                {
                    $simpleType = new SimplePronosticType();
                    $simpleType->setName($entity->getGame()->getId());
                    $form = $this->createForm($simpleType, $entity, array(
                            'action' => '',
                            'method' => 'PUT',
                    ));
                    $form->handleRequest($request);
                    if ($form->isValid()) {
                        $em->persist($entity);
                        $em->flush();
                    }

                    array_push($forms, $form->createView());
                    $i++;
                }
            }
            else $forms = "";
            return array(
                    'contest'                       => $contest,
                    'event'     => $event,
                    'currentChampionshipDay' => $currentChampionshipDay,
                    'user'		=> $this->getUser(),
                    'entities'	=> $entities,
                    'bestscorer_pronostic' => $pronostic ? $bestscorer_pronostic : '',
                    'nbPronostics' => $nb,
                    'nbPerfectScore' => $nbPerfectScore,
                    'nbGoodScore' => $nbGoodScore,
                    'nbBadScore' => $nbBadScore,
                    'total'         => $total,
                    'forms'	=> $forms,
            );
        }
        else throw $this->createNotFoundException('Unable to find Event entity.');
    }
}