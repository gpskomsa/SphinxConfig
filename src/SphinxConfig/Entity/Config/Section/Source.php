<?php

namespace SphinxConfig\Entity\Config\Section;

class Source extends Chunked
{
    /**
     * Hook for parameter's setup
     *
     * @param array $params
     */
    public function setParams(array $params)
    {
        parent::setParams($params);

        foreach ($this->chunks as $chunk) {
            if (!$chunk->xmlpipe_command
                && 'xmlpipe2' === $chunk->type) {
                $chunk->xmlpipe_command = $this->getXmlpipeCommand();
            }
        }

        if (!$this->xmlpipe_command
            && 'xmlpipe2' === $this->type) {
            $this->xmlpipe_command = $this->getXmlpipeCommand(true);
        }
    }

    /**
     * Builds xmlpipe_command option
     *
     * @param boolean $self
     * @return string
     */
    protected function getXmlpipeCommand($self = false)
    {
        return
            'php '
            . realpath('./public/index.php')
            . ' index build '
            . $this->sectionName
            . ($self === false ? '_{CHUNK_ID}' : '');
    }
}