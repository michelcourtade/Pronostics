<?php
namespace Dwf\PronosticsBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class EventAdmin extends Admin
{
    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
    	$event = $this->getSubject();
    	
    	// use $fileFieldOptions so we can add other options to the field
    	$fileFieldOptions = array('required' => false);
    	if ($event && ($webPath = $event->getWebPath())) {
    		// get the container so the full path to the image can be set
    		$container = $this->getConfigurationPool()->getContainer();
    		$fullPath = $container->get('request')->getBasePath().'/'.$webPath;
    	
    		// add a 'help' option containing the preview's img tag
    		$fileFieldOptions['help'] = '<img src="'.$fullPath.'" class="admin-preview" />';
    	}
    	$fileFieldOptions['data_class'] = null;
    	
        $formMapper
        ->add('name', 'text', array('label' => 'Nom'))
        ->add('file', 'file', $fileFieldOptions)
        ->add('startDate')
        ->add('finishDate')
        ->add('active', 'checkbox', array('required' => false))
        ->add('sport')
        ->add('nationalTeams', 'checkbox', array('required' => false))
        ->add('nbPointsForLoss')
        ->add('nbPointsForDraw')
        ->add('nbPointsForWin')
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
        ->add('name')
        ->add('sport')
        ->add('nationalTeams')
        ->add('active')
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {    	
        $listMapper
        ->addIdentifier('name')
        ->add('sport')
        ->add('startDate')
        ->add('finishDate')
        ->add('active')
        //->add('file', 'file',$fileFieldOptions)
        ;
    }
}