<?php

namespace SphinxConfig\Entity\Config\Section;

use SphinxConfig\Entity\Config\Section;

class Searchd extends Section
{
    /**
     *
     * @var array
     */
    protected $requiredOptions = array(
        'listen',
        'pid_file'
    );
}