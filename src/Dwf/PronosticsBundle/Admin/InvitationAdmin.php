<?php
namespace Dwf\PronosticsBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Dwf\PronosticsBundle\Mailer\UserMailer as Mailer;

class InvitationAdmin extends Admin
{
	private $mailer;
	
    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
        ->add('email', 'text', array('label' => 'Email'))
        //->add('code', 'text', array('label' => 'Code invitation'))
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
        ->add('email')
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {    	
        $listMapper
        ->addIdentifier('email')
        ->addIdentifier('code')
        ;
    }
    
    public function prePersist($invitation)
    {
    	$invitation->setInvitationCode();
    }
    
    public function postPersist($invitation)
    {
    	$body = "
    			<html>
    			<head></head>
    			<body>
    			<p>Bonjour,</p>
    			
    			<p>Vous venez d'etre invité à participer à l'application <strong>Pronostics</strong> de DWF.</p>
    			<p>
    			Voici vos identifiants pour y accéder:</p>
    			<ul>
    			<li><strong>Pseudo</strong>: à vous de le définir (merci d'utiliser un pseudo permettant à tout le monde de vous identifier)</li>
    			<li><strong>Email</strong>: " . $invitation->getEmail().
    			"</li>
    			<li><strong>Mot de passe</strong>: à vous de le définir</li>
    			<li><strong>Code d'invitation</strong>: " . $invitation->getCode()."</li>
    			</ul>
    			<p>Vous devez obligatoirement vous créer un compte afin de participer au jeu.</p>
    			<p>Pour vous créer un compte : <a href='http://pronostics.dwf.fr/register'>Me créer un compte</a> </p>
    			<p>Pour vous logguer si vous avez déjà un compte: <a href='http://pronostics.dwf.fr/login'>Me logguer</a></p>
    			<p><a href='http://pronostics.dwf.fr/1_reglement'>Consulter le règlement</a></p>
    			<p>Bonne chance</p>
 				</body>
    			</html>
    			";
    	$this->mailer->sendEmail("contact@dwf.fr", $invitation->getEmail(), $body, 'Invitation pour le jeu Pronostics Coupe du monde 2014');
    }
    
    public function setMailer(Mailer $mailer)
    {
    	$this->mailer = $mailer;
    }
}