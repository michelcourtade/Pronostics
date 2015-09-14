<?php
namespace Dwf\PronosticsBundle\Form\Type;

use FOS\UserBundle\Form\Type\RegistrationFormType as BaseRegistrationFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;

class ContestFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add('name');
        $builder->add('event', 'entity_hidden', array('class' => 'Dwf\PronosticsBundle\Entity\Event'));
        $builder->add('owner', 'entity_hidden', array('class' => 'Dwf\PronosticsBundle\Entity\User'));
    }

    public function getName()
    {
        return 'dwf_pronosticsbundle_contest_type';
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                'data_class' => 'Dwf\PronosticsBundle\Entity\Contest'
        ));
    }
    
}