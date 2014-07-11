<?php

namespace Dwf\PronosticsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Dwf\PronosticsBundle\Entity\Game;
use Dwf\PronosticsBundle\Form\GameType;

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
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

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
        }
        else {
            $nextGame = "";
            $pronosticNextGame = "";
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

        //$deleteForm = $this->createDeleteForm($id);

        return array(
            'event'                 => $entity->getEvent(),
            'entity'                => $entity,
            'pronostic'             => $pronostic,
            'nextGame'              => $nextGame,
            'pronosticNextGame'     => $pronosticNextGame,
        	'pronostics'            => $pronostics,
            'scorersTeam1'          => $scorersTeam1,
            'scorersTeam2'          => $scorersTeam2,
            //'delete_form' => $deleteForm->createView(),
        );
    }
    
    /**
     * Lists all Game entities.
     *
     * @Route("/event/{event}", name="games_event")
     * @Method("GET")
     * @Template("DwfPronosticsBundle:Game:index.html.twig")
     */
    public function showByEventAction($event)
    {
    	$em = $this->getDoctrine()->getManager();
    
    	$event = $em->getRepository('DwfPronosticsBundle:Event')->find($event);
    	if($event) {
	    	$entities = $em->getRepository('DwfPronosticsBundle:Game')->findAllByEventOrderedByDate($event);
	    	$types = $em->getRepository('DwfPronosticsBundle:GameType')->findAllByEvent($event);
	    	return array(
	    			'event'		=> $event,
	    			'user' 		=> $this->getUser(),
	    			'entities' 	=> $entities,
	    			'types'     => $types,
	    	);
    	}
    	else return $this->redirect($this->generateUrl('events'));
    }

    /**
     * Lists all Game entities by GameType
     *
     * @Route("/event/{event}/type/{type}", name="game_by_type")
     * @Method("GET")
     * @Template("DwfPronosticsBundle:Game:showByGameType.html.twig")
     */
    public function showByGameTypeAndEventAction($type, $event)
    {
        $em = $this->getDoctrine()->getManager();
    
        $event = $em->getRepository('DwfPronosticsBundle:Event')->find($event);
        if($event) {
            $results = $em->getRepository('DwfPronosticsBundle:GameTypeResult')->getResultsByGameTypeAndEvent($type, $event);
            $teams = $em->getRepository('DwfPronosticsBundle:Team')->findAll();
            foreach ($teams as $team)
            {
            	$arrayTeams[$team->getId()] = $team;
            }
            $entity = $em->getRepository('DwfPronosticsBundle:GameType')->find($type);
            $entities = $em->getRepository('DwfPronosticsBundle:Game')->findAllByTypeAndEvent($type, $event);
            $types = $em->getRepository('DwfPronosticsBundle:GameType')->findAllByEvent($event);
        
            return array(
            		'teams'	=> $arrayTeams,
                    'results' => $results,
                    'event' => $event,
                    'user' => $this->getUser(),
                    'entity'    => $entity,
                    'entities' => $entities,
	    			'types'     => $types,
            );
        }
        else return $this->redirect($this->generateUrl('events'));
    }
}
