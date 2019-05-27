<?php

declare(strict_types=1);

namespace SphinxConfigTest\EntityTest;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

use SphinxConfig\Options\ModuleOptions;
use SphinxConfig\Entity\Service\ConfigFactory;
use SphinxConfig\Entity\Service\SectionFactory;
use SphinxConfig\Entity\Config\Section;
use SphinxConfig\Config\Reader\Yaml;

class ConfigTest extends TestCase
{
    protected $configSearchd = null;
    protected $configIndexer = null;

    protected function setUp()
    {
        \Zend\Config\Factory::registerReader('yaml', new Yaml(
                array('Symfony\Component\Yaml\Yaml', 'parse')
            )
        );

        $sphinxConfigModuleOptions = new ModuleOptions(
            [
                'config_id' => 'server-one',
                'config_dir' => './example/config/sphinx-config',
                'render_dir' => '/tmp'
            ]
        );
        
        $sectionFactory = new SectionFactory();
        $sectionFactory->setProto('source', new Section\Source);
        $sectionFactory->setProto('index', new Section\Index);
        $sectionFactory->setProto('searchd', new Section\Searchd);
        $sectionFactory->setProto('indexer', new Section\Indexer);

        $configFactory = new ConfigFactory($sphinxConfigModuleOptions, $sectionFactory);

        $this->configSearchd = $configFactory->getConfigForSearchd();
        $this->configIndexer = $configFactory->getConfigForIndexer();
    }

    public function testRender()
    {
        $searchdConfig = $this->configSearchd->render();
        $this->assertIsString($searchdConfig);
        $this->assertRegExp('/^source video_1\n{/m', $searchdConfig);
        $this->assertRegExp('/^source video_2\n{/m', $searchdConfig);
        $this->assertRegExp('/^source video_3\n{/m', $searchdConfig);
        $this->assertRegExp('/^source video_4\n{/m', $searchdConfig);
        $this->assertRegExp('/^index video_1\n{/m', $searchdConfig);
        $this->assertRegExp('/^index video_2\n{/m', $searchdConfig);
        $this->assertRegExp('/^index video_3\n{/m', $searchdConfig);
        $this->assertRegExp('/^index video_4\n{/m', $searchdConfig);
        $this->assertRegExp('/^index video\n{/m', $searchdConfig);
        $this->assertRegExp('/^searchd\n{/m', $searchdConfig);

        $indexerConfig = $this->configIndexer->render();
        $this->assertIsString($indexerConfig);
        $this->assertRegExp('/^source video_1\n{/m', $indexerConfig);
        $this->assertRegExp('/^source video_2\n{/m', $indexerConfig);
        $this->assertRegExp('/^source video_3\n{/m', $indexerConfig);
        $this->assertRegExp('/^source video_4\n{/m', $indexerConfig);
        $this->assertRegExp('/^index video_1\n{/m', $indexerConfig);
        $this->assertRegExp('/^index video_2\n{/m', $indexerConfig);
        $this->assertRegExp('/^index video_3\n{/m', $indexerConfig);
        $this->assertRegExp('/^index video_4\n{/m', $indexerConfig);
        $this->assertRegExp('/^index video\n{/m', $indexerConfig);
        $this->assertRegExp('/^searchd\n{/m', $indexerConfig);
    }
}
