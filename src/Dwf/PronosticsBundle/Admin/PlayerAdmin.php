<?php
namespace Dwf\PronosticsBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class PlayerAdmin extends Admin
{
    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
    	$player = $this->getSubject();
    	
    	// use $fileFieldOptions so we can add other options to the field
    	$fileFieldOptions = array('required' => false);
    	if ($player && ($webPath = $player->getWebPath())) {
    		// get the container so the full path to the image can be set
    		$container = $this->getConfigurationPool()->getContainer();
    		$fullPath = $container->get('request')->getBasePath().'/'.$webPath;
    	
    		// add a 'help' option containing the preview's img tag
    		$fileFieldOptions['help'] = '<img src="'.$fullPath.'" class="admin-preview" />';
    	}
    	$fileFieldOptions['data_class'] = null;
    	
        $formMapper
        ->add('firstname', 'text', array('label' => 'Prénom', 'required' => false))        
        ->add('name', 'text', array('label' => 'Nom'))
        ->add('file', 'file', $fileFieldOptions)
        ->add('nationalTeam')
        ->add('team')
        ->add('active')
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
        ->add('firstname')
        ->add('name')
        ->add('active')
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {    	
        $listMapper
        ->addIdentifier('firstname')
        ->addIdentifier('name')
        ->add('team')
        ->add('nationalTeam')
        ->add('active')
        ;
    }
}