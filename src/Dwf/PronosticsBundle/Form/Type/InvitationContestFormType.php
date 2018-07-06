<?php
namespace Dwf\PronosticsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InvitationContestFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        //$builder->add('invitation', 'dwf_pronosticsbundle_invitation_type');
        $builder->add('email');
//         $builder->add('groups', 'entity', array(
//                                             'class'         => 'ApplicationSonataUserBundle:Group',
//                                             'label'         => 'Groupe',
//                                             'query_builder' => function ($repository) use ($options) { return $repository->createQueryBuilder('g')
//                                                                                                                          ->orderBy('g.name', 'ASC'); },
//                                             //'property' => 'name',
//                                             //'expanded' => true,
//                                             'multiple'      => true,
//                                             'required'      => true,
//                                             'empty_value'   => '--Choisir groupe--'
//                                             )
//         );
    }

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
        return 'dwf_pronosticsbundle_invitation_contest';
    }
}