<?php

namespace SphinxConfig\Entity\Config;

abstract class Section
{
    /**
     * Required options
     *
     * @var array
     */
    protected $requiredOptions = array();

    /**
     * Section type (source, index and etc.)
     *
     * @var string
     */
    protected $sectionType = null;

    /**
     * Section name
     *
     * @var string
     */
    protected $sectionName = null;

    /**
     * Section's parent(not working)
     *
     * @var string
     */
    protected $sectionExtends = null;

    /**
     * Constants for current object
     *
     * @var array
     */
    protected $constants = array();

    /**
     * Section's options
     *
     * @var array
     */
    protected $options = array();

    /**
     * Setup object
     *
     * @param array $params
     * @return Section
     */
    public function initialize(array $params)
    {
        $this->reset();

        if (!isset($params['sectionType'])) {
            throw new \Exception('sectionType not defined');
        }

        if (isset($params['sectionName'])) {
            $params['constants']['SECTION_NAME'] = $params['sectionName'];
        }

        if (isset($params['constants'])) {
            $this->setConstants($params['constants']);
            unset($params['constants']);
        }

        foreach ($params as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                call_user_func_array(array($this, $method), array($value));
            } else if (property_exists($this, $key)) {
                $this->{$key} = $this->applyConstants($value);
            }
        }

        return $this;
    }

    /**
     * Resets object state
     *
     * @return \SphinxConfig\Entity\Config\Section
     */
    public function reset()
    {
        $this->options = array();
        $this->constants = array();

        return $this;
    }

    /**
     *
     * @param array $constants
     * @return \SphinxConfig\Entity\Config\Section
     */
    public function setConstants(array $constants)
    {
        $this->constants = $constants;

        return $this;
    }

    /**
     * Object's options setup
     *
     * @param array $options
     * @return Section
     */
    public function setOptions(array $options)
    {
        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }

        return $this;
    }

    /**
     * Set option
     *
     * @param string $name
     * @param string $value
     * @return Section
     */
    public function setOption($name, $value)
    {
        $name = (string) $this->applyConstants($name);
        $this->options[$name] = $this->applyConstants($value);

        return $this;
    }

    /**
     * Gets option value by name
     *
     * @param string $name
     * @return string
     */
    public function getOption($name)
    {
        if (isset($this->options[$name])) {
            return $this->options[$name];
        }

        return null;
    }

    /**
     * Gets option name
     *
     * @return string
     */
    public function getName()
    {
        return $this->sectionName;
    }

    /**
     * Gets option type
     *
     * @return string
     */
    public function getType()
    {
        return $this->sectionType;
    }

    /**
     *
     * @param string $name
     * @param string $value
     * @return Section
     */
    public function __set($name, $value)
    {
        return $this->setOption($name, $value);
    }

    /**
     *
     * @param string $name
     * @return Section
     */
    public function __get($name)
    {
        return $this->getOption($name);
    }

    /**
     * Returns section view
     *
     * @return string
     */
    public function render()
    {
        if (empty($this->options)) {
            return '';
        }

        $missedRequired = array_diff_key(array_flip($this->requiredOptions), $this->options);
        if (count($missedRequired)) {
            throw new \Exception('Missed required options: ' . implode(',', array_keys($missedRequired)));
        }

        $buffer = $this->getTitle();
        $buffer .= "\n{\n";
        $buffer .= $this->getBody();
        $buffer .= "}\n\n";

        return $buffer;
    }

    /**
     * Returns title text of section
     *
     * @return string
     */
    public function getTitle()
    {
        $title = $this->sectionType;

        if (!$title) {
            throw new \Exception('section type is not defined');
        }

        if ($this->getName()) {
            $title .= ' ' . $this->getName();
        }

        if ($this->sectionExtends) {
            $title .= ' : ' . $this->sectionExtends;
        }

        return $this->applyConstants($title);
    }

    /**
     * Returns body text of section
     *
     * @return string
     */
    public function getBody()
    {
        $buffer = '';
        foreach ($this->options as $name => $value) {
            if (is_scalar($value)) {
                $buffer .= "  $name = $value\n";
            } elseif (is_array($value)) {
                foreach ($value as $val) {
                    if (is_scalar($val)) {
                        $buffer .= "  $name = $val\n";
                    }
                }
            }
        }

        return $buffer;
    }

    /**
     * Find constants in value and replace them
     *
     * @param string $value
     * @return string
     */
    protected function applyConstants($value)
    {
        foreach ($this->constants as $key => $const) {
            if (is_scalar($value)) {
                $value = str_replace('{' . $key . '}', $const, $value);
            } else if (is_array($value)) {
                foreach ($value as $key => &$val) {
                    $val = $this->applyConstants($val);
                }
            }
        }

        return $value;
    }

}