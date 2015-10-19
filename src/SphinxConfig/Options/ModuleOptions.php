<?php
namespace SphinxConfig\Options;

use Zend\Stdlib\AbstractOptions;

class ModuleOptions extends AbstractOptions
{
    /**
     * @var string
     */
    protected $configId = 'default';

    /**
     * @var string
     */
    protected $configDir = './config/sphinx-config';

    /**
     * @var string
     */
    protected $renderDir = './data/sphinx-config';

    /**
     * set config id
     *
     * @param string $configId
     * @return ModuleOptions
     */
    public function setConfigId($configId)
    {
        $this->configId = $configId;

        return $this;
    }

    /**
     * get config id
     *
     * @return string
     */
    public function getConfigId()
    {
        return $this->configId;
    }

    /**
     * set directory with stored configs
     *
     * @param string $configDir
     * @return ModuleOptions
     */
    public function setConfigDir($configDir)
    {
        $this->configDir = $configDir;

        return $this;
    }

    /**
     * get directory with stored configs
     *
     * @return string
     */
    public function getConfigDir()
    {
        return $this->configDir;
    }

    /**
     * set directory to pull rendered configs
     *
     * @param string $renderDir
     * @return ModuleOptions
     */
    public function setRenderDir($renderDir)
    {
        $this->renderDir = $renderDir;

        return $this;
    }

    /**
     * get directory to pull rendered configs
     *
     * @return string
     */
    public function getRenderDir()
    {
        return $this->renderDir;
    }
}