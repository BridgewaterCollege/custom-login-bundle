<?php
namespace BridgewaterCollege\Bundle\CustomLoginBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader;

class BridgewaterCollegeCustomLoginExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // This loader, loads in the routing.yml and merges it with the main app's services container
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        /** attempt at using yaml instead of xml here... */
        //$loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        //$loader->load('services.yaml');

        //$configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $sillyServiceDefintion = $container->getDefinition( 'BridgewaterCollege\Bundle\CustomLoginBundle\Security\User\UserCreator' );
        if (isset($config['simplesaml']['attributes']))
            $sillyServiceDefintion->addMethodCall( 'setConfig', array( $config['simplesaml']['attributes']) );

        $securityControllerDefinition = $container->getDefinition( 'bridgewater_college_custom_login.controller.security_controller' );
        if (isset($config['local_return_url']))
            $securityControllerDefinition->addMethodCall('setConfig', array($config['local_return_url']));
        //$simplesamlConfiguration = $container->getDefinition('simplesaml_configuration');
        //$simplesamlConfiguration->addMethodCall('__construct', array($config['simplesaml']['config'], null));
    }
}