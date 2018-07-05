<?php

namespace Dwf\PronosticsBundle\Controller;

use Dwf\PronosticsBundle\Entity\ChatMessage;
use Dwf\PronosticsBundle\Form\Type\ChatMessageFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Dwf\PronosticsBundle\Entity\Pronostic;
use Dwf\PronosticsBundle\Form\PronosticType;
use Dwf\PronosticsBundle\Form\PronosticGameType;
use Dwf\PronosticsBundle\Form\SimplePronosticType;

/**
 * Pronostic controller.
 *
 * @Route("/pronostics")
 */
class PronosticController extends Controller
{

    /**
     * Lists all Pronostic entities.
     *
     * @Route("/", name="pronostics")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $pronostic = $em->getRepository('DwfPronosticsBundle:BestScorerPronostic')->findOneByUserAndEvent($this->getUser(), $event);
        if($pronostic)
            $bestscorer_pronostic = $pronostic[0];

        $entities = $em->getRepository('DwfPronosticsBundle:Pronostic')->findAllByUser($this->getUser());
        $nb = $em->getRepository('DwfPronosticsBundle:Pronostic')->getNbByUserAndEvent($this->getUser(), $event);
        $total = $em->getRepository('DwfPronosticsBundle:Pronostic')->getResultsByEventAndUser($event, $this->getUser());

        return array(
            'entities' => $entities,
            'bestscorer_pronostic' => $pronostic ? $bestscorer_pronostic : '',
        	'nbPronostics' => $nb,
        	'total'         => $total,
        );
    }
    /**
     * Creates a new Pronostic entity.
     *
     * @Route("/", name="pronostics_create")
     * @Method("POST")
     * @Template("DwfPronosticsBundle:Pronostic:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Pronostic();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $pronosticRepository = $em->getRepository('DwfPronosticsBundle:Pronostic');
            $pronostic = $pronosticRepository->findOneBy(
                [
                    'contest' => $entity->getContest(),
                    'user'    => $entity->getUser(),
                    'game'    => $entity->getGame(),
                ]
            );
            if ($pronostic) {
                $this->addFlash(
                    'info',
                    $this->get('translator')->trans('You already have a bet registered for this game. You can modify it below.')
                );

                return $this->redirect(
                    $this->generateUrl('pronostics_edit', array('id' => $pronostic->getId(), 'contestId' => $pronostic->getContest()->getId()))
                );
            }
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('contest_game_show', array('id' => $entity->getGame()->getId(), 'contestId' => $entity->getContest()->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a Pronostic entity.
    *
    * @param Pronostic $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Pronostic $entity)
    {
        $form = $this->createForm(new PronosticGameType(), $entity, array(
            'action' => $this->generateUrl('pronostics_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => $this->get('translator')->trans('Create')));

        return $form;
    }

    /**
     * Displays a form to create a new Pronostic entity.
     *
     * @Route("/new", name="pronostics_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Pronostic();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new Pronostic entity.
     *
     * @Route("/contest/{contestId}/new/{id}", name="pronostics_new_forgame")
     * @Method("GET")
     * @Template()
     */
    public function newForGameAction($id, $contestId)
    {
        $em = $this->getDoctrine()->getManager();

        $chatMessageRepository = $em->getRepository('DwfPronosticsBundle:ChatMessage');
        $contest               = $em->getRepository('DwfPronosticsBundle:Contest')->find($contestId);
        $game                  = $em->getRepository('DwfPronosticsBundle:Game')->find($id);
        $gameType              = $em->getRepository('DwfPronosticsBundle:GameType')->find($game->getType());
        $event                 = $game->getEvent();
        if ($game->hasBegan() || $game->getPlayed()) {
            $this->addFlash(
                'info',
                $this->get('translator')->trans('Game has already started, you can\'t bet anymore.')
            );

            return $this->redirect(
                $this->generateUrl('contest_game_show', array('id' => $game->getId(), 'contestId' => $contestId))
            );
        }

        $pronostic = $em->getRepository('DwfPronosticsBundle:Pronostic')->findOneBy(array('user' => $this->getUser(), 'game' => $game, 'contest' => $contest));
        if ($pronostic) {
            $this->addFlash(
                'info',
                $this->get('translator')->trans('You already have a bet registered for this game. You can modify it below.')
            );

            return $this->redirect(
                $this->generateUrl('pronostics_edit', array('id' => $pronostic->getId(), 'contestId' => $pronostic->getContest()->getId()))
            );
        }

        $entity = new Pronostic();
        $entity->setGame($game);
        $entity->setUser($this->getUser());
        $entity->setEvent($event);
        $entity->setContest($contest);

        $form = $this->createForm(new PronosticGameType(), $pronostic ? $pronostic:$entity, array(
                'action' => $this->generateUrl('pronostics_create'),
                'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => $this->get('translator')->trans('Bet')));

        $messageForContest = null;
        $contestMessage    = $em->getRepository('DwfPronosticsBundle:ContestMessage')->findByContest($contest);
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
                'event'                         => $game->getEvent(),
                'entity'                        => $entity,
                'form'                          => $form->createView(),
                'user'                          => $this->getUser(),
                'gameType'                      => $gameType,
                'messageForContest'             => $messageForContest,
                'adminMessage'                  => $adminMessage,
                'formMessage'                   => $formMessage->createView(),
                'chatMessages'                  => $chatMessages,
                'pusher_auth_key'               => $this->container->getParameter('pusher_auth_key'),
        );
    }

    /**
     * Finds and displays a Pronostic entity.
     *
     * @Route("/{id}", name="pronostics_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DwfPronosticsBundle:Pronostic')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Pronostic entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays Pronostics for a specific user.
     *
     * @Route("/user/{user_id}/event/{event}", name="pronostics_show_user")
     * @Method("GET")
     * @Template("DwfPronosticsBundle:Pronostic:showForUser.html.twig")
     */
    public function showForUserAndEventAction($user_id, $event)
    {
    	$em = $this->getDoctrine()->getManager();

    	$user = $em->getRepository('DwfPronosticsBundle:User')->find($user_id);
        //$user = $user[0];
    	if (!$user) {
    		throw $this->createNotFoundException('Unable to find User entity.');
    	}

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
    	    $pronostic = $em->getRepository('DwfPronosticsBundle:BestScorerPronostic')->findOneByUserAndEvent($user, $event);
    	    if($pronostic)
    	        $bestscorer_pronostic = $pronostic[0];
            $entities = $em->getRepository('DwfPronosticsBundle:Pronostic')->findByUserAndEvent($user, $event);
            $nb = $em->getRepository('DwfPronosticsBundle:Pronostic')->getNbByUserAndEvent($user, $event);
            $nbPerfectScore = $em->getRepository('DwfPronosticsBundle:Pronostic')->getNbScoreByUserAndEventAndResult($user, $event, 5);
            $nbGoodScore = $em->getRepository('DwfPronosticsBundle:Pronostic')->getNbScoreByUserAndEventAndResult($user, $event, 3);
            $nbBadScore = $em->getRepository('DwfPronosticsBundle:Pronostic')->getNbScoreByUserAndEventAndResult($user, $event, 1);
            $total = $em->getRepository('DwfPronosticsBundle:Pronostic')->getResultsByEventAndUser($event, $user);
    //     	$deleteForm = $this->createDeleteForm($id);

            $adminMessage = $em->getRepository('DwfPronosticsBundle:AdminMessage')->findLast();
                return array(
                    'user'                      => $user,
                    'bestscorer_pronostic'      => $pronostic ? $bestscorer_pronostic : '',
                    'entities'	                => $entities,
                    'event'                     => $event,
                    'currentChampionshipDay'    => $currentChampionshipDay,
                    'nbPronostics'              => $nb,
                    'nbPerfectScore'            => $nbPerfectScore,
                    'nbGoodScore'               => $nbGoodScore,
                    'nbBadScore'                => $nbBadScore,
                    'total'                     => $total,
                    'contest'                   => '',
                    'adminMessage'              => $adminMessage,
        	);
    	}
    	else throw $this->createNotFoundException('Unable to find Event entity.');
    }

