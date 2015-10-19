<?php

namespace SphinxConfig\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use SphinxConfig\Entity\Service\ConfigFactoryInterface;

class ConfigController extends AbstractActionController
{
    /**
     *
     * @var ConfigFactoryInterface
     */
    protected $configFactory = null;

    /**
     *
     * @param ConfigFactoryInterface $configFactory
     */
    public function __construct(ConfigFactoryInterface $configFactory)
    {
        $this->configFactory = $configFactory;
    }

    /**
     * Build config files for `searchd` and `indexer` tools
     *
     * @return ViewModel
     */
    public function buildAction()
    {
        $target = $this->params()->fromRoute('target', null);

        $this->configFactory->getConfigForIndexer($target)->save();
        $this->configFactory->getConfigForSearchd($target)->save();
    }
}
