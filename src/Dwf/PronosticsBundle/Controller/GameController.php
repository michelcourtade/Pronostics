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
            $championshipManager = $this->get('dwf_pronosticbundle.championshipmanager');
            $championshipManager->setEvent($event);
            $currentChampionshipDay = $championshipManager->getCurrentChampionshipDay();
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
            'form'                  => $entity->getEvent()->getSimpleBet() ? $form->createView():'',
            'contest'               => '',
        );
    }

}
