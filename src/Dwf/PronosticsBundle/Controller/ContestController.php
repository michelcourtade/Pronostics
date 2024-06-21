<?php

namespace Dwf\PronosticsBundle\Controller;

use Dwf\PronosticsBundle\Championship\HighchartManager;
use Dwf\PronosticsBundle\Championship\ScoreManager;
use Dwf\PronosticsBundle\Entity\ChatMessage;
use Dwf\PronosticsBundle\Entity\Contest;
use Dwf\PronosticsBundle\Entity\ContestMessage;
use Dwf\PronosticsBundle\Entity\ContestRepository;
use Dwf\PronosticsBundle\Entity\Invitation;
use Dwf\PronosticsBundle\Entity\Pronostic;
use Dwf\PronosticsBundle\Entity\Repository\ChatMessageRepository;
use Dwf\PronosticsBundle\Form\SimplePronosticType;
use Dwf\PronosticsBundle\Form\Type\ChatMessageFormType;
use Dwf\PronosticsBundle\Form\Type\ContestFormType;
use Dwf\PronosticsBundle\Form\Type\ContestMessageFormType;
use Dwf\PronosticsBundle\Form\Type\ContestType;
use Dwf\PronosticsBundle\Form\Type\CreateContestFormType;
use Dwf\PronosticsBundle\Form\Type\InvitationContestFormType;
use Dwf\PronosticsBundle\Form\Type\InvitationContestOpenFormType;
use Pusher\Pusher;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

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
        $user = $this->getUser();
        $event = $em->getRepository('DwfPronosticsBundle:Event')->find($event);
        if($this->getUser()) {
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
            $contest->setOwner($this->getUser());
            $contest->setEvent($event);
            $form = $this->createForm($contestType, $contest, array(
                    'action' => $this->generateUrl('contests', array('event' => $event->getId())),
                    'method' => 'PUT',
            ));
            $form->add('submit', 'submit', array('label' => $this->get('translator')->trans('Create')));
            $form->handleRequest($request);
            if ($form->isValid()) {
                $contest->setName($this->getUser()->__toString().'-'.$contest->getContestName().'-'.uniqid());
                $em->persist($contest);
                $user->addGroup($contest);
                $em->persist($user);
                $em->flush();
                $this->addFlash(
                        'success',
                        $this->get('translator')->trans('Your contest has been created.')
                );
                return $this->redirect($this->generateUrl('events'));
            }
            $contestForm = $form->createView();
        } else {
            $myContests = $otherContests = $contestForm = "";
        }

        return array("myContests" => $myContests,
                     "otherContests" => $otherContests,
                     "contestForm"=> $contestForm);
    }

    /**
     * Display a form to create a contest
     *
     * @Route("/contest/create", name="contest_create")
     * @Method({"GET","POST", "PUT"})
     * @Template("DwfPronosticsBundle:Contest:create.html.twig")
     */
    public function createAction()
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $user = $this->getUser();

        $events = $em->getRepository('DwfPronosticsBundle:Event')->findAllOrderedByDate();
        $contestType = new CreateContestFormType("Dwf\PronosticsBundle\Entity\User");
        $contest = new Contest('');
        $contest->setOwner($this->getUser());
        //$contest->setEvent($event);
        $form = $this->createForm($contestType, $contest, array(
                'action' => '',
                'method' => 'PUT',
        ));
        $form->add('submit', 'submit', array('label' => $this->get('translator')->trans('Create')));
        $form->handleRequest($request);
        if ($form->isValid()) {
            $contest->setName($this->getUser()->__toString().'-'.$contest->getContestName().'-'.uniqid());
            $em->persist($contest);

            $user->addGroup($contest);
            $em->persist($user);
            $em->flush();

            $this->addFlash(
                    'success',
                    $this->get('translator')->trans('Your contest has been created.')
            );
            return $this->redirect($this->generateUrl('events'));
        }
        $contestForm = $form->createView();

        return array(
                "events" => $events,
                "contestForm" => $contestForm
        );

    }

    /**
     * Display a form to join a contest
     *
     * @Route("/contest/join", name="contest_join")
     * @Method({"GET","POST", "PUT"})
     * @Template("DwfPronosticsBundle:Contest:join.html.twig")
     */
    public function joinAction()
    {
        $em      = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $user    = $this->getUser();

        $invitationContestOpenType = new InvitationContestOpenFormType("Dwf\PronosticsBundle\Entity\Invitation");
        $invitationContest         = new Invitation();
        $formContest               = $this->createForm($invitationContestOpenType, $invitationContest, array(
                'action' => '',
                'method' => 'POST',
        ));
        $formContest->add('code', 'text', array('label' => $this->get('translator')->trans('Your invitation code')));
        $formContest->add('submit', 'submit', array('label' => $this->get('translator')->trans('Join with code')));
        $formContest->handleRequest($request);
        if ($formContest->isValid()) {
            $code = $formContest->getData()->getCode();
            $invitationFromContest = $em->getRepository('DwfPronosticsBundle:Invitation')->findOneByCode($code);
            if ($invitationFromContest->getCode() == $code) {
                $contest = $invitationFromContest->getContest();
                $user->addGroup($contest);
                $em->persist($user);
                $em->flush();
            }
            $this->addFlash(
                    'success',
                    $this->get('translator')->trans('You can now play in this contest : ').$contest->getContestName().' !'
            );

            return $this->redirect($this->generateUrl('contest_show', array('contestId' => $contest->getId())));
        }
        $invitationFormContest = $formContest->createView();

        return array(
                'form' => $invitationFormContest,
        );
    }

    /**
     * Display infos on a contest when you're in
     *
     * @Route("/contest/{contestId}/infos", name="contest_infos")
     * @Method("GET")
     * @Template("DwfPronosticsBundle:Contest:infos.html.twig")
     */
    public function infosAction($contestId)
    {
        $em      = $this->getDoctrine()->getManager();
        $request = $this->getRequest();

        $chatMessageRepository = $em->getRepository('DwfPronosticsBundle:ChatMessage');
        $contest               = $em->getRepository('DwfPronosticsBundle:Contest')->find($contestId);
        $event                 = $contest->getEvent();
        $users                 = $em->getRepository('DwfPronosticsBundle:User')->getOtherUsersByContest($this->getUser(), $contest);

        $contestMessage = $em->getRepository('DwfPronosticsBundle:ContestMessage')->findByContest($contest);
        if (!$contestMessage) {
            $contestMessage = new ContestMessage();
            $contestMessage->setContest($contest);
            $contestMessage->setUser($this->getUser());
            $contestMessage->setDate(date("YmdHis"));
            $messageForContest = null;
        } else {
            $contestMessage    = $contestMessage[0];
            $messageForContest = $contestMessage;
        }
        $adminMessage = $em->getRepository('DwfPronosticsBundle:AdminMessage')->findLast();

        $messageChatContestType = new ChatMessageFormType("Dwf\PronosticsBundle\Entity\ChatMessage");
        $chatMessage = new ChatMessage();
        $chatMessage->setUser($this->getUser());
        $chatMessage->setContest($contest);
        $chatMessage->setEvent($contest->getEvent());
        $formMessage = $this->createForm($messageChatContestType, $chatMessage, array(
            'action' => $this->generateUrl('contest_send_message', array('contestId' => $contest->getId())),
            'method' => 'PUT',
        ));
        $formMessage->add('submit', 'submit', array(
            'label'        => $this->get('translator')->trans('Send'),
            'button_class' => 'btn btn-warning btn-sm',
        ));

        $countMessages = $chatMessageRepository->getCountMessagesForContest($contest);
        $offset        = max($countMessages - 20, 0);
        $chatMessages  = $chatMessageRepository->getLastMessagesByContest($contest, $offset, $countMessages);

        return array(
                'contest'                   => $contest,
                'user'                      => $this->getUser(),
                'event'                     => $event,
                'users'                     => $users,
                'messageForContest'         => $messageForContest,
                'adminMessage'              => $adminMessage,
                'anchorDate'                => '',
                'formMessage'               => $formMessage->createView(),
                'chatMessages'              => $chatMessages,
                'pusher_auth_key'           => $this->container->getParameter('pusher_auth_key'),
        );
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
        $translator = $this->get('translator');

        $chatMessageRepository = $em->getRepository('DwfPronosticsBundle:ChatMessage');
        $contest = $em->getRepository('DwfPronosticsBundle:Contest')->find($contestId);
        $standings = $em->getRepository('DwfPronosticsBundle:Standing')->getByContest($contest);

        $event = $contest->getEvent();
        $user = $this->getUser();
        if (!$user->hasGroup($contest->getName())) {
            return $this->redirect($this->generateUrl('events'));
        }

        if ($standings) {
            $position = 1;
            foreach ($standings as $standing) {
                if ($standing[0]->getUser()->getId() == $user->getId()) {
                    $points = $standings[0]['total'];
                    break;
                }
                $position++;
            }
        } else {
            $position = 0;
        }

        $currentChampionshipDay = '';
        if ($event->getChampionship()) {
            $championshipManager = $this->get('dwf_pronosticbundle.championshipmanager');
            $championshipManager->setEvent($event);
            $currentChampionshipDay = $championshipManager->getCurrentChampionshipDay();
        }

        $players = $em->getRepository('DwfPronosticsBundle:Player')->findAll();
        $arrayPlayers = array();
        if ($players) {
            foreach ($players as $player) {
                $arrayPlayers[$player->getId()] = $player;
            }
        }

        $teams = $em->getRepository('DwfPronosticsBundle:Team')->findAll();
        $arrayTeams = array();
        if ($teams) {
            foreach ($teams as $team) {
                $arrayTeams[$team->getId()] = $team;
            }
        }
        $scoreManager = $this->get('dwf_pronosticbundle.score_manager');
        $scores = $scoreManager->buildScoresForContestAndUser($contest, $this->getUser());

        /** @var HighchartManager $chartManager */
        $chartManager = $this->get('dwf_pronosticbundle.highchartmanager');
        $betsChart = $chartManager->buildBetsChartWithScores('piechart', $translator->trans('Bets distribution'), $scores);
        $betPointsChart = $chartManager->buildBetPointsChartWithScores('piechart2', $translator->trans('Bet points distribution'), $scores);

        $contestMessage = $em->getRepository('DwfPronosticsBundle:ContestMessage')->findByContest($contest);
        $messageForContest = null;
        if($contestMessage) {
            $messageForContest = $contestMessage[0];
        }
        $adminMessage = $em->getRepository('DwfPronosticsBundle:AdminMessage')->findLast();

        $messageChatContestType = new ChatMessageFormType("Dwf\PronosticsBundle\Entity\ChatMessage");
        $chatMessage = new ChatMessage();
        $chatMessage->setUser($this->getUser());
        $chatMessage->setContest($contest);
        $chatMessage->setEvent($contest->getEvent());
        $formMessage = $this->createForm($messageChatContestType, $chatMessage, array(
            'action' => $this->generateUrl('contest_send_message', array('contestId' => $contest->getId())),
            'method' => 'PUT',
        ));
        $formMessage->add('submit', 'submit', array(
            'label' => $translator->trans('Send'),
            'button_class' => 'btn btn-warning btn-sm',
        ));

        $countMessages = $chatMessageRepository->getCountMessagesForContest($contest);
        $offset = max($countMessages - 20, 0);
        $chatMessages = $chatMessageRepository->getLastMessagesByContest($contest, $offset, $countMessages);

        return array(
            'contest'=> $contest,
            'user' => $this->getUser(),
            'event' => $event,
            'currentChampionshipDay'=> $currentChampionshipDay,
            'teams' => $arrayTeams,
            'players' => $arrayPlayers,
            'scores' => $scores,
            'messageForContest' => $messageForContest,
            'adminMessage' => $adminMessage,
            'formMessage' => $formMessage->createView(),
            'chatMessages'=> $chatMessages,
            'pusher_auth_key'=> $this->container->getParameter('pusher_auth_key'),
            'betsChart' => $scores['totalScores'] > 0 ? $betsChart : null,
            'betPointsChart' => $scores['totalScores'] ? $betPointsChart : null,
            'position' => $position,
            'points' => $points,
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

        $chatMessageRepository = $em->getRepository('DwfPronosticsBundle:ChatMessage');
        $contest               = $em->getRepository('DwfPronosticsBundle:Contest')->find($contestId);
        $event                 = $contest->getEvent();

        if (!$contest) {
            return $this->redirect($this->generateUrl('events'));
        }

        $championshipManager = $this->get('dwf_pronosticbundle.championshipmanager');
        $championshipManager->setEvent($event);
        $currentChampionshipDay = $championshipManager->getCurrentChampionshipDay();

        $user = $this->getUser();
        $entities          = $em->getRepository('DwfPronosticsBundle:Standing')->getByContest($contest);
        $contestMessage    = $em->getRepository('DwfPronosticsBundle:ContestMessage')->findByContest($contest);
        $messageForContest = null;
        if($contestMessage) {
            $messageForContest = $contestMessage[0];
        }
        $adminMessage = $em->getRepository('DwfPronosticsBundle:AdminMessage')->findLast();

        $messageChatContestType = new ChatMessageFormType("Dwf\PronosticsBundle\Entity\ChatMessage");
        $chatMessage = new ChatMessage();
        $chatMessage->setUser($this->getUser());
        $chatMessage->setContest($contest);
        $chatMessage->setEvent($contest->getEvent());
        $formMessage = $this->createForm($messageChatContestType, $chatMessage, array(
            'action' => $this->generateUrl('contest_send_message', array('contestId' => $contest->getId())),
            'method' => 'PUT',
        ));
        $formMessage->add('submit', 'submit', array(
            'label'        => $this->get('translator')->trans('Send'),
            'button_class' => 'btn btn-warning btn-sm',
        ));

        $countMessages = $chatMessageRepository->getCountMessagesForContest($contest);
        $offset        = max($countMessages - 20, 0);
        $chatMessages  = $chatMessageRepository->getLastMessagesByContest($contest, $offset, $countMessages);

        return array(
                'contest'                   => $contest,
                'user'                      => $user,
                'event'                     => $event,
                'currentChampionshipDay'    => $currentChampionshipDay,
                'entities'                  => $entities,
                'messageForContest'         => $messageForContest,
                'adminMessage'              => $adminMessage,
                'formMessage'               => $formMessage->createView(),
                'chatMessages'              => $chatMessages,
                'pusher_auth_key'           => $this->container->getParameter('pusher_auth_key'),
        );
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
        $em      = $this->getDoctrine()->getManager();
        $request = $this->getRequest();

        $chatMessageRepository = $em->getRepository('DwfPronosticsBundle:ChatMessage');
        $contest               = $em->getRepository('DwfPronosticsBundle:Contest')->find($contestId);
        $event                 = $contest->getEvent();

        if(!$contest) {
            return $this->redirect($this->generateUrl('events'));
        }
        $championshipManager = $this->get('dwf_pronosticbundle.championshipmanager');
        $championshipManager->setEvent($event);
        $currentChampionshipDay = $championshipManager->getCurrentChampionshipDay();

        $invitationsAlreadySent = $em->getRepository('DwfPronosticsBundle:Invitation')->findAllByUserAndContest($this->getUser(), $contest);
        $invitationType = new InvitationContestFormType("Dwf\PronosticsBundle\Entity\Invitation");
        $invitation = new Invitation();
        $invitation->setUser($this->getUser());
        $invitation->setContest($contest);
        $form = $this->createForm($invitationType, $invitation, array(
                'action' => '',
                'method' => 'PUT',
        ));
        $form->add('submit', 'submit', array('label' => $this->get('translator')->trans('Send')));
        $form->handleRequest($request);
        if ($form->isValid()) {
            $existingInvitation = $em->getRepository('DwfPronosticsBundle:Invitation')->findAllByEmailAndContest($invitation->getEmail(), $contest);
            if ($existingInvitation) {
                $this->addFlash(
                        'info',
                        $this->get('translator')->trans('Invitation already sent for ').$invitation->getEmail().' !'
                );
            } else {
                $invitation->setInvitationCode();
                $invitation->setSent(true);
                $em->persist($invitation);
                $em->flush();
                $this->get('dwf_pronosticbundle.user_swift_mailer')->sendInvitationEmailMessage($this->getUser(), $invitation);
                $this->addFlash(
                        'success',
                        $this->get('translator')->trans('Your invitation has been sent to ').$invitation->getEmail().' !'
                );
            }

            return $this->redirect($this->generateUrl('contest_admin', array('contestId' => $contest->getId())));
        }
        $invitationForm = $form->createView();
        $users = $em->getRepository('DwfPronosticsBundle:User')->getOtherUsersByContest($this->getUser(), $contest);

        $contestType = new ContestType("Dwf\PronosticsBundle\Entity\Contest");
        $contestForm = $this->createForm($contestType, $contest, array(
                'action' => '',
                'method' => 'PUT',
        ));
        $contestForm->add('submit', 'submit', array('label' => $this->get('translator')->trans('Change name')));
        $contestForm->handleRequest($request);
        if ($contestForm->isValid()) {
            $em->persist($contest);
            $em->flush();
            $this->addFlash(
                    'success',
                    $this->get('translator')->trans('Your contest name has changed to ').$contest->getContestName().' !'
            );
            return $this->redirect($this->generateUrl('contest_admin', array('contestId' => $contest->getId())));
        }
        $contestForm = $contestForm->createView();

        // invitation without an email => open contest with invitation code
        $invitationContestOpenType = new InvitationContestOpenFormType("Dwf\PronosticsBundle\Entity\Invitation");
        $invitationContest = $em->getRepository('DwfPronosticsBundle:Invitation')->findAllByUserAndContestAndEmail($this->getUser(), $contest, null);
        if (!$invitationContest) {
            $invitationContest = new Invitation();
            $invitationContest->setUser($this->getUser());
            $invitationContest->setContest($contest);
        }
        $formContest = $this->createForm($invitationContestOpenType, $invitationContest, array(
                'action' => '',
                'method' => 'PUT',
        ));
        $formContest->add('submit', 'submit', array('label' => $this->get('translator')->trans('Generate new access code')));
        $formContest->handleRequest($request);
        if ($formContest->isValid()) {
            $invitationContest->setInvitationCode();
            $invitationContest->setSent(true);
            $em->persist($invitationContest);
            $em->flush();
            $this->addFlash(
                    'success',
                    $this->get('translator')->trans('Your contest is now open to others with one unique code : ').$invitationContest->getCode().' !'
            );


            return $this->redirect($this->generateUrl('contest_admin', array('contestId' => $contest->getId())));
        }
        $invitationFormContest = $formContest->createView();

        $contestMessage = $em->getRepository('DwfPronosticsBundle:ContestMessage')->findByContest($contest);
        if (!$contestMessage) {
            $contestMessage = new ContestMessage();
            $contestMessage->setContest($contest);
            $contestMessage->setUser($this->getUser());
            $contestMessage->setDate(date("YmdHis"));
            $messageForContest = null;
        } else {
            $contestMessage = $contestMessage[0];
            $messageForContest = $contestMessage;
        }

        $contestMessageType = new ContestMessageFormType("Dwf\PronosticsBundle\Entity\ContestMessage");
        $contestMessageForm = $this->createForm($contestMessageType, $contestMessage, array(
                'action' => '',
                'method' => 'PUT',
        ));
        $contestMessageForm->add('submit', 'submit', array('label' => $this->get('translator')->trans('Post your message')));
        $contestMessageForm->handleRequest($request);
        if ($contestMessageForm->isValid()) {
            $em->persist($contestMessage);
            $em->flush();
            $this->addFlash(
                    'success',
                    $this->get('translator')->trans('Your message has been added to the contest')
            );

            return $this->redirect($this->generateUrl('contest_admin', array('contestId' => $contest->getId())));
        }
        $contestMessageForm = $contestMessageForm->createView();
        $adminMessage       = $em->getRepository('DwfPronosticsBundle:AdminMessage')->findLast();

        $messageChatContestType = new ChatMessageFormType("Dwf\PronosticsBundle\Entity\ChatMessage");
        $chatMessage = new ChatMessage();
        $chatMessage->setUser($this->getUser());
        $chatMessage->setContest($contest);
        $chatMessage->setEvent($contest->getEvent());
        $formMessage = $this->createForm($messageChatContestType, $chatMessage, array(
            'action' => $this->generateUrl('contest_send_message', array('contestId' => $contest->getId())),
            'method' => 'PUT',
        ));
        $formMessage->add('submit', 'submit', array(
            'label'        => $this->get('translator')->trans('Send'),
            'button_class' => 'btn btn-warning btn-sm',
        ));

        $countMessages = $chatMessageRepository->getCountMessagesForContest($contest);
        $offset        = max($countMessages - 20, 0);
        $chatMessages  = $chatMessageRepository->getLastMessagesByContest($contest, $offset, $countMessages);

        return array(
                'contest'                   => $contest,
                'user'                      => $this->getUser(),
                'event'                     => $event,
                'currentChampionshipDay'    => $currentChampionshipDay,
                'invitationForm'            => $invitationForm,
                'invitationFormContest'     => $invitationFormContest,
                'invitationCode'            => $invitationContest->getCode() ? $invitationContest->getCode() : null,
                'invitationsAlreadySent'    => $invitationsAlreadySent,
                'users'                     => $users,
                'contestForm'               => $contestForm,
                'contestMessageForm'        => $contestMessageForm,
                'messageForContest'         => $messageForContest,
                'adminMessage'              => $adminMessage,
                'anchorDate'                => '',
                'formMessage'               => $formMessage->createView(),
                'chatMessages'              => $chatMessages,
                'pusher_auth_key'           => $this->container->getParameter('pusher_auth_key'),
        );
    }

    /**
     * Delete a contest created by current user
     *
     * @Route("/contest/{contestId}/delete", name="contest_delete")
     * @Method("GET")
     * @Template()
     */
    public function deleteAction($contestId)
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $contest = $em->getRepository('DwfPronosticsBundle:Contest')->find($contestId);
        $event = $contest->getEvent();
        if($contest) {
            if($contest->getOwner() == $this->getUser()) {
                $em->remove($contest);
                $em->flush();
                $this->addFlash(
                        'success',
                        $this->get('translator')->trans('Your contest has been successfully deleted.')
                );
                return $this->redirect($this->generateUrl('events'));
            }
            $this->addFlash(
                    'danger',
                    $this->get('translator')->trans('You can\'t delete contest from another user.')
            );
            return $this->redirect($this->generateUrl('events'));

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
        $em                    = $this->getDoctrine()->getManager();
        $request               = $this->getRequest();
        $chatMessageRepository = $em->getRepository('DwfPronosticsBundle:ChatMessage');
        $contest               = $em->getRepository('DwfPronosticsBundle:Contest')->find($contestId);
        $event                 = $contest->getEvent();
        $types                 = $em->getRepository('DwfPronosticsBundle:GameType')->findAllByEvent($event);
        $anchorDate            = '';
        $user = $this->getUser();
        if (!$event) {
            return $this->redirect($this->generateUrl('events'));
        }
        $currentChampionshipDay = '';
        if ($event->getChampionship()) {
            $championshipManager = $this->get('dwf_pronosticbundle.championshipmanager');
            $championshipManager->setEvent($event);
            $currentChampionshipDay = $championshipManager->getCurrentChampionshipDay();
        }
        $games = $em->getRepository('DwfPronosticsBundle:Game')->findAllByEventOrderedByDate($event);
        if ($event->getChampionship()) {
            $gameTypeId = $em->getRepository('DwfPronosticsBundle:GameTypeResult')->getMaxGameTypeIdByEvent($event);
            if (!$gameTypeId) {
                $firstGameType = $em->getRepository('DwfPronosticsBundle:GameType')->getFirstByEvent($event);
                $gameTypeId = $firstGameType[0]->getId();
            }
            $gameType = $em->getRepository('DwfPronosticsBundle:GameType')->find($gameTypeId);
            $results = $em->getRepository('DwfPronosticsBundle:GameTypeResult')->getResultsByGameTypeAndEvent($gameType, $event);
        } else {
            $results = $em->getRepository('DwfPronosticsBundle:GameTypeResult')->getResultsByEvent($event);
        }
        $teams = $em->getRepository('DwfPronosticsBundle:Team')->findAll();
        foreach ($teams as $team) {
            $arrayTeams[$team->getId()] = $team;
        }
        if ($event->getSimpleBet()) {
            $forms = array();
            $pronostics = array();
            $nbPointsWonByChampionshipDay = 0;
            $nbPronostics = 0;
            $nbPerfectScore = $nbGoodScore = $nbBadScore = 0;
            $i = 0;
            foreach($games as $entity) {
                $date = $entity->getDate()->format("Ymd");
                if ($date == date("Ymd")) {
                    $anchorDate = date('Ymd');
                }
                $pronostic = $em->getRepository('DwfPronosticsBundle:Pronostic')->findOneBy(array('user' => $this->getUser(), 'game' => $entity, 'contest' => $contest));
                if (!$pronostic) {
                    $pronostic = new Pronostic();
                    $pronostic->setGame($entity);
                    $pronostic->setUser($this->getUser());
                    $pronostic->setEvent($event);
                    $pronostic->setContest($contest);
                }
                if ($event->getScoreDiff()) {
                    $simpleType = new SimplePronosticType();
                } else {
                    $simpleType = new SimplePronosticType();
                }
                $simpleType->setName($entity->getId());
                $form = $this->createForm($simpleType, $pronostic, array(
                        'action' => '',
                        'method' => 'PUT',
                ));
                $form->handleRequest($request);
                if ($form->isValid()) {
                    if ($pronostic->getSimpleBet() == "N") {
                        $pronostic->setSliceScore(null);
                    }
                    $em->persist($pronostic);
                    if ($user->isUniqueBet()) {
                        foreach ($user->getGroups() as $group) {
                            if ($group->getId() != $contest->getId()) {
                                $otherContest = $em->getRepository('DwfPronosticsBundle:Contest')->find($group->getId());
                                if ($otherContest && $otherContest->getEvent() == $event) {
                                    $otherPronostic = $em->getRepository('DwfPronosticsBundle:Pronostic')->findOneBy(array('user' => $this->getUser(), 'game' => $entity, 'contest' => $otherContest));
                                    if (!$otherPronostic) {
                                        $otherPronostic = new Pronostic();
                                        $otherPronostic->setGame($entity);
                                        $otherPronostic->setUser($this->getUser());
                                        $otherPronostic->setEvent($event);
                                        $otherPronostic->setContest($otherContest);
                                    }
                                    $otherPronostic->setSimpleBet($pronostic->getSimpleBet());
                                    $otherPronostic->setSliceScore($pronostic->getSliceScore());
                                    $otherPronostic->setScoreTeam1($pronostic->getScoreTeam1());
                                    $otherPronostic->setScoreTeam2($pronostic->getScoreTeam2());
                                    $otherPronostic->setOvertime($pronostic->getOvertime());
                                    $otherPronostic->setScoreTeam1Overtime($pronostic->getScoreTeam1Overtime());
                                    $otherPronostic->setScoreTeam2Overtime($pronostic->getScoreTeam2Overtime());
                                    $otherPronostic->setWinner($pronostic->getWinner());
                                    $em->persist($otherPronostic);
                                }
                            }
                        }
                    }
                    $em->flush();
                }

                array_push($forms, $form->createView());
                array_push($pronostics, $pronostic);
                $i++;
            }
        } else {
            $forms = "";
            $pronostics = array();
            $nbPronostics = $nbPerfectScore = $nbGoodScore = $nbBadScore = 0;
            $nbPointsWonByChampionshipDay = 0;
            foreach($games as $entity) {
                $date = $entity->getDate()->format("Ymd");
                if ($date == date("Ymd")) {
                    $anchorDate = date('Ymd');
                }
                $pronostic = $em->getRepository('DwfPronosticsBundle:Pronostic')->findOneBy(array('user' => $this->getUser(), 'game' => $entity, 'contest' => $contest));
                array_push($pronostics, $pronostic);
            }
        }
        $contestMessage = $em->getRepository('DwfPronosticsBundle:ContestMessage')->findByContest($contest);
        $messageForContest = null;
        if ($contestMessage) {
            $messageForContest = $contestMessage[0];
        }

        $adminMessage = $em->getRepository('DwfPronosticsBundle:AdminMessage')->findLast();

        $messageChatContestType = new ChatMessageFormType("Dwf\PronosticsBundle\Entity\ChatMessage");
        $chatMessage = new ChatMessage();
        $chatMessage->setUser($this->getUser());
        $chatMessage->setContest($contest);
        $chatMessage->setEvent($contest->getEvent());
        $formMessage = $this->createForm($messageChatContestType, $chatMessage, array(
            'action' => $this->generateUrl('contest_send_message', array('contestId' => $contest->getId())),
            'method' => 'PUT',
        ));
        $formMessage->add('submit', 'submit', array(
            'label'        => $this->get('translator')->trans('Send'),
            'button_class' => 'btn btn-warning btn-sm',
        ));

        $countMessages = $chatMessageRepository->getCountMessagesForContest($contest);
        $offset        = max($countMessages - 20, 0);
        $chatMessages  = $chatMessageRepository->getLastMessagesByContest($contest, $offset, $countMessages);

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
                'gameType'                      => '',
                'messageForContest'             => $messageForContest,
                'adminMessage'                  => $adminMessage,
                'anchorDate'                    => $anchorDate,
                'formMessage'                   => $formMessage->createView(),
                'chatMessages'                  => $chatMessages,
                'pusher_auth_key'               => $this->container->getParameter('pusher_auth_key'),
        );
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
        $em      = $this->getDoctrine()->getManager();
        $request = $this->getRequest();

        $chatMessageRepository = $em->getRepository('DwfPronosticsBundle:ChatMessage');
        $contest               = $em->getRepository('DwfPronosticsBundle:Contest')->find($contestId);
        $event                 = $contest->getEvent();
        if ($event) {
            $currentChampionshipDay = '';
            $ob                     = '';
            if ($event->getChampionship()) {
                $championshipManager = $this->get('dwf_pronosticbundle.championshipmanager');
                $championshipManager->setEvent($event);
                $currentChampionshipDay = $championshipManager->getCurrentChampionshipDay();

                $usersResultsByType = $em->getRepository('DwfPronosticsBundle:Pronostic')->getResultsByContestAndType($contest, $type);
                $results = array();
                $pronos  = array();
                foreach ($usersResultsByType as $userResult)
                {
                    $result   = array($userResult['username'], intval($userResult['total']));
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
            $results = $em->getRepository('DwfPronosticsBundle:GameTypeResult')->getResultsByGameTypeAndEvent($type, $event);
            $teams = $em->getRepository('DwfPronosticsBundle:Team')->findAll();
            foreach ($teams as $team)
            {
                $arrayTeams[$team->getId()] = $team;
            }
            $gameType = $em->getRepository('DwfPronosticsBundle:GameType')->find($type);
            $games = $em->getRepository('DwfPronosticsBundle:Game')->findAllByTypeAndEvent($type, $event);
            $types = $em->getRepository('DwfPronosticsBundle:GameType')->findAllByEvent($event);

            if ($event->getSimpleBet()) {
                $forms = array();
                $pronostics = array();
                $nbPointsWonByChampionshipDay = 0;
                $nbPronostics = 0;
                $nbPerfectScore = $nbGoodScore = $nbBadScore = 0;
                $i = 0;
                foreach($games as $entity)
                {
                    $pronostic = $em->getRepository('DwfPronosticsBundle:Pronostic')->findOneBy(array(
                        'user'    => $this->getUser(),
                        'game'    => $entity,
                        'contest' => $contest));
                    if ($pronostic) {
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
                    } else {
                        $simplePronostic = new Pronostic();
                        $simplePronostic->setGame($entity);
                        $simplePronostic->setUser($this->getUser());
                        $simplePronostic->setEvent($entity->getEvent());
                        $simplePronostic->setContest($contest);
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
            } else {
                $forms                         = "";
                $pronostics                    = array();
                $nbPointsWonByChampionshipDay  = 0;
                $nbPronostics                  = 0;
                $nbPerfectScore = $nbGoodScore = $nbBadScore = 0;
                foreach($games as $entity)
                {
                    $pronostic = $em->getRepository('DwfPronosticsBundle:Pronostic')->findOneBy(array(
                        'user'    => $this->getUser(),
                        'game'    => $entity,
                        'contest' => $contest
                    ));
                    array_push($pronostics, $pronostic);
                }
            }
            $contestMessage = $em->getRepository('DwfPronosticsBundle:ContestMessage')->findByContest($contest);
            $messageForContest = null;
            if($contestMessage) {
                $messageForContest = $contestMessage[0];
            }
            $adminMessage = $em->getRepository('DwfPronosticsBundle:AdminMessage')->findLast();

            $messageChatContestType = new ChatMessageFormType("Dwf\PronosticsBundle\Entity\ChatMessage");
            $chatMessage = new ChatMessage();
            $chatMessage->setUser($this->getUser());
            $chatMessage->setContest($contest);
            $chatMessage->setEvent($contest->getEvent());
            $formMessage = $this->createForm($messageChatContestType, $chatMessage, array(
                'action' => $this->generateUrl('contest_send_message', array('contestId' => $contest->getId())),
                'method' => 'PUT',
            ));
            $formMessage->add('submit', 'submit', array(
                'label'        => $this->get('translator')->trans('Send'),
                'button_class' => 'btn btn-warning btn-sm',
            ));

            $countMessages = $chatMessageRepository->getCountMessagesForContest($contest);
            $offset        = max($countMessages - 20, 0);
            $chatMessages  = $chatMessageRepository->getLastMessagesByContest($contest, $offset, $countMessages);

            return array(
                    'contest'                       => $contest,
                    'teams'                         => $arrayTeams,
                    'results'                       => $results,
                    'event'                         => $event,
                    'currentChampionshipDay'        => $currentChampionshipDay,
                    'nbPointsWonByChampionshipDay'  => $nbPointsWonByChampionshipDay,
                    'user'                          => $this->getUser(),
                    'gameType'                      => $gameType,
                    'games'                         => $games,
                    'types'                         => $types,
                    'forms'                         => $forms,
                    'pronostics'                    => $pronostics,
                    'nbPronostics'                  => $nbPronostics,
                    'nbPerfectScore'                => $nbPerfectScore,
                    'nbGoodScore'                   => $nbGoodScore,
                    'nbBadScore'                    => $nbBadScore,
                    'chart'                         => $ob,
                    'messageForContest'             => $messageForContest,
                    'adminMessage'                  => $adminMessage,
                    'anchorDate'                    => '',
                    'formMessage'                   => $formMessage->createView(),
                    'chatMessages'                  => $chatMessages,
                    'pusher_auth_key'               => $this->container->getParameter('pusher_auth_key'),
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
        $request = $this->getRequest();

        $chatMessageRepository = $em->getRepository('DwfPronosticsBundle:ChatMessage');
        $gameRepository = $em->getRepository('DwfPronosticsBundle:Game');

        $contest = $em->getRepository('DwfPronosticsBundle:Contest')->find($contestId);
        $entity = $em->getRepository('DwfPronosticsBundle:Game')->find($id);
        $event = $contest->getEvent();

        $scorersTeam1 = "";
        $scorersTeam2 = "";
        if ($entity->getScoreTeam1() > 0 || $entity->getScoreTeam1Overtime() > 0) {
            $scorersTeam1 = $em->getRepository('DwfPronosticsBundle:Scorer')
                                ->findScorersByGameAndTeam($entity, $entity->getTeam1(), $event->getNationalTeams());
        }
        if ($entity->getScoreTeam2() > 0 || $entity->getScoreTeam2Overtime() > 0) {
            $scorersTeam2 = $em->getRepository('DwfPronosticsBundle:Scorer')
                ->findScorersByGameAndTeam($entity, $entity->getTeam2(), $event->getNationalTeams());
        }

        $nextGame = $gameRepository->findNextGameAfter($entity);
        $allTeamGamesOnEvent = $gameRepository->findAllTeamGamesOnEvent([$entity->getTeam1(), $entity->getTeam2()], $event);
        $pronosticNextGame = $em->getRepository('DwfPronosticsBundle:Pronostic')->findOneBy(array('user' => $this->getUser(), 'game' => $nextGame, 'contest' => $contest));
        $pronostic = $em->getRepository('DwfPronosticsBundle:Pronostic')->findOneBy(array('user' => $this->getUser(), 'game' => $entity, 'contest' => $contest));

        if ($entity->getEvent()->getSimpleBet()) {
            if (!$pronostic) {
                $pronostic = new Pronostic();
                $pronostic->setGame($entity);
                $pronostic->setUser($this->getUser());
                $pronostic->setEvent($entity->getEvent());
                $pronostic->setContest($contest);
            }
            $simpleType = new SimplePronosticType();
            $simpleType->setName($entity->getId());
            $form = $this->createForm($simpleType, $pronostic, array(
                    'action' => '',
                    'method' => 'PUT',
            ));
            $form->handleRequest($request);
            if ($form->isValid()) {
                if ($pronostic->getSimpleBet() == "N") {
                    $pronostic->setSliceScore(null);
                }
                $em->persist($pronostic);
                $em->flush();
            }
        }

        $pronostics = "";
        if ($entity->hasBegan() || $entity->getPlayed()) {
            $user = $this->getUser();
            $groups = $user->getGroups();
            $pronostics = $em->getRepository('DwfPronosticsBundle:Pronostic')->findAllByGameAndContest($entity, $contest);
        }

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Game entity.');
        }
        $event = $entity->getEvent();
        $currentChampionshipDay = '';
        if ($event->getChampionship()) {
            $championshipManager = $this->get('dwf_pronosticbundle.championshipmanager');
            $championshipManager->setEvent($event);
            $currentChampionshipDay = $championshipManager->getCurrentChampionshipDay();
        }

        $contestMessage = $em->getRepository('DwfPronosticsBundle:ContestMessage')->findByContest($contest);
        $messageForContest = null;
        if ($contestMessage) {
            $messageForContest = $contestMessage[0];
        }
        $adminMessage = $em->getRepository('DwfPronosticsBundle:AdminMessage')->findLast();

        $messageChatContestType = new ChatMessageFormType("Dwf\PronosticsBundle\Entity\ChatMessage");
        $chatMessage = new ChatMessage();
        $chatMessage->setUser($this->getUser());
        $chatMessage->setContest($contest);
        $chatMessage->setEvent($contest->getEvent());
        $formMessage = $this->createForm($messageChatContestType, $chatMessage, array(
            'action' => $this->generateUrl('contest_send_message', array('contestId' => $contest->getId())),
            'method' => 'PUT',
        ));
        $formMessage->add('submit', 'submit', array(
            'label'        => $this->get('translator')->trans('Send'),
            'button_class' => 'btn btn-warning btn-sm',
        ));

        $countMessages = $chatMessageRepository->getCountMessagesForContest($contest);
        $offset = max($countMessages - 20, 0);
        $chatMessages = $chatMessageRepository->getLastMessagesByContest($contest, $offset, $countMessages);

        return array(
            'contest' => $contest,
            'event' => $event,
            'user' => $this->getUser(),
            'currentChampionshipDay' => $currentChampionshipDay,
            'entity' => $entity,
            'pronostic' => $pronostic,
            'nextGame' => $nextGame,
            'pronosticNextGame' => $pronosticNextGame,
            'pronostics' => $pronostics,
            'scorersTeam1' => $scorersTeam1,
            'scorersTeam2' => $scorersTeam2,
            'form' => $entity->getEvent()->getSimpleBet() ? $form->createView():'',
            'messageForContest' => $messageForContest,
            'adminMessage' => $adminMessage,
            'formMessage' => $formMessage->createView(),
            'chatMessages' => $chatMessages,
            'pusher_auth_key' => $this->container->getParameter('pusher_auth_key'),
            'allTeamGamesOnEvent' => $allTeamGamesOnEvent,
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
        $translator = $this->get('translator');
        $user = $this->getUser();

        $contest = $em->getRepository('DwfPronosticsBundle:Contest')->find($contestId);
        $chatMessageRepository = $em->getRepository('DwfPronosticsBundle:ChatMessage');

        $event = $contest->getEvent();
        if ($event) {
            $currentChampionshipDay = '';
            if ($event->getChampionship()) {
                $lastGamePlayed = $em->getRepository('DwfPronosticsBundle:Game')->findLastGamePlayedByEvent($event);
                $currentChampionshipDay = '';
                if (count($lastGamePlayed) > 0) {
                    $lastGamePlayed = $lastGamePlayed[0];
                    $gamesLeftInChampionshipDay = $em->getRepository('DwfPronosticsBundle:Game')->findGamesLeftByEventAndGameType($event, $lastGamePlayed->getType());
                    if ($gamesLeftInChampionshipDay) {
                        $currentChampionshipDay = $em->getRepository('DwfPronosticsBundle:GameType')->find($lastGamePlayed->getType());
                    } else {
                        $currentChampionshipDay = $em->getRepository('DwfPronosticsBundle:GameType')->getByEventAndPosition($event, $lastGamePlayed->getType()->getPosition() + 1);
                        $currentChampionshipDay = '';
                        if ($currentChampionshipDay) {
                            $currentChampionshipDay = $currentChampionshipDay[0];
                        }
                    }
                }
            }
            $pronostic = $em->getRepository('DwfPronosticsBundle:BestScorerPronostic')->findOneByUserAndEvent($this->getUser(), $event);
            if ($pronostic) {
                $bestscorer_pronostic = $pronostic[0];
            }

            /** @var ScoreManager $scoreManager */
            $scoreManager = $this->get('dwf_pronosticbundle.score_manager');
            $scores = $scoreManager->buildScoresForContestAndUser($contest, $this->getUser());

            $standings = $em->getRepository('DwfPronosticsBundle:Standing')->getByContest($contest);
            if ($standings) {
                $position = 1;
                foreach ($standings as $standing) {
                    if ($standing[0]->getUser()->getId() == $user->getId()) {
                        $points = $standings[0]['total'];
                        break;
                    }
                    $position++;
                }
            } else {
                $position = 0;
            }

            /** @var HighchartManager $chartManager */
            $chartManager = $this->get('dwf_pronosticbundle.highchartmanager');
            $betsChart = $chartManager->buildBetsChartWithScores('piechart', $translator->trans('Bets distribution'), $scores);
            $betPointsChart = $chartManager->buildBetPointsChartWithScores('piechart2', $translator->trans('Bet points distribution'), $scores);

            $entities = $em->getRepository('DwfPronosticsBundle:Pronostic')->findByUserAndContest($this->getUser(), $contest, 0);
            $forms = "";
            if ($event->getSimpleBet()) {
                $forms = array();
                $i = 0;
                foreach ($entities as $entity) {
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

            $contestMessage = $em->getRepository('DwfPronosticsBundle:ContestMessage')->findByContest($contest);
            $messageForContest = null;
            if ($contestMessage) {
                $messageForContest = $contestMessage[0];
            }
            $adminMessage = $em->getRepository('DwfPronosticsBundle:AdminMessage')->findLast();

            $messageChatContestType = new ChatMessageFormType("Dwf\PronosticsBundle\Entity\ChatMessage");
            $chatMessage = new ChatMessage();
            $chatMessage->setUser($this->getUser());
            $chatMessage->setContest($contest);
            $chatMessage->setEvent($contest->getEvent());
            $formMessage = $this->createForm($messageChatContestType, $chatMessage, array(
                'action' => $this->generateUrl('contest_send_message', array('contestId' => $contest->getId())),
                'method' => 'PUT',
            ));
            $formMessage->add('submit', 'submit', array(
                'label'        => $this->get('translator')->trans('Send'),
                'button_class' => 'btn btn-warning btn-sm',
            ));

            $countMessages = $chatMessageRepository->getCountMessagesForContest($contest);
            $offset        = max($countMessages - 20, 0);
            $chatMessages  = $chatMessageRepository->getLastMessagesByContest($contest, $offset, $countMessages);

            return array(
                    'contest' => $contest,
                    'event' => $event,
                    'currentChampionshipDay' => $currentChampionshipDay,
                    'user' => $this->getUser(),
                    'entities' => $entities,
                    'bestscorer_pronostic' => $pronostic ? $bestscorer_pronostic : '',
                    'scores' => $scores,
                    'forms' => $forms,
                    'messageForContest' => $messageForContest,
                    'adminMessage' => $adminMessage,
                    'formMessage' => $formMessage->createView(),
                    'chatMessages' => $chatMessages,
                    'pusher_auth_key' => $this->container->getParameter('pusher_auth_key'),
                    'betsChart' => $scores['totalScores'] > 0 ? $betsChart : null,
                    'betPointsChart' => $scores['totalScores'] ? $betPointsChart : null,
                    'position' => $position,
            );
        }
        else throw $this->createNotFoundException('Unable to find Event entity.');
    }

    /**
     * Register form filled with invitation and email
     *
     * @Route("/register/{invitation}/{email}", name="dwf_pronosticsbundle_invitation_confirm")
     * @Method("GET")
     * @Template("FOSUserBundle:Registration:register.html.twig")
     */
    public function invitationConfirmAction($invitation, $email)
    {
        return array(
            'invitation' => $invitation,
            'email'     => $email,
        );
    }

    /**
     * Show games of the day for an event
     *
     * @Route("/contests/{contestId}/gameday", name="contest_gameday")
     * @Method({"GET","POST", "PUT"})
     * @Template()
     */
    public function gameDayAction($contestId)
    {
        $em      = $this->getDoctrine()->getManager();
        $request = $this->getRequest();

        $chatMessageRepository = $em->getRepository('DwfPronosticsBundle:ChatMessage');
        $contest               = $em->getRepository('DwfPronosticsBundle:Contest')->find($contestId);
        $event                 = $contest->getEvent();

        $currentChampionshipDay = '';
        if ($event->getChampionship()) {
            $championshipManager = $this->get('dwf_pronosticbundle.championshipmanager');
            $championshipManager->setEvent($event);
            $currentChampionshipDay = $championshipManager->getCurrentChampionshipDay();
        }

        $games = $em->getRepository('DwfPronosticsBundle:Game')->findAllByEventAndDate($event, date("Y/m/d"));
        if ($event->getSimpleBet()) {
            $forms_games = array();
            $pronostics  = array();
            $i = 0;
            foreach ($games as $entity) {
                $pronostic = $em->getRepository('DwfPronosticsBundle:Pronostic')->findOneBy(array('user' => $this->getUser(), 'game' => $entity, 'contest' => $contest));
                if ($pronostic) {
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
                } else {
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
                array_push($pronostics, $pronostic);
                $i++;
            }
        } else {
            $forms_games = "";
            $pronostics = array();
            foreach ($games as $entity) {
                $pronostic = $em->getRepository('DwfPronosticsBundle:Pronostic')->findOneBy(array('user' => $this->getUser(), 'game' => $entity, 'contest' => $contest));
                array_push($pronostics, $pronostic);
            }

        }

        $teams = $em->getRepository('DwfPronosticsBundle:Team')->findAll();
        foreach ($teams as $team) {
            $arrayTeams[$team->getId()] = $team;
        }
        $contestMessage = $em->getRepository('DwfPronosticsBundle:ContestMessage')->findByContest($contest);
        $messageForContest = null;
        if ($contestMessage) {
            $messageForContest = $contestMessage[0];
        }
        $adminMessage = $em->getRepository('DwfPronosticsBundle:AdminMessage')->findLast();

        $messageChatContestType = new ChatMessageFormType("Dwf\PronosticsBundle\Entity\ChatMessage");
        $chatMessage = new ChatMessage();
        $chatMessage->setUser($this->getUser());
        $chatMessage->setContest($contest);
        $chatMessage->setEvent($contest->getEvent());
        $formMessage = $this->createForm($messageChatContestType, $chatMessage, array(
            'action' => $this->generateUrl('contest_send_message', array('contestId' => $contest->getId())),
            'method' => 'PUT',
        ));
        $formMessage->add('submit', 'submit', array(
            'label'        => $this->get('translator')->trans('Send'),
            'button_class' => 'btn btn-warning btn-sm',
        ));

        $countMessages = $chatMessageRepository->getCountMessagesForContest($contest);
        $offset        = max($countMessages - 20, 0);
        $chatMessages  = $chatMessageRepository->getLastMessagesByContest($contest, $offset, $countMessages);

        return array(
                'user'                      => $this->getUser(),
                'event'                     => $event,
                'currentChampionshipDay'    => $currentChampionshipDay,
                'games'                     => $games,
                'teams'                     => $arrayTeams,
                'forms_games'               => $forms_games,
                'pronostics_games'          => $pronostics,
                'adminMessage'              => $adminMessage,
                'contest'                   => $contest,
                'messageForContest'         => $messageForContest,
                'formMessage'               => $formMessage->createView(),
                'chatMessages'              => $chatMessages,
                'pusher_auth_key'           => $this->container->getParameter('pusher_auth_key'),
        );
    }

    /**
     * Show next games for an event
     *
     * @Route("/contests/{contestId}/nextgames", name="contest_nextgames")
     * @Method({"GET","POST", "PUT"})
     * @Template()
     */
    public function nextGamesAction($contestId)
    {
        $em      = $this->getDoctrine()->getManager();
        $request = $this->getRequest();

        $chatMessageRepository = $em->getRepository('DwfPronosticsBundle:ChatMessage');
        $contest               = $em->getRepository('DwfPronosticsBundle:Contest')->find($contestId);
        $event                 = $contest->getEvent();

        $currentChampionshipDay = '';
        if ($event->getChampionship()) {
            $championshipManager = $this->get('dwf_pronosticbundle.championshipmanager');
            $championshipManager->setEvent($event);
            $currentChampionshipDay = $championshipManager->getCurrentChampionshipDay();
        }

        $nextGames = $em->getRepository('DwfPronosticsBundle:Game')->findNextGames($event);
        if ($event->getSimpleBet()) {
            $forms_nextgames      = array();
            $pronostics_nextgames = array();
            $i = 0;
            foreach ($nextGames as $entity) {
                $pronostic = $em->getRepository('DwfPronosticsBundle:Pronostic')->findOneBy(array('user' => $this->getUser(), 'game' => $entity, 'contest' => $contest));
                if ($pronostic) {
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
                } else {
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
        } else {
            $forms_nextgames      = "";
            $pronostics_nextgames = array();
            $pronostics           = array();
            foreach ($nextGames as $entity) {
                $pronostic = $em->getRepository('DwfPronosticsBundle:Pronostic')->findOneBy(array('user' => $this->getUser(), 'game' => $entity, 'contest' => $contest));
                array_push($pronostics_nextgames, $pronostic);
            }
        }

        $teams = $em->getRepository('DwfPronosticsBundle:Team')->findAll();
        foreach ($teams as $team) {
            $arrayTeams[$team->getId()] = $team;
        }
        $contestMessage = $em->getRepository('DwfPronosticsBundle:ContestMessage')->findByContest($contest);
        $messageForContest = null;
        if ($contestMessage) {
            $messageForContest = $contestMessage[0];
        }
        $adminMessage = $em->getRepository('DwfPronosticsBundle:AdminMessage')->findLast();

        $messageChatContestType = new ChatMessageFormType("Dwf\PronosticsBundle\Entity\ChatMessage");
        $chatMessage = new ChatMessage();
        $chatMessage->setUser($this->getUser());
        $chatMessage->setContest($contest);
        $chatMessage->setEvent($contest->getEvent());
        $formMessage = $this->createForm($messageChatContestType, $chatMessage, array(
            'action' => $this->generateUrl('contest_send_message', array('contestId' => $contest->getId())),
            'method' => 'PUT',
        ));
        $formMessage->add('submit', 'submit', array(
            'label'        => $this->get('translator')->trans('Send'),
            'button_class' => 'btn btn-warning btn-sm',
        ));

        $countMessages = $chatMessageRepository->getCountMessagesForContest($contest);
        $offset        = max($countMessages - 20, 0);
        $chatMessages  = $chatMessageRepository->getLastMessagesByContest($contest, $offset, $countMessages);

        return array(
                'user'                      => $this->getUser(),
                'event'                     => $event,
                'currentChampionshipDay'    => $currentChampionshipDay,
                'nextGames'                 => $nextGames,
                'teams'                     => $arrayTeams,
                'forms_nextgames'           => $forms_nextgames,
                'pronostics_nextgames'      => $pronostics_nextgames,
                'contest'                   => $contest,
                'adminMessage'              => $adminMessage,
                'messageForContest'         => $messageForContest,
                'formMessage'               => $formMessage->createView(),
                'chatMessages'              => $chatMessages,
                'pusher_auth_key'           => $this->container->getParameter('pusher_auth_key'),
        );
    }

    /**
     * send a Message for the contest
     *
     * @Route("/contest/{contestId}/send_message", name="contest_send_message")
     * @Method({"POST", "PUT"})
     * @Template()
     */
    public function publishMessageAction(Request $request, $contestId)
    {
        $user = $this->getUser();
        $em   = $this->getDoctrine()->getManager();

        /** @var ContestRepository $contestRepository */
        $contestRepository     = $em->getRepository('DwfPronosticsBundle:Contest');
        /** @var ChatMessageRepository $chatMessageRepository */
        $chatMessageRepository = $em->getRepository('DwfPronosticsBundle:ChatMessage');

        $contest = $contestRepository->find($contestId);
        if (!$user->hasGroup($contest)) {
            return new Response('Not allowed', 401);
        }

        $lastChatMessage = $chatMessageRepository->getLastMessageByContest($contest);

        $messageChatContestType = new ChatMessageFormType("Dwf\PronosticsBundle\Entity\ChatMessage");
        $chatMessage = new ChatMessage();
        $chatMessage->setUser($user);
        $chatMessage->setContest($contest);
        $chatMessage->setEvent($contest->getEvent());
        $formMessage = $this->createForm($messageChatContestType, $chatMessage, array(
            'action' => '',
            'method' => 'PUT',
        ));
        $formMessage->add('submit', 'submit', array('label' => $this->get('translator')->trans('Post')));
        $formMessage->handleRequest($request);
        if ($formMessage->isValid()) {
            $message = $formMessage->getData()->getMessage();

            $options = array(
                'cluster'   => 'eu',
                'encrypted' => true
            );
            $pusher = new Pusher(
                $this->container->getParameter('pusher_auth_key'),
                $this->container->getParameter('pusher_secret'),
                $this->container->getParameter('pusher_app_id'),
                $options
            );

            $chatMessage->setMessage($message);

            $em->persist($chatMessage);
            $em->flush();

            $sameUser = false;
            if ($lastChatMessage) {
                if ($lastChatMessage->getUser()->getUsername() == $user->getUsername()) {
                    $sameUser = true;
                }
            }

            $html = $this->renderView("DwfPronosticsBundle:Chat:message.html.twig", array(
                'message'  => $chatMessage,
                'lastUser' => $sameUser ? $user->getUsername() : '',
                'index'    => 0,
                'last'     => true,
            ));
            if ($sameUser) {
                $html = $this->renderView("DwfPronosticsBundle:Chat:message-content.html.twig", array(
                    'message'  => $chatMessage,
                ));
            }

            $data['message']    = $message;
            $data['message_id'] = $chatMessage->getId();
            $data['user']       = $user->getUsername();
            $data['date']       = date('H:i');
            $data['html']       = $html;
            $data['same_user']  = (bool) $sameUser;

            $response = $pusher->trigger($contest->getSlugName(), 'new-message', $data);

            return new JsonResponse(
                [
                    'response'   => (bool)$response,
                    'user'       => $user->getUsername(),
                    'message'    => $message,
                    'message_id' => $chatMessage->getId(),
                    'date'       => date('Y-m-d H:i:s'),
                    'html'       => $html,
                    'same_user'  => (bool) $sameUser,
                    'last_user'  => $lastChatMessage->getUser()->getUsername(),
                ]
            );
        }
    }

    /**
     * Show user's valid bets for a contest
     *
     * @Route("/contest/{contestId}/user/{userId}", name="contest_user_show")
     * @Method({"GET","POST", "PUT"})
     * @Template("DwfPronosticsBundle:Contest:user-show.html.twig")
     */
    public function showUserAction($contestId, $userId)
    {
        $em = $this->getDoctrine()->getManager();
        $translator = $this->get('translator');

        $userRepository = $em->getRepository('DwfPronosticsBundle:User');
        $contestRepository = $em->getRepository('DwfPronosticsBundle:Contest');
        $chatMessageRepository = $em->getRepository('DwfPronosticsBundle:ChatMessage');

        $contest = $contestRepository->find($contestId);

        if (!$contest) {
            return new RedirectResponse($this->generateUrl('home'));
        }

        $event = $contest->getEvent();
        if (!$event) {
            $this->addFlash(
                'danger',
                $this->get('translator')->trans('Event not found')
            );
            return new RedirectResponse($this->generateUrl('home'));
        }
        $user = $userRepository->find($userId);
        if (!$user) {
            $this->addFlash(
                'danger',
                $this->get('translator')->trans('User not found')
            );
            return new RedirectResponse($this->generateUrl('home'));
        }
        if (!$user->hasGroup($contest->getName())) {
            $this->addFlash(
                'danger',
                $this->get('translator')->trans('User doesn\'t belong to this contest')
            );
            return new RedirectResponse($this->generateUrl('home'));
        }

        $currentChampionshipDay = '';
        if ($event->getChampionship()) {
            $lastGamePlayed = $em->getRepository('DwfPronosticsBundle:Game')->findLastGamePlayedByEvent($event);
            $currentChampionshipDay = '';
            if (count($lastGamePlayed) > 0) {
                $lastGamePlayed = $lastGamePlayed[0];
                $gamesLeftInChampionshipDay = $em->getRepository('DwfPronosticsBundle:Game')->findGamesLeftByEventAndGameType($event, $lastGamePlayed->getType());
                if ($gamesLeftInChampionshipDay) {
                    $currentChampionshipDay = $em->getRepository('DwfPronosticsBundle:GameType')->find($lastGamePlayed->getType());
                } else {
                    $currentChampionshipDay = $em->getRepository('DwfPronosticsBundle:GameType')->getByEventAndPosition($event, $lastGamePlayed->getType()->getPosition() + 1);
                    $currentChampionshipDay = '';
                    if ($currentChampionshipDay) {
                        $currentChampionshipDay = $currentChampionshipDay[0];
                    }
                }
            }
        }
        $pronostic = $em->getRepository('DwfPronosticsBundle:BestScorerPronostic')->findOneByUserAndEvent($this->getUser(), $event);
        if ($pronostic) {
            $bestscorer_pronostic = $pronostic[0];
        }

        /** @var ScoreManager $scoreManager */
        $scoreManager = $this->get('dwf_pronosticbundle.score_manager');
        $scores = $scoreManager->buildScoresForContestAndUser($contest, $user);

        /** @var HighchartManager $chartManager */
        $chartManager = $this->get('dwf_pronosticbundle.highchartmanager');
        $betsChart = $chartManager->buildBetsChartWithScores('piechart', $translator->trans('Bets distribution'), $scores);
        $betPointsChart = $chartManager->buildBetPointsChartWithScores('piechart2', $translator->trans('Bet points distribution'), $scores);

        $entities = $em->getRepository('DwfPronosticsBundle:Pronostic')->findByUserAndContest($user, $contest, 1);

        $contestMessage = $em->getRepository('DwfPronosticsBundle:ContestMessage')->findByContest($contest);
        $messageForContest = null;
        if ($contestMessage) {
            $messageForContest = $contestMessage[0];
        }
        $adminMessage = $em->getRepository('DwfPronosticsBundle:AdminMessage')->findLast();

        $messageChatContestType = new ChatMessageFormType("Dwf\PronosticsBundle\Entity\ChatMessage");
        $chatMessage = new ChatMessage();
        $chatMessage->setUser($this->getUser());
        $chatMessage->setContest($contest);
        $chatMessage->setEvent($contest->getEvent());
        $formMessage = $this->createForm($messageChatContestType, $chatMessage, array(
            'action' => $this->generateUrl('contest_send_message', array('contestId' => $contest->getId())),
            'method' => 'PUT',
        ));
        $formMessage->add('submit', 'submit', array(
            'label'        => $this->get('translator')->trans('Send'),
            'button_class' => 'btn btn-warning btn-sm',
        ));

        $countMessages = $chatMessageRepository->getCountMessagesForContest($contest);
        $offset = max($countMessages - 20, 0);
        $chatMessages = $chatMessageRepository->getLastMessagesByContest($contest, $offset, $countMessages);

        return array(
            'contest' => $contest,
            'event' => $event,
            'currentChampionshipDay' => $currentChampionshipDay,
            'user' => $this->getUser(),
            'userDisplayed' => $user,
            'entities' => $entities,
            'bestscorer_pronostic' => $pronostic ? $bestscorer_pronostic : '',
            'scores' => $scores,
            'messageForContest' => $messageForContest,
            'adminMessage' => $adminMessage,
            'formMessage'=> $formMessage->createView(),
            'chatMessages' => $chatMessages,
            'pusher_auth_key' => $this->container->getParameter('pusher_auth_key'),
            'betsChart' => $scores['totalScores'] > 0 ? $betsChart : null,
            'betPointsChart' => $scores['totalScores'] ? $betPointsChart : null,
        );
    }
}
