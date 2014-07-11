<?php

namespace Dwf\PronosticsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Dwf\PronosticsBundle\Entity\BestScorerPronostic;
use Dwf\PronosticsBundle\Form\BestScorerPronosticType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

//use Dwf\PronosticsBundle\Form\PronosticGameType;

/**
 * Pronostic controller.
 *
 * @Route("/bestscorers")
 */
class BestScorerPronosticController extends Controller
{

    /**
     * Lists all Best scorer Pronostic entities for a specific event.
     *
     * @Route("/{event}", name="bestscorer_pronostics_show")
     * @ParamConverter("event", class="DwfPronosticsBundle:Event", options={"id" = "event"})
     * @Method("GET")
     * @Template()
     */
    public function indexAction(\Dwf\PronosticsBundle\Entity\Event $event)
    {
        $em = $this->getDoctrine()->getManager();

        $pronostic = $em->getRepository('DwfPronosticsBundle:BestScorerPronostic')->findOneByUserAndEvent($this->getUser(), $event);
		if($pronostic)
			$pronostic = $pronostic[0];
        return array(
            'entity' => $pronostic,
            'event' => $event
        );
    }
    /**
     * Creates a new Pronostic entity.
     *
     * @Route("/create", name="bestscorer_pronostics_create")
     * @Method("POST")
     * @Template("DwfPronosticsBundle:BestScorerPronostic:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new BestScorerPronostic();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('pronostics_event', array('event' => $entity->getEvent()->getId())));
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
    private function createCreateForm(BestScorerPronostic $entity)
    {
        $form = $this->createForm(new BestScorerPronosticType(), $entity, array(
            'action' => $this->generateUrl('bestscorer_pronostics_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Pronostiquer'));

        return $form;
    }

    /**
     * Displays a form to create a new Pronostic entity.
     *
     * @Route("/new/{event}", name="bestscorer_pronostics_new")
     * @Method("GET")
     * @ParamConverter("event", class="DwfPronosticsBundle:Event", options={"id" = "event"})
     * @Template()
     */
    public function newAction(\Dwf\PronosticsBundle\Entity\Event $event)
    {
        $pronostic = new BestScorerPronostic();
        $pronostic->setUser($this->getUser());
        $pronostic->setEvent($event);
        $form   = $this->createCreateForm($pronostic);
		$em = $this->getDoctrine()->getManager();
    	$players = $em->getRepository('DwfPronosticsBundle:Player')->findAllOrderedByName();
    	
    	if(!$event->hasBegan()) {
	        return array(
	            'pronostic' => $pronostic,
	            'event' => $event,
	            'form'   => $form->createView(),
	        	'players' => $players,
	        );
        }
        else throw $this->createNotFoundException('Pronostic impossible car la compétition a déjà commencé !');
    }


    /**
     * Displays Pronostics for a specific user.
     *
     * @Route("/user/{name}", name="bestscorer_pronostics_show_user")
     * @Method("GET")
     * @Template()
     */
    public function showForUserAction($name)
    {
    	$em = $this->getDoctrine()->getManager();
    
    	$user = $em->getRepository('DwfPronosticsBundle:User')->findByUsername($name);
        $user = $user[0];
    	if (!$user) {
    		throw $this->createNotFoundException('Unable to find User entity.');
    	}
    
    	$entities = $em->getRepository('DwfPronosticsBundle:BestScorerPronostic')->findAllForUser($user);
    	
//     	$deleteForm = $this->createDeleteForm($id);
    
    	return array(
    			'user'      => $user,
    			'entities'	=> $entities,
//     			'delete_form' => $deleteForm->createView(),
    	);
    }

    /**
     * Displays a form to edit an existing Pronostic entity.
     *
     * @Route("/{id}/edit", name="bestscorer_pronostics_edit")
     * @Method("GET")
     * @Template("DwfPronosticsBundle:BestScorerPronostic:new.html.twig")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DwfPronosticsBundle:BestScorerPronostic')->find($id);
        $event = $em->getRepository('DwfPronosticsBundle:Event')->find($entity->getEvent());
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Pronostic entity.');
        }        
        $players = $em->getRepository('DwfPronosticsBundle:Player')->findAllOrderedByName();
        
		//var_dump($game->getDate());
        if(!$event->hasBegan()) {
	        $editForm = $this->createEditForm($entity);
	        $deleteForm = $this->createDeleteForm($id);
	
	        return array(
	            'entity'      => $entity,
	            'form'   => $editForm->createView(),
	            'event'    => $event,
	        		'players' => $players,
	            //'delete_form' => $deleteForm->createView(),
	        );
        }
        else throw $this->createNotFoundException('Pronostic impossible à modifier car compétition déjà commencée !');
    }

    /**
    * Creates a form to edit a Pronostic entity.
    *
    * @param Pronostic $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(BestScorerPronostic $entity)
    {
        $form = $this->createForm(new BestScorerPronosticType(), $entity, array(
            'action' => $this->generateUrl('bestscorer_pronostics_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Modifier'));

        return $form;
    }
    /**
     * Edits an existing Pronostic entity.
     *
     * @Route("/{id}", name="bestscorer_pronostics_update")
     * @Method("PUT")
     * @Template("DwfPronosticsBundle:Pronostic:newForGame.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DwfPronosticsBundle:BestScorerPronostic')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Pronostic entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();
            return $this->redirect($this->generateUrl('pronostics_event', array('event' => $entity->getEvent()->getId())));
            //return $this->redirect($this->generateUrl('bestscorer_pronostics_show', array('event' => $entity->getEvent()->getId())));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Pronostic entity.
     *
     * @Route("/{id}", name="bestscorer_pronostics_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('DwfPronosticsBundle:BestScorerPronostic')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Pronostic entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('bestscorer_pronostics_show'));
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
            ->setAction($this->generateUrl('bestscorer_pronostics_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Supprimer'))
            ->getForm()
        ;
    }
}
