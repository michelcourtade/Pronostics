<?php
namespace Dwf\PronosticsBundle\Mailer;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Routing\RouterInterface;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Mailer\TwigSwiftMailer;
use Dwf\PronosticsBundle\Entity\Invitation;

class UserSwiftMailer extends TwigSwiftMailer implements MailerInterface
{
    public function sendInvitationEmailMessage(UserInterface $user, Invitation $invitation)
    {
        $template = $this->parameters['template']['invitation'];
        $url = $this->router->generate('dwf_pronosticsbundle_invitation_confirm', array('invitation' => $invitation->getCode(), 'email' => $invitation->getEmail()), true);
    
        $context = array(
                'user' => $user,
                'confirmationUrl' => $url
        );
    
        $this->sendMessage($template, $context, $this->parameters['from_email']['invitation'], $invitation->getEmail());
    }
    
    public function getName() {
        return 'user_swift_mailer';
    }
}