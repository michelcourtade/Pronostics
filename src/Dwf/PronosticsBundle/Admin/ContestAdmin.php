<?php
namespace Dwf\PronosticsBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class ContestAdmin extends Admin
{
    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {	
        $formMapper
        ->add('event')
        ->add('owner')
        ->add('name')
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
        ->add('event')
        ->add('name')
        ->add('owner')
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {    	
        $listMapper
        //->addIdentifier('flag', 'string', array('label' => 'Drapeau', 'template' => 'DwfPronosticsBundle:Admin:list_flag.html.twig'))
        ->addIdentifier('id')
        ->add('event')
        ->add('owner')
        ->add('name')
        ;
    }
}