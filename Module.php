<?php
namespace SphinxConfig;

use Zend\Mvc\MvcEvent;

use Zend\ServiceManager\Factory\InvokableFactory;

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

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'SearchdConfig' => function($sm) {
                    return $sm->get(Entity\Service\ConfigFactoryInterface::class)->getConfigForSearchd();
                },
                'IndexerConfig' => function($sm) {
                    return $sm->get(Entity\Service\ConfigFactoryInterface::class)->getConfigForIndexer();
                },
                Options\ModuleOptions::class => function($sm) {
                    $config = $sm->get('config');
                    return new Options\ModuleOptions($config['sphinx_config'] ?? []);
                },
                Entity\Service\ConfigFactoryInterface::class => function($sm) {
                    return new Entity\Service\ConfigFactory(
                        $sm->get(Options\ModuleOptions::class),
                        $sm->get(Entity\Service\SectionFactory::class)
                    );
                },
                Entity\Service\SectionFactory::class => function($sm) {
                    $factory = new Entity\Service\SectionFactory();
                    $factory->setProto('source', $sm->get(Entity\Config\Section\Source::class));
                    $factory->setProto('index', $sm->get(Entity\Config\Section\Index::class));
                    $factory->setProto('searchd', $sm->get(Entity\Config\Section\Searchd::class));
                    $factory->setProto('indexer', $sm->get(Entity\Config\Section\Indexer::class));
                    return $factory;
                },
                Entity\Config\Section\Searchd::class => InvokableFactory::class,
                Entity\Config\Section\Indexer::class => InvokableFactory::class,
                Entity\Config\Section\Source::class => InvokableFactory::class,
                Entity\Config\Section\Index::class => InvokableFactory::class,
                Entity\Config\Section\Chunked::class => InvokableFactory::class,
            ),
            'shared_by_default' => array(
                Entity\Config\Section\Searchd::class => false,
                Entity\Config\Section\Source::class => false,
                Entity\Config\Section\Index::class => false,
                Entity\Config\Section\Chunked::class => false,
            ),
        );
    }
}