    /**
     * Displays Pronostics for a specific event.
     *
     * @Route("/event/{event}", name="pronostics_event")
     * @Method({"GET","POST", "PUT"})
     * @Template("DwfPronosticsBundle:Pronostic:index.html.twig")
     */
    public function showForEventAction($event)
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

    /**
     * Displays Pronostics for a specific game.
     *
     * @Route("/game/{gameId}", name="pronostics_show_game")
     * @Method("GET")
     * @Template()
     */
    public function showForGameAction($gameId)
    {
        $em = $this->getDoctrine()->getManager();

        $game = $em->getRepository('DwfPronosticsBundle:Game')->find($gameId);

        if (!$game) {
            throw $this->createNotFoundException('Unable to find Game entity.');
        }

        $entities = $em->getRepository('DwfPronosticsBundle:Pronostic')->findAllByGame($game);

        //     	$deleteForm = $this->createDeleteForm($id);

        return array(
                'user'      => $this->getUser(),
                'game'      => $game,
                'event'     => $game->getEvent(),
                'entities'	=> $entities,
                //     			'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Displays a form to edit an existing Pronostic entity.
     *
     * @Route("/contest/{contestId}/{id}/edit", name="pronostics_edit")
     * @Method("GET")
     * @Template("DwfPronosticsBundle:Pronostic:newForGame.html.twig")
     */
    public function editAction($id, $contestId)
    {
        $em = $this->getDoctrine()->getManager();

        $chatMessageRepository = $em->getRepository('DwfPronosticsBundle:ChatMessage');
        $contest               = $em->getRepository('DwfPronosticsBundle:Contest')->find($contestId);
        $entity                = $em->getRepository('DwfPronosticsBundle:Pronostic')->find($id);
        $game                  = $em->getRepository('DwfPronosticsBundle:Game')->find($entity->getGame());
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Pronostic entity.');
        }
        $contestMessage = $em->getRepository('DwfPronosticsBundle:ContestMessage')->findByContest($contest);
        $messageForContest = null;
        if ($contestMessage) {
            $messageForContest = $contestMessage[0];
        }
        $adminMessage = $em->getRepository('DwfPronosticsBundle:AdminMessage')->findLast();
        if (!$game->hasBegan() && !$game->getPlayed()) {
            $editForm = $this->createEditForm($entity);
            $deleteForm = $this->createDeleteForm($id);

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
                'event'                         => $game->getEvent(),
                'entity'                        => $entity,
                'form'                          => $editForm->createView(),
                'user'                          => $this->getUser(),
                'messageForContest'             => $messageForContest,
                'adminMessage'                  => $adminMessage,
                'formMessage'                   => $formMessage->createView(),
                'chatMessages'                  => $chatMessages,
                'pusher_auth_key'               => $this->container->getParameter('pusher_auth_key'),
            );
        }
        else throw $this->createNotFoundException('Pronostic impossible à modifier car match déjà commencé !');
    }

