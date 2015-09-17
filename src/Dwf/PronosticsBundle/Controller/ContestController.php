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
     * @Route("/contests/{contestId}/admin", name="contest_admin")
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