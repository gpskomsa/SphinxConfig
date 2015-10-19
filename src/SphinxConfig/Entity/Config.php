<?php

namespace SphinxConfig\Entity;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;

use SphinxConfig\Entity\Service\SectionFactoryInterface;

use SphinxConfig\Entity\Config\Section\Chunked;

class Config
{
    /**
     *
     * @var SectionFactoryInterface
     */
    protected $sectionFactory = null;

    /**
     *
     * @var array
     */
    protected $constants = array();

    /**
     *
     * @var array
     */
    protected $sections = array();

    /**
     *
     * @var string|null
     */
    protected $renderDirectory = null;

    /**
     * Name of config file, can contain full path
     *
     * @var string
     */
    protected $name = null;

    /**
     *
     * @var EventManagerInterface
     */
    protected $events;

    /**
     *
     * @param SectionFactoryInterface $sectionFactory
     * @param array $config
     * @param array $options
     */
    public function __construct(
        SectionFactoryInterface $sectionFactory,
        array $config = array(),
        array $options = array()
    )
    {
        $this->setSectionFactory($sectionFactory);
        $this->setOptions($options);

        if (isset($config['constants'])) {
            $this->setConstants($config['constants']);
            isset($config['constants']);
        }

        $this->setConfig($config);
    }

    /**
     *
     * @param array $options
     * @return Config
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                call_user_func_array(array($this, $method), array($value));
            }
        }

        return $this;
    }

    /**
     *
     * @param string $renderDirectory
     * @return Config
     */
    public function setRenderDirectory($renderDirectory)
    {
        $this->renderDirectory = (string) $renderDirectory;

        return $this;
    }

    /**
     *
     * @param string $name
     * @return Config
     */
    public function setName($name)
    {
        $this->name = (string) $name;

        return $this;
    }

    /**
     * Determines and returns section of distributed index
     * by one of his chunk if that has
     *
     * @param string $index
     * @return Section|false
     */
    public function getDistributedFor($index)
    {
        foreach ($this->sections as $section) {
            if ($section->getType() === 'index') {
                if ($section->getChunkByName($index)) {
                    return $section;
                }
            }
        }

        return false;
    }

    /**
     *
     * @param EventManagerInterface $events
     * @return Config
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers(
            array(
                __CLASS__,
                get_called_class(),
            )
        );
        $this->events = $events;

        return $this;
    }

    /**
     *
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        if (null === $this->events) {
            $this->setEventManager(new EventManager());
        }

        return $this->events;
    }

    /**
     * Object configuration
     *
     * @param array $config
     */
    public function setConfig(array $config)
    {
        $this->sections = array();
        foreach ($config as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                call_user_func_array(array($this, $method), array($value));
            } else {
                $this->addSection($value, $key);
            }

            unset($config[$key]);
        }
    }

    /**
     * Indexes configuration
     *
     * @param array $options
     */
    public function setIndexes(array $options)
    {
        foreach ($options as $index => $indexConfig) {
            if (isset($indexConfig['source'])) {
                $indexConfig['source']['sectionName'] = $index;
                $this->addSection($indexConfig['source'], 'source');
            }
            if (isset($indexConfig['index'])) {
                $indexConfig['index']['sectionName'] = $index;
                $this->addSection($indexConfig['index'], 'index');
            }
        }
    }

    /**
     * Returns section by type and name
     *
     * @param string $type
     * @param string $name
     * @return Section|false
     */
    public function getSection($type, $name = null)
    {
        foreach ($this->sections as $section) {
            if ($section->getType() === $type) {
                if (null === $name) {
                    return $section;
                }

                if ($section->getName() === $name) {
                    return $section;
                }

                if ($section instanceof Chunked) {
                    $tmp = $section->getChunkByName($name);
                    if ($tmp) {
                        return $tmp;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Adds new section
     *
     * @param array $options
     * @param string $section
     */
    public function addSection(array $options, $section)
    {
        $options['constants'] = $this->constants;
        $this->sections[] = $this->sectionFactory->getSection($options, $section);
    }

    /**
     * Sets constants
     *
     * @param array $constants
     */
    public function setConstants(array $constants)
    {
        $this->constants = $constants;
    }

    /**
     * Sets section factory
     *
     * @param SectionFactoryInterface $sectionFactory
     */
    public function setSectionFactory(SectionFactoryInterface $sectionFactory)
    {
        $this->sectionFactory = $sectionFactory;
    }

    /**
     * Save rendered config into file
     *
     * @param string $path
     */
    public function save($path = null)
    {
        if (null === $path) {
            $path = $this->getPath();
        }

        file_put_contents($path, $this->render());

        $params = compact('path');
        $this->getEventManager()->trigger(__FUNCTION__, $this, $params);
    }

    /**
     * Checks if config file already exists
     *
     * @param string $path full filename with path
     * @return boolean
     */
    public function isSaved($path = null)
    {
        if (null === $path) {
            $path = $this->getPath();
        }

        return (boolean) file_exists($path);
    }

    /**
     * Returns full path to config file
     *
     * @return string
     * @throws \Exception
     */
    public function getPath()
    {
        if (null === $this->name) {
            throw new \Exception('config name is not defined');
        }

        $path = $this->name . '.conf';

        $dirname = dirname($path);
        if ($dirname !== '.') {
            if (!file_exists($dirname)) {
                throw new \Exception("$dirname is not exists");
            }
        } else {
            if (null !== $this->renderDirectory) {
                if (!file_exists($this->renderDirectory)) {
                    throw new \Exception($this->renderDirectory . " is not exists");
                }

                $path = $this->renderDirectory . DIRECTORY_SEPARATOR . $path;
            } else {
                $path = './module/SphinxConfig/data/config/' . $path;
            }
        }

        return $path;
    }

    public function __toString()
    {
        return $this->render();
    }

    /**
     * Render config data into string
     *
     * @return string
     */
    public function render()
    {
        $sections = array();
        foreach ($this->sections as $section) {
            $sections[] = $section->render($this->constants);
        }

        return implode("\n", $sections);
    }
}