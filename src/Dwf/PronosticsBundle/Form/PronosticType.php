<?php

namespace Dwf\PronosticsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PronosticType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('game')
            ->add('scoreTeam1')
            ->add('scoreTeam2')
            ->add('user', 'entity_hidden', array('class' => 'Dwf\PronosticsBundle\Entity\User'))
            //->add('user')
//             ->add('createdAt')
//             ->add('updatedAt')
//             ->add('expiresAt')
//            ->add('result')
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
