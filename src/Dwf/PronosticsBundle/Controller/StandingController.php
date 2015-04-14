<?php

namespace Dwf\PronosticsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Dwf\PronosticsBundle\Entity\Pronostic;
use Dwf\PronosticsBundle\Form\PronosticType;
use Dwf\PronosticsBundle\Form\PronosticGameType;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\NotValidCurrentPageException;

/**
 * Standings controller.
 *
 * @Route("/standings")
 */
class StandingController extends Controller
{

    /**
     * Lists global standings for the app.
     *
     * @Route("/{event}/{page}", name="standings_event", requirements={"page" = "\d+"}, defaults={"page" = "1"})
     * @Method("GET")
     * @Template()
     */
    public function indexAction($event, $page)
    {
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository('DwfPronosticsBundle:Event')->find($event);
        if($event) {
            $championshipManager = $this->get('dwf_pronosticbundle.championshipmanager');
            $championshipManager->setEvent($event);
            $currentChampionshipDay = $championshipManager->getCurrentChampionshipDay();
            $user = $this->getUser();
            $query = $em->getRepository('DwfPronosticsBundle:Standing')->getByEventQuery($event);
            //$entities = $query->getResult();
            $pager = new Pagerfanta(new DoctrineORMAdapter($query));
            $pager->setMaxPerPage(2);
            try {
                $pager->setCurrentPage($page);
            } catch(NotValidCurrentPageException $e) {
                throw new NotFoundHttpException();
            }
            return array(
                'user'      => $user,
                'event'		=> $event,
                'currentChampionshipDay' => $currentChampionshipDay,
                'entities' => $pager->getCurrentPageResults(),
                'pager' => $pager,
            );
        }
        else return $this->redirect($this->generateUrl('events'));
    }

    
    /**
     * Lists groups of a user
     *
     * @Route("/{event}/groups", name="standings_event_groups")
     * @Method("GET")
     * @Template("DwfPronosticsBundle:Standing:index-group.html.twig")
     */
    public function showByEventAction($event)
    {
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository('DwfPronosticsBundle:Event')->find($event);
        if($event) {
            $championshipManager = $this->get('dwf_pronosticbundle.championshipmanager');
            $championshipManager->setEvent($event);
            $currentChampionshipDay = $championshipManager->getCurrentChampionshipDay();

            $groupResults = array();
            $user = $this->getUser();
            $groups = $user->getGroups();
            if(sizeof($groups) == 1) {
                $group = $groups[0];
                return $this->redirect($this->generateUrl('standings_event_group', array('event' => $event->getId(), 'group' => $group->getId())));
            }
            return array(
                    'user'      => $user,
                    'event'		=> $event,
                    'currentChampionshipDay' => $currentChampionshipDay,
                    'groups'	=> $groups,
            );
        }
        else return $this->redirect($this->generateUrl('events'));
    }
    
    /**
     * Lists standings for an event and a group
     *
     * @Route("/{event}/group/{group}", name="standings_event_group")
     * @Method("GET")
     * @Template("DwfPronosticsBundle:Standing:group.html.twig")
     */
    public function showByEventAndGroupAction($event, $group)
    {
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository('DwfPronosticsBundle:Event')->find($event);
        if($event) {
            $championshipManager = $this->get('dwf_pronosticbundle.championshipmanager');
            $championshipManager->setEvent($event);
            $currentChampionshipDay = $championshipManager->getCurrentChampionshipDay();
    
            $groupResults = array();
            $user = $this->getUser();
            $groups = $user->getGroups();
            $userGroup = false;
            if($groups) {
                foreach ($groups as $key => $groupUser) {
                    if($groupUser->getId() == $group) {
                        $entities = $em->getRepository('DwfPronosticsBundle:Standing')->getByEventAndGroup($event, $groupUser);
                        break;
                    }
                }
            }
            return array(
                    'user'      => $user,
                    'event'		=> $event,
                    'currentChampionshipDay' => $currentChampionshipDay,
                    'entities' => $entities,
                    'group'	=> $groupUser,
            );
        }
        else return $this->redirect($this->generateUrl('events'));
    }
}
