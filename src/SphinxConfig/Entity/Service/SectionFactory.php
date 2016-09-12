<?php

namespace SphinxConfig\Entity\Service;

use SphinxConfig\Entity\Config\Section;

class SectionFactory implements SectionFactoryInterface
{
    /**
     *
     * @var array
     */
    protected $proto = array();

    /**
     * Set proto of given type
     *
     * @param string $type
     * @param mixed $proto
     * @return \SphinxConfig\Entity\Service\SectionFactory
     */
    public function setProto($type, $proto)
    {
        if (!$proto instanceof Section) {
            throw new \Exception('Invalid type of section: ' . $type);
        }

        $this->proto[$type] = $proto;

        return $this;
    }

    /**
     * Returns Section object by its type
     *
     * @param array $options
     * @param string $type
     * @return Section
     */
    public function getSection(array $options, $type = null)
    {
        if (!isset($options['sectionType']) && null !== $type) {
            $options['sectionType'] = $type;
        }

        if (isset($options['sectionType']) && $options['sectionType'] && null === $type) {
            $type = $options['sectionType'];
        }

        if (!isset($this->proto[$type])) {
            throw new \Exception('Unknown type of section: ' . $type);
        }

        $section = clone $this->proto[$type];
        if ($section instanceof SectionFactoryAwareInterface) {
            $section->setSectionFactory($this);
        }

        $section->initialize($options);

        return $section;
    }
}