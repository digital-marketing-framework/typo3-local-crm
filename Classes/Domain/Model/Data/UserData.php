<?php

namespace DigitalMarketingFramework\Typo3\LocalCrm\Domain\Model\Data;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class UserData extends AbstractEntity
{
    public function __construct(
        protected string $userId = '',
        protected string $serializedData = '',
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

    public function getSerializedData(): string
    {
        return $this->serializedData;
    }

    public function setSerializedData(string $serializedData): void
    {
        $this->serializedData = $serializedData;
    }
}
