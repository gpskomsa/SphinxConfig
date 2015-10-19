<?php

namespace SphinxConfig\Entity\Config\Section;

class Source extends Chunked
{
    /**
     *
     * @var string
     */
    protected $xmlpipeCommandTemplate = null;

    /**
     *
     * @param string $xmlpipeCommandTemplate
     */
    public function setXmlpipeCommandTemplate($xmlpipeCommandTemplate)
    {
        $this->xmlpipeCommandTemplate = $xmlpipeCommandTemplate;
    }

    /**
     * Hook for parameter's setup
     *
     * @param array $params
     */
    public function initialize(array $params)
    {
        parent::initialize($params);

        foreach ($this->chunks as $chunk) {
            if (!$chunk->xmlpipe_command
                && 'xmlpipe2' === $chunk->type) {
                $chunk->xmlpipe_command = $chunk->getXmlpipeCommand();
            }
        }

        if (!$this->xmlpipe_command
            && 'xmlpipe2' === $this->type) {
            $this->xmlpipe_command = $this->getXmlpipeCommand();
        }
    }

    /**
     * Builds xmlpipe_command option
     *
     * @param boolean $self
     * @return string
     */
    protected function getXmlpipeCommand()
    {
        if (is_string($this->xmlpipeCommandTemplate)) {
            return $this->xmlpipeCommandTemplate;
        }

        throw new \Exception('xmlpipeCommandTemplate is not defined');
    }
}