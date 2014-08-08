<?php

namespace Dwf\PronosticsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BestScorerPronosticType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        for($i = 0; $i < 40; $i++)
            $arrayChoices[$i] = $i;
        $builder
            ->add('goals', 'choice', array('label' => null, 
            								'choices' => $arrayChoices))            
            ->add('player', 'entity', array('label' => 'Joueur', 
                                            'class' => 'Dwf\PronosticsBundle\Entity\Player',
                                            'query_builder' => function ($repository) use ($options) { return $repository->createQueryBuilder('p')
                                                                ->orderBy('p.name', 'ASC'); },
                                        )
                )
            ->add('user', 'entity_hidden', array('class' => 'Dwf\PronosticsBundle\Entity\User'))
            ->add('event', 'entity_hidden', array('class' => 'Dwf\PronosticsBundle\Entity\Event'))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Dwf\PronosticsBundle\Entity\BestScorerPronostic'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'dwf_pronosticsbundle_bestscorer_pronostic';
    }
}
