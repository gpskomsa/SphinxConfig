<?php

namespace SphinxConfig\Entity\Service;

use SphinxConfig\Entity\Config\Section\Chunked;
use SphinxConfig\Entity\Config\Section\Searchd;
use SphinxConfig\Entity\Config\Section\Source;
use SphinxConfig\Entity\Config\Section\Index;

class SectionFactory implements SectionFactoryInterface
{
    /**
     * Отдает объект Section
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

        switch ($type) {
            case 'searchd':
                $section = new Searchd($this, $options);
                break;
            case 'source':
                $section = new Source($this, $options);
                break;
            case 'index':
                $section = new Index($this, $options);
                break;
            default:
                $section = new Chunked($this, $options);
                break;
        }

        return $section;
    }
}