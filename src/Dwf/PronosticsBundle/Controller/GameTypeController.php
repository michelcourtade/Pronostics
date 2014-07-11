<?php

namespace Dwf\PronosticsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Dwf\PronosticsBundle\Entity\GameType;
use Dwf\PronosticsBundle\Form\GameTypeType;

/**
 * GameType controller.
 *
 * @Route("/type")
 */
class GameTypeController extends Controller
{

    /**
     * Lists all GameType entities.
     *
     * @Route("/", name="type")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('DwfPronosticsBundle:GameType')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new GameType entity.
     *
     * @Route("/", name="type_create")
     * @Method("POST")
     * @Template("DwfPronosticsBundle:GameType:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new GameType();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('type_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a GameType entity.
    *
    * @param GameType $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(GameType $entity)
    {
        $form = $this->createForm(new GameTypeType(), $entity, array(
            'action' => $this->generateUrl('type_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new GameType entity.
     *
     * @Route("/new", name="type_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new GameType();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a GameType entity.
     *
     * @Route("/{id}", name="type_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DwfPronosticsBundle:GameType')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find GameType entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing GameType entity.
     *
     * @Route("/{id}/edit", name="type_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DwfPronosticsBundle:GameType')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find GameType entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a GameType entity.
    *
    * @param GameType $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(GameType $entity)
    {
        $form = $this->createForm(new GameTypeType(), $entity, array(
            'action' => $this->generateUrl('type_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing GameType entity.
     *
     * @Route("/{id}", name="type_update")
     * @Method("PUT")
     * @Template("DwfPronosticsBundle:GameType:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DwfPronosticsBundle:GameType')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find GameType entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('type_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a GameType entity.
     *
     * @Route("/{id}", name="type_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('DwfPronosticsBundle:GameType')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find GameType entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('type'));
    }

    /**
     * Creates a form to delete a GameType entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('type_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
