<?php

namespace Dwf\PronosticsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PronosticGameType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        for($i = 0; $i < 11; $i++)
            $arrayChoices[$i] = $i;
        $builder
            ->add('game', 'entity_hidden', array('class' => 'Dwf\PronosticsBundle\Entity\Game'))
            ->add('event', 'entity_hidden', array('class' => 'Dwf\PronosticsBundle\Entity\Event'))
            ->add('scoreTeam1', 'choice', array('label' => null,
                                                    'choices' => $arrayChoices))
            ->add('scoreTeam2', 'choice', array('label' => null,
                                                    'choices' => $arrayChoices))
            ->add('overtime', 'checkbox', array('label' => 'Ira aux prolongations ?', 'required' => false))
            ->add('scoreTeam1Overtime', 'choice', array('label' => null,
                                                    'choices' => $arrayChoices))
            ->add('scoreTeam2Overtime', 'choice', array('label' => null,
                                                    'choices' => $arrayChoices))
            ->add('winner', 'entity', array('label' => 'Vainqueur si nul', 
                                            'required' => false,
                                            'class' => 'Dwf\PronosticsBundle\Entity\Team',
                                            'query_builder' => function ($repository) use ($options) { return $options['data']->getGame() ? $repository->createQueryBuilder('t')
                                                                ->where('t.id IN (:teams)')
                                                                ->setParameter('teams',array($options['data']->getGame()->getTeam1(),$options['data']->getGame()->getTeam2()))
                                                                ->orderBy('t.id', 'ASC') : $repository->createQueryBuilder('t')
                                                                ->orderBy('t.id', 'ASC'); },
                                        )
                )
            ->add('user', 'entity_hidden', array('class' => 'Dwf\PronosticsBundle\Entity\User'))
            ->add('contest', 'entity_hidden', array('class' => 'Dwf\PronosticsBundle\Entity\Contest'))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Dwf\PronosticsBundle\Entity\Pronostic'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'dwf_pronosticsbundle_pronostic';
    }
}
