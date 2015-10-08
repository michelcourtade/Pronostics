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
        ->with('General', array('description' => 'Informations générales sur l\'évènement'))
            ->add('sport')
            ->add('name', 'text', array('label' => 'Nom'))
            ->add('file', 'file', $fileFieldOptions)
            ->add('startDate')
            ->add('finishDate')
            ->add('championship', 'checkbox', array('label' => 'Championnat ?', 'required' => false))
            ->add('nationalTeams', 'checkbox', array('required' => false))
            ->add('nbPointsForLoss')
            ->add('nbPointsForDraw')
            ->add('nbPointsForWin')
            ->add('active', 'checkbox', array('required' => false))
        ->end()
        ->with('Pronostics simples', array('description' => 'Informations sur les pronostics de type 1 / N / 2'))
            ->add('simpleBet', 'checkbox', array('label' => 'Pari simple ? (1 N 2)', 'required' => false))
            ->add('scoreDiff', 'checkbox', array('label' => 'Avec ecart de score ?', 'required' => false))
            ->add('nbPointsForRightSimpleBet', 'integer', array('label' => 'Nb points bon pronostic', 'required' => false))
            ->add('nbPointsForWrongSimpleBet', 'integer', array('label' => 'Nb points mauvais pronostic', 'required' => false))
            ->add('nbPointsForRightSliceScore', 'integer', array('label' => 'Nb points pour bon écart de score trouvé', 'required' => false))
        ->end()
        ->with('Pronostics classiques', array('description' => 'Informations sur les pronostics "classiques" avec score'))
            ->add('nbPointsForRightBetWithScore', 'integer', array('label' => 'Nb points bon pronostic avec score', 'required' => false))
            ->add('nbPointsForRightBet', 'integer', array('label' => 'Nb points bon pronostic', 'required' => false))
            ->add('nbPointsForWrongBet', 'integer', array('label' => 'Nb points mauvais pronostic', 'required' => false))
            ->add('nbPointsForAlmostRightBet', 'integer', array('label' => 'Nb points bon pronostic mais pas exact', 'required' => false))
        ->end()
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
        ->add('name')
        ->add('sport')
        ->add('nationalTeams')
        ->add('championship')
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