<?php

namespace Dwf\PronosticsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Dwf\PronosticsBundle\Entity\SliceScoreRepository;

class SimplePronosticType extends AbstractType
{
	private $name = 'dwf_pronosticsbundle_pronostic';
	private $gameId;
	
	public function setName($name){
		$this->name .= '_'.$name;
		$this->gameId = $name;
	}
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            //->add('game')
            ->add('simpleBet', 'choice', 
                                array('expanded' => true, 
                                        'choices' => array('1' => '1','N' => 'N','2' => '2'),
                                        //'attr' => array('onclick'=>'$("#dwf_pronosticsbundle_pronostic").submit()')
            ))
            ->add('sliceScore', 'entity',
                    array('expanded' => false,
                            'class' => 'Dwf\PronosticsBundle\Entity\SliceScore',
                            'query_builder' => function (SliceScoreRepository $er) use ($options) {
                                return $er->createQueryBuilder('s')
                                ->leftJoin('s.sports', 'sp')
                                ->where('sp.id = :sport')
                                ->setParameter('sport', array($options['data']->getEvent()->getSport()))
                                ->orderBy('s.id', 'ASC');
                            },
                            'multiple' => false,
                            'required' => false,
                    ))
            ->add('user', 'entity_hidden', array('class' => 'Dwf\PronosticsBundle\Entity\User'))
            ->add('game', 'entity_hidden', array('class' => 'Dwf\PronosticsBundle\Entity\Game'))
            ->add('event', 'entity_hidden', array('class' => 'Dwf\PronosticsBundle\Entity\Event'))
            
            //->add('gameId', 'hidden', array('attr' => array('name' => 'gameId'),'data' => $this->gameId, 'mapped' => false))
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
        return $this->name;
    }
}
