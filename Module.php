<?php
namespace SphinxConfig;

use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\Mvc\MvcEvent;

class Module implements ConsoleUsageProviderInterface
{
    public function onBootstrap(MvcEvent $e)
    {
        \Zend\Config\Factory::registerReader('yaml', new Config\Reader\Yaml(
                array('Symfony\Component\Yaml\Yaml', 'parse')
            )
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getControllerConfig()
    {
        return array(
            'factories' => array(
                'SphinxConfig\Config' => function ($sm) {
                    return new Controller\ConfigController(
                        $sm->getServiceLocator()->get('ConfigFactory')
                    );
                },
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'SearchdConfig' => function($sm) {
                    return $sm->get('ConfigFactory')->getConfigForSearchd();
                },
                'IndexerConfig' => function($sm) {
                    return $sm->get('ConfigFactory')->getConfigForIndexer();
                },
                'SphinxConfigModuleOptions' => function($sm) {
                    $config = $sm->get('Config');
                    return new Options\ModuleOptions(isset($config['sphinx_config']) ? $config['sphinx_config'] : array());
                },
                'ConfigFactory' => function($sm) {
                    return new Entity\Service\ConfigFactory(
                        $sm->get('SphinxConfigModuleOptions'),
                        $sm->get('SectionFactory')
                    );
                }
            ),
            'invokables' => array(
                'SectionFactory' => 'SphinxConfig\Entity\Service\SectionFactory',
                'Section\Searchd' => 'SphinxConfig\Entity\Config\Section\Searchd',
                'Section\Source' => 'SphinxConfig\Entity\Config\Section\Source',
                'Section\Index' => 'SphinxConfig\Entity\Config\Section\Index',
                'Section\Chunked' => 'SphinxConfig\Entity\Config\Section\Chunked',
            ),
            'shared' => array(
                'Section\Searchd' => false,
                'Section\Source' => false,
                'Section\Index' => false,
                'Section\Chunked' => false,
            ),
        );
    }

    public function getConsoleUsage(Console $console)
    {
        return array(
            'To build sphinx configs for searchd and indexer tool:',
            'sphinxconfig build [<server_id>]' => 'build sphinx\'s configs for server with id <server_id>',
            array('<server_id>', 'server id, optional, default to current server id'),
        );
    }
}
