<?php
namespace Dwf\PronosticsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InvitationContestOpenFormType extends AbstractType
{

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                'data_class' => 'Dwf\PronosticsBundle\Entity\Invitation'
        ));
    }
    
    public function getName()
    {
        return 'dwf_pronosticsbundle_invitation_contest_open';
    }
}