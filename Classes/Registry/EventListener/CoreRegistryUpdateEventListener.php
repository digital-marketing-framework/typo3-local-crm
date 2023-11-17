<?php

namespace DigitalMarketingFramework\Typo3\LocaleCrm\Registry\EventListener;

use DigitalMarketingFramework\LocalCrm\LocalCrmInitialization;
use DigitalMarketingFramework\Typo3\Core\Registry\EventListener\AbstractCoreRegistryUpdateEventListener;
use DigitalMarketingFramework\Typo3\LocalCrm\Service\Typo3LocalCrmService;

class CoreRegistryUpdateEventListener extends AbstractCoreRegistryUpdateEventListener
{
    public function __construct(Typo3LocalCrmService $crm)
    {
        parent::__construct(new LocalCrmInitialization($crm, 'dmf_local_crm'));
    }
}
