<?php
namespace Dwf\PronosticsBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Dwf\PronosticsBundle\Entity\Pronostic;
use Dwf\PronosticsBundle\Entity\Scorer;
use Dwf\PronosticsBundle\Result\Result;

class GameAdmin extends Admin
{

	private $result;

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
        ->add('event')
        ->add('team1')
        ->add('team2')
        ->add('date', 'sonata_type_datetime_picker', array(
                //'dp_side_by_side'       => true,
                //'dp_use_current'        => false,
                'dp_use_seconds'        => false,
        ))
        ->add('type')
        ->add('scoreTeam1')
        ->add('scoreTeam2')
        ->add('overtime')
        ->add('scoreTeam1Overtime')
        ->add('scoreTeam2Overtime')
        ->add('winner')
        ->add('played')
        ->add('comment', 'textarea', array('label' => 'L\'avis de l\'expert', 'required' => false, 'attr' => array('class' => 'tinymce', 'data-theme' => 'advanced')))
        ->end()
        ;
        if ($this->getSubject() != null && $this->getSubject()->getId() != null) {
            $formMapper
            ->with('Buteurs')
            ->add('scorers', 'sonata_type_collection',
                    array(
                            //'options'  => array('data' => $this->getSubject()->getId()),
                            'by_reference' => false,
                            'cascade_validation' => true,
                            'required' => false
                    ),
                    array(
                            'edit' => 'inline',
                            'inline' => 'table',
                            //'sortable' => 'position',
                    )
            )
            ;
        }

    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
        ->add('event')
        ->add('team1')
        ->add('team2')
        ->add('date')
        ->add('type')
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
        ->addIdentifier('id')
        ->add('event')
        ->add('date')
        ->addIdentifier('flags', 'string', array('label' => 'Match', 'template' => 'DwfPronosticsBundle:Admin:list_game_flags.html.twig'))
        ->add('type')
        ->add('team1')
        ->add('team2')
        ->addIdentifier('score', 'string', array('label' => 'Score', 'template' => 'DwfPronosticsBundle:Admin:list_game_score.html.twig'))
        //->add('scoreTeam1')
        //->add('scoreTeam2')
        ;
    }

    public function postUpdate($game)
    {
        if($game->getPlayed()) {
            $this->result->setResultsForGame($game);
            if(!$game->getType()->getCanHaveOvertime())
                $this->result->setResultsForGroup($game);
        }
    		//Pronostic::setResultsForGame($game);
    }

    public function setResult(Result $result)
    {
    	$this->result = $result;
    }
}