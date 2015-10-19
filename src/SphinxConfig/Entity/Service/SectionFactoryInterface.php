<?php

namespace SphinxConfig\Entity\Service;

interface SectionFactoryInterface
{
    /**
     * @return SphinxConfig\Entity\Config\Section
     */
    public function getSection(array $options, $type = null);
}