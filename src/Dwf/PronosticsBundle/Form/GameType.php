<?php

namespace Dwf\PronosticsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GameType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('team1')
            ->add('team2')
//             ->add('idTeam1', 'entity', array(
//             		'class' 		=> 'Dwf\PronosticsBundle\Entity\Team',
//             		'query_builder' => function ($repository) { return $repository->createQueryBuilder('s')->orderBy('s.id', 'ASC'); },
//             ))
//             ->add('idTeam2', 'entity', array(
//             		'class' 		=> 'Dwf\PronosticsBundle\Entity\Team',
//             		'query_builder' => function ($repository) { return $repository->createQueryBuilder('s')->orderBy('s.id', 'ASC'); },
//             ))
            ->add('date')
            ->add('type')
            ->add('scoreTeam1')
            ->add('scoreTeam2')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Dwf\PronosticsBundle\Entity\Game'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'dwf_pronosticsbundle_game';
    }
}
