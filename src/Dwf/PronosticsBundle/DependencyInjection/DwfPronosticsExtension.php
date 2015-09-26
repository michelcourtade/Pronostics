<?php

namespace Dwf\PronosticsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class DwfPronosticsExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('dwf_pronostics.from_email', array($config['from_email']['address'] => $config['from_email']['sender_name']));
        //$container->setParameter('dwf_pronostics.from_email.sender_name', $config['from_email']['sender_name']);
        //$container->setParameter('dwf_pronostics.from_email.address', $config['from_email']['address']);
        
        $container->setParameter('dwf_pronostics.invitation.template', $config['invitation']['template']);
        
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
    }
}
