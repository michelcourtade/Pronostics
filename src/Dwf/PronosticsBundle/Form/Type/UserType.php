<?php

namespace Dwf\PronosticsBundle\Form\Type;

use Sonata\CoreBundle\Form\Type\BooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('uniqueBet', CheckboxType::class, [
                'label' => 'Bets are identical on every event',
                'required' => false,
            ]
        );
    }

    public function getParent() {
        return 'FOS\UserBundle\Form\Type\ProfileFormType';
    }

    public function getBlockPrefix() {
        return 'app_user_profile';
    }
}