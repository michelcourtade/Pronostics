<?php
namespace Dwf\PronosticsBundle\Form\Type;

use FOS\UserBundle\Form\Type\RegistrationFormType as BaseRegistrationFormType;
use Symfony\Component\Form\FormBuilderInterface;

class RegistrationFormType extends BaseRegistrationFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add('invitation', 'dwf_pronosticsbundle_invitation_type');
        $builder->add('groups', 'entity', array(
                                            'class'         => 'ApplicationSonataUserBundle:Group',
                                            'label'         => 'Groupe',
                                            'query_builder' => function ($repository) use ($options) { return $repository->createQueryBuilder('g')
                                                                                                                         ->orderBy('g.name', 'ASC'); },
                                            //'property' => 'name',
                                            //'expanded' => true,
                                            'multiple'      => true,
                                            'required'      => true,
                                            'empty_value'   => '--Choisir groupe--'
                                            )
        );
    }

    public function getName()
    {
        return 'dwf_pronosticsbundle_user_registration';
    }
}