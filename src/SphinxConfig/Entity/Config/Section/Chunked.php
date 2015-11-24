<?php

namespace SphinxConfig\Entity\Config\Section;

use SphinxConfig\Entity\Config\Section;
use SphinxConfig\Entity\Service\SectionFactoryAwareInterface;
use SphinxConfig\Entity\Service\SectionFactoryInterface;

class Chunked extends Section implements SectionFactoryAwareInterface
{
    /**
     * Subsections
     *
     * @var array
     */
    protected $chunks = array();

    /**
     *
     * @var SectionFactoryInterface
     */
    protected $sectionFactory = null;

    /**
     *
     * @param SectionFactoryInterface $sectionFactory
     * @return \SphinxConfig\Entity\Config\Section\Chunked
     */
    public function setSectionFactory(SectionFactoryInterface $sectionFactory)
    {
        $this->sectionFactory = $sectionFactory;

        return $this;
    }

    /**
     * Hook for parameter's setup
     *
     * @param array $params
     */
    public function initialize(array $params)
    {
        if (isset($params['chunks'])
            && isset($params['chunks']['count'])) {
            $chunks = array_fill(1, $params['chunks']['count'], array());
            foreach ($chunks as $id => $chunkOptions) {
                $chunkOptions = array();
                if (isset($params['chunks']['common'])) {
                    $chunkOptions = $params['chunks']['common'];
                }
                if (isset($params['chunks']['custom'][$id])) {
                    $chunkOptions = $this->arrayMergeRecursive(
                        $chunkOptions,
                        $params['chunks']['custom'][$id]
                    );
                }

                $chunkOptions['constants'] = $this->constants;
                $chunkOptions['constants']['CHUNK_ID'] = $id;
                $chunkOptions['sectionType'] = $params['sectionType'];
                $chunkOptions['sectionName'] = $params['sectionName'] . '_' . $id;

                unset($chunkOptions['chunks']);
                $this->addChunk($chunkOptions);
            }

            unset($params['chunks']);
        }

        parent::initialize($params);
    }

    /**
     * Returns subsection by name
     *
     * @param string $name
     * @return Section|false
     */
    public function getChunkByName($name)
    {
        foreach ($this->chunks as $chunk) {
            if ($chunk->getName() === $name) {
                return $chunk;
            }
        }

        return false;
    }

    /**
     * Adds new subsection
     *
     * @param array $params
     * @return Chunked
     */
    protected function addChunk(array $params)
    {
        $this->chunks[count($this->chunks) + 1] = $this->sectionFactory->getSection($params);

        return $this;
    }

    /**
     *
     * @param integer $id
     * @return boolean
     */
    public function hasChunk($id)
    {
        return isset($this->chunks[$id]);
    }


    /**
     * Returns subsection by id
     *
     * @param integer $id
     * @return Section
     */
    public function getChunk($id)
    {
        if (!isset($this->chunks[$id])) {
            throw new \Exception('chunk ' . $id . ' is not exists');
        }

        return $this->chunks[$id];
    }

    /**
     * Checks if section has subsections
     *
     * @return boolean
     */
    public function hasChunks()
    {
        if ($this->getChunkCount()) {
            return true;
        }

        return false;
    }

    /**
     * Returns subsection names
     *
     * @return array
     */
    public function getChunkNames()
    {
        $names = array();
        foreach ($this->chunks as $chunk) {
            $names[] = $chunk->getName();
        }

        return $names;
    }

    /**
     * Returns subsection count
     *
     * @return integer
     */
    public function getChunkCount()
    {
        return count($this->chunks);
    }

    /**
     * Builds section view
     *
     * @param array $constants
     * @return string
     */
    public function render(array $constants = array())
    {
        $buffer = '';
        if (count($this->chunks)) {
            foreach ($this->chunks as $chunk) {
                $buffer .= $chunk->render($constants);
            }
        }

        return $buffer . parent::render($constants);
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
        foreach($two as $key => $value)
        {
            if(array_key_exists($key, $one) && is_array($value)) {
                $one[$key] = $this->arrayMergeRecursive($one[$key], $two[$key]);
            } else {
                $one[$key] = $value;
            }
        }

        return $one;
    }
}