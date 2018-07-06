<?php
namespace Dwf\PronosticsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DwfPronosticsBundleAdminPasswordType extends AbstractType
{
    public function getParent()
    {
        return 'text';
    }

    public function getName()
    {
        return 'dwf_pronosticsbundle_admin_password';
    }

    }