<?php

namespace DigitalMarketingFramework\Typo3\LocalCrm\Domain\Repository\Data;

use DigitalMarketingFramework\Typo3\LocalCrm\Domain\Model\Data\UserData;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @extends Repository<UserData>
 */
class UserDataRepository extends Repository
{
    protected int $pid;

    public function __construct(
        protected ExtensionConfiguration $extensionConfiguration
    ) {
        parent::__construct();
    }

    public function initializeObject(): void
    {
        /** @var QuerySettingsInterface $querySettings */
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(true);
        $querySettings->setStoragePageIds([$this->getPid()]);
        $this->setDefaultQuerySettings($querySettings);
    }

    protected function getPid(): int
    {
        if (!isset($this->pid)) {
            try {
                $this->pid = $this->extensionConfiguration->get('dmf_local_crm')['storage']['pid'] ?? 0;
            } catch (ExtensionConfigurationExtensionNotConfiguredException|ExtensionConfigurationPathDoesNotExistException) {
                $this->pid = 0;
            }
        }

        return $this->pid;
    }

    /**
     * @param object $userData
     */
    public function add($userData): void
    {
        $userData->setPid($this->getPid());
        parent::add($userData);
    }

    /**
     * @param object $userData
     */
    public function update($userData): void
    {
        $userData->setPid($this->getPid());
        parent::update($userData);
    }
}
