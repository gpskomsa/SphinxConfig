<?php

namespace SphinxConfig\Entity\Service;

use Zend\Config\Factory;

use SphinxConfig\Entity\Config;
use SphinxConfig\Options\ModuleOptions;

class ConfigFactory implements ConfigFactoryInterface
{
    const CONFIG_TYPE_SEARCHD = 'searchd';
    const CONFIG_TYPE_INDEXER = 'indexer';

    /**
     *
     * @var SectionFactory
     */
    protected $sectionFactory = null;

    /**
     *
     * @var ModuleOptions
     */
    protected $moduleOptions = null;

    /**
     *
     * @param ModuleOptions $moduleOptions
     * @param \SphinxConfig\Entity\Service\SectionFactory $sectionFactory
     */
    public function __construct(ModuleOptions $moduleOptions, SectionFactory $sectionFactory)
    {
        $this->moduleOptions = $moduleOptions;
        $this->sectionFactory = $sectionFactory;
    }

    /**
     * Returns config for `searchd`
     *
     * @param string $target
     * @param boolean $force
     * @return Config
     */
    public function getConfigForSearchd($target = null, $force = false)
    {
        return $this->getConfig($target, self::CONFIG_TYPE_SEARCHD, $force);
    }

    /**
     * Returns config for `indexer`
     *
     * @param string $target
     * @param boolean $force
     * @return Config
     */
    public function getConfigForIndexer($target = null, $force = false)
    {
        return $this->getConfig($target, self::CONFIG_TYPE_INDEXER, $force);
    }

    /**
     * Returns config object for specified target
     *
     * @param string $target
     * @param string $type
     * @return Config
     */
    public function getConfig($target = null, $type = self::CONFIG_TYPE_SEARCHD)
    {
        if (null === $target) {
            $target = $this->moduleOptions->configId;
        }

        $configData = $this->parse($target, $type);
        $options = array(
            'renderDirectory' => $this->moduleOptions->renderDir,
            'name' => $target . '_' . $type,
        );

        return new Config(
            $this->sectionFactory,
            $configData,
            $options
        );
    }

    /**
     * Reads configuration from file and returns it as array
     *
     * @param string $target
     * @return array
     */
    protected function parse($target, $type)
    {
        $configStorage = $this->moduleOptions->configDir;
        $filename = $configStorage . DIRECTORY_SEPARATOR . 'server'
                . DIRECTORY_SEPARATOR . $target . '.yaml';

        $config = $this->loadConfig($filename);

        $indexes = array();
        if (isset($config['indexes']['list'][$type])) {
            foreach ($config['indexes']['list'][$type] as $index) {
                $filename = $configStorage . DIRECTORY_SEPARATOR . 'index'
                        . DIRECTORY_SEPARATOR . $index . '.yaml';
                $indexes[$index] = $this->loadConfig($filename);

                if (isset($config['indexes'][$index])
                && is_array($config['indexes'][$index])) {
                    $indexes[$index] = $this->arrayMergeRecursive(
                        $indexes[$index],
                        $config['indexes'][$index]
                    );
                }
            }
        }

        $config['indexes'] = $indexes;

        return $config;
    }

    /**
     * Loads config file
     *
     * @param string $filename
     * @param array $inheritance
     * @return array
     */
    protected function loadConfig($filename, array $inheritance = array())
    {
        $config = $this->readFile($filename);

        foreach ($config as $section => &$data) {
            if (isset($data['extends'])) {
                if (in_array($data['extends'], $inheritance)) {
                    throw new \Exception('inheritance loop detected for ' . $data['extends']);
                }

                $ext = dirname($filename)
                    . DIRECTORY_SEPARATOR . $data['extends'] . '.yaml';

                array_push($inheritance, $data['extends']);
                $extend = $this->loadConfig($ext, $inheritance);
                array_pop($inheritance);

                if (isset($extend[$section])) {
                    $data = $this->arrayMergeRecursive($extend[$section], $data);
                }
                unset($data['extends']);
            }
        }

        return $config;
    }

    /**
     * Reads file from FS
     *
     * @param string $filename
     * @return array
     */
    protected function readFile($filename)
    {
        if (!file_exists($filename)) {
            throw new \Exception("$filename is not exists");
        }

        return Factory::fromFile($filename, false);
    }

    /**
     * Merges two arrays recursively
     *
     * @param array $one
     * @param array $two
     * @return array
     */
    public function arrayMergeRecursive($one, $two)
    {
        foreach ($two as $key => $value)
        {
            if (array_key_exists($key, $one) && is_array($value)) {
                $one[$key] = $this->arrayMergeRecursive($one[$key], $two[$key]);
            } else {
                $one[$key] = $value;
            }
        }

        return $one;
    }
}