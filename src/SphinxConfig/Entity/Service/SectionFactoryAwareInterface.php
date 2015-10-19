<?php

namespace SphinxConfig\Entity\Service;

interface SectionFactoryAwareInterface
{
    /**
     * Set section factory
     *
     * @param \SphinxConfig\Entity\Service\SectionFactoryInterface $sectionFactory
     */
    public function setSectionFactory(SectionFactoryInterface $sectionFactory);
}