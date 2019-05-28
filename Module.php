<?php
namespace SphinxConfig;

use Zend\Mvc\MvcEvent;

class Module
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
                },
                'SectionFactory' => function($sm) {
                    $factory = new Entity\Service\SectionFactory();
                    $factory->setProto('source', $sm->get('Section\Source'));
                    $factory->setProto('index', $sm->get('Section\Index'));
                    $factory->setProto('searchd', $sm->get('Section\Searchd'));
                    $factory->setProto('indexer', $sm->get('Section\Indexer'));
                    return $factory;
                }
            ),
            'invokables' => array(
                'Section\Searchd' => 'SphinxConfig\Entity\Config\Section\Searchd',
                'Section\Indexer' => 'SphinxConfig\Entity\Config\Section\Indexer',
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
}
