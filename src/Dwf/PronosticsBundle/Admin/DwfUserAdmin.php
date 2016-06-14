<?php

namespace Dwf\PronosticsBundle\Admin;

//use Sonata\UserBundle\Admin\Model\UserAdmin as BaseUserAdmin;

use Sonata\AdminBundle\Admin\Admin;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
//use Sonata\UserBundle\Admin\Model\UserAdmin as SonataUserAdmin;

use FOS\UserBundle\Model\UserManagerInterface;
use Sonata\AdminBundle\Route\RouteCollection;


class DwfUserAdmin extends Admin
{
    protected $userManager;

    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
        ->with('General')
        ->add('username')
        ->add('email')
        ->add('groups')
        ->end()
        // .. more info
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {

        $formMapper
        ->with('General')
        ->add('username')
        ->add('email')
        //->add('plainPassword', 'text', array('required' => false))
        ->add('plainPassword', 'dwf_pronosticsbundle_admin_password', array(
                'required' => (!$this->getSubject() || is_null($this->getSubject()->getId()))
        ))
        ->add('sendMail', 'checkbox', array('required' => false))
        ->end()
        // .. more info
        ;

        if (!$this->getSubject()->hasRole('ROLE_SUPER_ADMIN')) {
            $formMapper
            ->with('Management')
//             ->add('roles', 'sonata_security_roles', array(
//                     'expanded' => true,
//                     'multiple' => true,
//                     'required' => false
//             ))
            ->add('locked', null, array('required' => false))
            ->add('expired', null, array('required' => false))
            ->add('enabled', null, array('required' => false))
            ->add('credentialsExpired', null, array('required' => false))
            ->add('groups')
            ->end()
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $filterMapper)
    {
        $filterMapper
        ->add('id')
        ->add('username')
        ->add('locked')
        ->add('email')
        ;
    }
    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
        ->addIdentifier('username')
        ->add('email')
        ->add('groups')
        ->add('enabled', null, array('editable' => true))
        ->add('locked', null, array('editable' => true))
        ->add('createdAt')
        ;

        if ($this->isGranted('ROLE_ALLOWED_TO_SWITCH')) {
            $listMapper
            ->add('impersonating', 'string', array('template' => 'SonataUserBundle:Admin:Field/impersonating.html.twig'))
            ;
        }
    }

    public function prePersist($user)
    {
        $user->refreshUpdated();
    }

    public function preUpdate($user)
    {
        $user->refreshUpdated();
        $this->sendMail($user);

        $this->getUserManager()->updateCanonicalFields($user);
        $this->getUserManager()->updatePassword($user);

    }

    /**
     * {@inheritdoc}
     */
    public function sendMail($user)
    {
        if($user->getSendMail()) {

            $globalHeader = $this->getConfigurationPool()->getContainer()->getParameter('email_headers');
            $headers = $globalHeader['admin_account_creation'];

            $message = \Swift_Message::newInstance()
            ->setSubject($headers['subject'])
            ->setFrom($headers['from'])
            ->setTo($user->getEmail())
            ->setBcc($headers['bcc'])
            ->setBody($this->getConfigurationPool()->getContainer()->get('templating')
                    ->render('email/admin_creation.email.html.twig', array('user' => $user,
                                                                    'username' => $user->getUsername(),
                            'password' => $user->getPlainPassword(),

                    )))
            ->setContentType("text/html")
            ;

            $this->getConfigurationPool()->getContainer()->get('mailer')->send($message);
        }
    }
    public function setUserManager(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * @return UserManagerInterface
     */
    public function getUserManager()
    {
        return $this->userManager;
    }
}
