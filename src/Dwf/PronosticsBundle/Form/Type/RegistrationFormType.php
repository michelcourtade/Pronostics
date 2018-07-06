<?php

namespace Dwf\PronosticsBundle\Form\Type;

use FOS\UserBundle\Form\Type\RegistrationFormType as BaseRegistrationFormType;
use Symfony\Component\Form\FormBuilderInterface;

class RegistrationFormType extends BaseRegistrationFormType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add('invitation', 'dwf_pronosticsbundle_invitation_type',
            array(
                'required' => false
            )
        );
    }

    /**
     * @return null|string|\Symfony\Component\Form\FormTypeInterface
     */
    public function getParent()
    {
        return 'fos_user_registration';
    }

    /**
     * @return null|string
     */
    public function getName()
    {
        return 'dwf_pronosticsbundle_user_registration';
    }

    /**
     * @return null|string
     */
    public function getBlockPrefix()
    {
        return 'dwf_fos_user_registration';
    }
}