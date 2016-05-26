<?php
namespace Dwf\PronosticsBundle\Mailer;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Routing\RouterInterface;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Mailer\MailerInterface;

class UserMailer implements MailerInterface
{

    protected $mailer;
    protected $router;
    protected $templating;
    protected $parameters;

    public function __construct(\Swift_Mailer $mailer, RouterInterface $router, EngineInterface $templating, array $parameters)
    {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->templating = $templating;
        $this->parameters = $parameters;
    }

    public function sendConfirmationEmailMessage(UserInterface $user)
    {
        $app = $this->templating->getGlobals();
        $template = $this->parameters['confirmation.template'];
        $url = $this->router->generate('fos_user_registration_confirm', array('token' => $user->getConfirmationToken()), true);
        $rendered = $this->templating->render($template, array(
                'user'              => $user,
                'confirmationUrl'   =>  $url,
                'app_name'          => $app["app_name"]
        ));
        $this->sendEmailMessage($rendered, $this->getSenderEmail('confirmation'), $user->getEmail());
    }

    public function sendResettingEmailMessage(UserInterface $user)
    {
        $app = $this->templating->getGlobals();
        $template = $this->parameters['resetting_password.template'];
        $url = $this->router->generate('fos_user_resetting_reset', array('token' => $user->getConfirmationToken()), true);
        $rendered = $this->templating->render($template, array(
                'user'              => $user,
                'confirmationUrl'   => $url,
                'app_name'          => $app["app_name"]
        ));
        $this->sendEmailMessage($rendered, $this->getSenderEmail('resetting_password'), $user->getEmail());
    }

    protected function sendEmailMessage($renderedTemplate, $fromEmail, $toEmail)
    {
        // Render the email, use the first line as the subject, and the rest as the body
        $renderedLines = explode("\n", trim($renderedTemplate));
        $subject = $renderedLines[0];
        $body = implode("\n", array_slice($renderedLines, 1));

        if (strlen($body) == 0 || strlen($subject) == 0) {
            throw new \RuntimeException(
                    "No message was found, cannot send e-mail to " . $toEmail.". This " .
                    "error can occur when you don't have set a confirmation template or using the default " .
                    "without having translations enabled."
            );
        }

        $message = \Swift_Message::newInstance()
        ->setSubject($subject)
        ->setFrom($fromEmail)
        ->setTo($toEmail)
        ->setBody($body, 'text/html');
        
        
        $this->mailer->send($message);
    }

    protected function getSenderEmail($type)
    {
        return $this->parameters['from_email'][$type];
    }
    
    public function getName() {
        return 'user_mailer';
    }
}