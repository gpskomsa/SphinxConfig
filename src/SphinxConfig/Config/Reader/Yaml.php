<?php

namespace SphinxConfig\Config\Reader;

use Zend\Config\Reader\Yaml as ZendYamlReader;

class Yaml extends ZendYamlReader
{
    /**
     *
     * @param string $string
     * @return array
     */
    public function fromString($string)
    {
        $data = parent::fromString($string);
        return $this->processConstants($data, true);
    }

    /**
     *
     * @param string $filename
     * @return array
     */
    public function fromFile($filename)
    {
        $data = parent::fromFile($filename);
        return $this->processConstants($data, true);
    }

    /**
     * Replace php constant within keys and values of array by their values
     * (constant - %CONSTANT%)
     *
     * @param array $options
     * @param boolean $root Process on root level or not
     * @return array
     */
    protected function processConstants(array &$options, $root = false)
    {
        foreach ($options as $option => &$value) {
            if (!is_scalar($value)) {
                $this->processConstants($value);
            } else {
                $value = $this->processConstant($value);
            }

            if (!$root) {
                $tmp = $this->processConstant($option);
                if ($option !== $tmp) {
                    unset($options[$option]);
                }

                $options[$tmp] = $value;
            } else {
                if ($this->defineConstant($option, $value)) {
                    unset($options[$option]);
                }
            }
        }

        return $options;
    }

    /**
     * If value of $name has '%' around, it defines constant with
     * corresponding name and with value $value
     *
     * @param string $name
     * @param string $value
     * @return boolean
     */
    protected function defineConstant($name, $value)
    {
        if (preg_match('/^\%(.*?)\%$/i', $name, $matches)) {
            $const = $matches[1];
            if (!defined($const)) {
                define($const, $value);
            }

            return true;
        }

        return false;
    }

    /**
     * Replace instead %const% value of constant const
     * if that is defined
     *
     * @param string $value
     * @return mixed
     */
    protected function processConstant($value)
    {
        if (preg_match_all('/\%(.*?)\%/i', $value, $matches)) {
            foreach ($matches[1] as $const) {
                if (defined($const)) {
                    $tmp = constant($const);
                    $value = str_replace("%$const%", $tmp, $value);
                }
            }
        }

        return $value;
    }
}