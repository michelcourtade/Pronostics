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
     * @Method("GET")
     * @Template()
     */
    public function homeAction($event)
    {
        $em = $this->getDoctrine()->getManager();
    
        $event = $em->getRepository('DwfPronosticsBundle:Event')->find($event);
    
        $games = $em->getRepository('DwfPronosticsBundle:Game')->findAllByEventAndDate($event, date("Y/m/d"));
        $nextGames = $em->getRepository('DwfPronosticsBundle:Game')->findNextGames($event);
        
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
                //'bestscorer_pronostic' => $pronostic ? $bestscorer_pronostic : '',
                'games' => $games,
                'nextGames' => $nextGames,
                'scorers'   => $scorers,
                'teams'     => $arrayTeams,
                'players'   => $arrayPlayers,
                'bestOffenses' => $bestOffenses,
                'bestDefenses' => $bestDefenses,
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
