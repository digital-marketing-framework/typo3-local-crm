<?php

namespace DigitalMarketingFramework\Typo3\LocalCrm\Domain\Model\Data;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class UserData extends AbstractEntity
{
    public function __construct(
        protected string $userId = '',
        protected string $serializedUserData = '',
    ) {
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    public function getSerializedUserData(): string
    {
        return $this->serializedUserData;
    }

    public function setSerializedUserData(string $serializedUserData): void
    {
        $this->serializedUserData = $serializedUserData;
    }
}
