<?php
namespace Dwf\PronosticsBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class ScorerAdmin extends Admin
{
    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
    	$scorer = $this->getSubject();
        $formMapper
//         ->add('event')
//         ->add('game')
        ->add('player')
        ->add('score')
        ->add('owngoal')
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
        ->add('event')
        ->add('game')
        ->add('player')
        ->add('owngoal')
                ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {    	
        $listMapper
        ->addIdentifier('id')
        ->add('event')
        ->add('game')
        ->add('player')
        ->add('score')
        ->add('owngoal')
                ;
    }
//     public function getParentAssociationMapping()
//     {
//         return 'game';
//     }
}