    /**
    * Creates a form to edit a Pronostic entity.
    *
    * @param Pronostic $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Pronostic $entity)
    {
        $form = $this->createForm(new PronosticGameType(), $entity, array(
            'action' => $this->generateUrl('pronostics_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => $this->get('translator')->trans('Modify')));

        return $form;
    }
    /**
     * Edits an existing Pronostic entity.
     *
     * @Route("/{id}", name="pronostics_update")
     * @Method("PUT")
     * @Template("DwfPronosticsBundle:Pronostic:newForGame.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('DwfPronosticsBundle:Pronostic')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Pronostic entity.');
        }
        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            $em->flush();
            $this->addFlash(
                    'success',
                    $this->get('translator')->trans('Your bet has been modified.')
            );

            return $this->redirect($this->generateUrl('pronostics_edit', array('id' => $id, 'contestId' => $entity->getContest()->getId())));
        }
        return array(
                'entity' => $entity,
                'edit_form' => $editForm->createView(),
                'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Pronostic entity.
     *
     * @Route("/{id}", name="pronostics_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('DwfPronosticsBundle:Pronostic')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Pronostic entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('pronostics'));
    }

    /**
     * Creates a form to delete a Pronostic entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('pronostics_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Supprimer'))
            ->getForm()
        ;
    }
}
