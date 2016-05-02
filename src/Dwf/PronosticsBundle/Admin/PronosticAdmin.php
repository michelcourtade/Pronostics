<?php
namespace Dwf\PronosticsBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class PronosticAdmin extends Admin
{
    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {	
        $formMapper
        ->add('event')
        ->add('user')
        ->add('contest')
        ->add('game')
        ->add('simpleBet', 'choice',array('expanded' => true,'choices' => array('1' => '1', 'N' => 'N', '2' => '2'), 'required' => false))
        ->add('sliceScore')
        ->add('scoreTeam1')
        ->add('scoreTeam2')
        ->add('overtime')
        ->add('scoreTeam1Overtime')
        ->add('scoreTeam2Overtime')
        ->add('winner')
        ->add('result')
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
        ->add('event')
        	->add('user')
        	->add('game')
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {    	
        $listMapper
        //->addIdentifier('flag', 'string', array('label' => 'Drapeau', 'template' => 'DwfPronosticsBundle:Admin:list_flag.html.twig'))
        ->addIdentifier('id')
        	->add('event')
        	->addIdentifier('user')
        	->add('game')
        	->add('simpleBet')
        	->add('scoreTeam1')
        	->add('scoreTeam2')
        	->add('result')
        //->add('file', 'file',$fileFieldOptions)
        ;
    }
}