<?php

namespace SphinxConfig\Entity\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

use SphinxConfig\Entity\Config\Section;

class SectionFactory implements SectionFactoryInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

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

        switch ($type) {
            case 'searchd':
                $section = $this->serviceLocator->get('Section\Searchd');
                break;
            case 'source':
                $section = $this->serviceLocator->get('Section\Source');
                break;
            case 'index':
                $section = $this->serviceLocator->get('Section\Index');
                break;
            default:
                throw new \Exception('invalid type of section: ' . $type);
                break;
        }

        if ($section instanceof SectionFactoryAwareInterface) {
            $section->setSectionFactory($this);
        }

        $section->initialize($options);

        return $section;
    }
}