<?php
namespace Dwf\PronosticsBundle\Mailer;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Mailer\TwigSwiftMailer;
use Dwf\PronosticsBundle\Entity\Invitation;

class UserSwiftMailer extends TwigSwiftMailer implements MailerInterface
{

    public function sendInvitationEmailMessage(UserInterface $user, Invitation $invitation)
    {
        $app = $this->twig->getGlobals();
        $template = $this->parameters['template']['invitation'];
        $url = $this->router->generate('fos_user_registration_register', array('invitation' => $invitation->getCode(), 'email' => $invitation->getEmail()), true);

        $context = array(
                'user'              => $user,
                'confirmationUrl'   => $url,
                'invitation_code'   => $invitation->getCode(),
                'app_name'          => $app["app_name"]
        );

        $this->sendMessage($template, $context, $this->parameters['from_email']['invitation'], $invitation->getEmail());
    }

    public function sendAdminCreationEmailMessage(UserInterface $user)
    {
        $app = $this->twig->getGlobals();
        $template = $this->parameters['template']['admin_creation'];
        $url = $this->router->generate('fos_user_security_login');
        $context = array(
                'user'              => $user,
                'password'          => $user->getPlainPassword(),
                'loginUrl'          => $url,
                'app_name'          => $app["app_name"]
        );
        $this->sendMessage($template, $context, $this->parameters['from_email']['admin_creation'], $user->getEmail());
    }
    
    public function getName() {
        return 'user_swift_mailer';
    }
}