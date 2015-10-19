<?php

namespace SphinxConfig\Entity\Config\Section;

class Index extends Chunked
{
    /**
     * Is index distributed?
     *
     * @var boolean
     */
    protected $distributed = false;

    /**
     * Hook for distributed index setup
     *
     * @param array $params
     */
    public function initialize(array $params)
    {
        parent::initialize($params);

        if ($this->distributed) {
            if (!isset($this->options['local'])
                && !isset($this->options['agent'])){
                $this->options['local'] = array_keys($this->chunks);
            }

            if (isset($this->options['local'])
                && isset($this->options['agent']))  {
                $intersect = array_intersect($this->options['local'], array_keys($this->options['agent']));
                if (!empty($intersect)) {
                    throw new \Exception('local and agent for `' . $this->sectionName . '` must not intersect');
                }
            }

            if (isset($this->options['local'])) {
                if (!is_array($this->options['local'])) {
                    $this->options['local'] = array($this->options['local']);
                }

                foreach ($this->options['local'] as $id => &$value) {
                    $value = $this->getChunk($value)->getName();
                }
            }

            if (isset($this->options['agent'])) {
                if (!is_array($this->options['agent'])) {
                    $this->options['agent'] = array($this->options['agent']);
                }

                foreach ($this->options['agent'] as $id => &$value) {
                    $value .= ':' . $this->getChunk($id)->getName();
                }
            }

            $this->options['type'] = 'distributed';
        }
    }
}