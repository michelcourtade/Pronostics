<?php
namespace Dwf\PronosticsBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class TeamAdmin extends Admin
{
    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
    	$team = $this->getSubject();
    	
    	// use $fileFieldOptions so we can add other options to the field
    	$fileFieldOptions = array('required' => false);
    	if ($team && ($webPath = $team->getWebPath())) {
    		// get the container so the full path to the image can be set
    		$container = $this->getConfigurationPool()->getContainer();
    		$fullPath = $container->get('request')->getBasePath().'/'.$webPath;
    	
    		// add a 'help' option containing the preview's img tag
    		$fileFieldOptions['help'] = '<img src="'.$fullPath.'" class="admin-preview" />';
    	}
    	$fileFieldOptions['data_class'] = null;
    	
        $formMapper
        ->add('name', 'text', array('label' => 'Nom'))
        ->add('iso')
        ->add('file', 'file', $fileFieldOptions)
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
        ->add('name')
        ->add('iso')
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {    	
        $listMapper
        ->addIdentifier('flag', 'string', array('label' => 'Drapeau', 'template' => 'DwfPronosticsBundle:Admin:list_flag.html.twig'))
        ->addIdentifier('name')
        ->add('iso')
        //->add('file', 'file',$fileFieldOptions)
        ;
    }
}