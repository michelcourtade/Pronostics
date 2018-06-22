<?php
namespace Dwf\PronosticsBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;

class ChatMessageFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add('message');
        $builder->add('contest', 'entity_hidden', array('class' => 'Dwf\PronosticsBundle\Entity\Contest'));
        $builder->add('event', 'entity_hidden', array('class' => 'Dwf\PronosticsBundle\Entity\Event'));
        $builder->add('user', 'entity_hidden', array('class' => 'Dwf\PronosticsBundle\Entity\User'));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'dwf_pronosticsbundle_chat_message_form_type';
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                'data_class' => 'Dwf\PronosticsBundle\Entity\ChatMessage'
        ));
    }
}
