<?php
namespace Dwf\PronosticsBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class BestScorerPronosticAdmin extends Admin
{
    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {	
        $formMapper
        ->add('user')
        ->add('player')
        ->add('event')
        ->add('goals')
        ->add('result')
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
        	->add('user')
        	->add('player')
        	->add('event')
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {    	
        $listMapper
        	->addIdentifier('id')
        	->addIdentifier('user')
        	->add('player')
        	->add('event')
        	->add('goals')
        	->add('result')
        ;
    }
}