<?php

namespace SphinxConfig\Entity\Service;

interface ConfigFactoryInterface
{
    /**
     * @return SphinxConfig\Entity\Config
     */
    public function getConfigForSearchd($target);

    /**
     * @return SphinxConfig\Entity\Config
     */
    public function getConfigForIndexer($target);
}