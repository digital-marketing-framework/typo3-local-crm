<?php

namespace DigitalMarketingFramework\Typo3\LocalCrm\Domain\Model\Identifier;

use DigitalMarketingFramework\Core\Model\Identifier\Identifier;

class Typo3LocalCrmUserIdentifier extends Identifier
{
    public function __construct(string $userId, string $hash)
    {
        parent::__construct(['id' => $userId, 'hash' => $hash]);
    }

    protected function getInternalCacheKey(): string
    {
        return $this->payload['id'];
    }

    public function getUserId(): string
    {
        return $this->payload['id'];
    }

    public function getHash(): string
    {
        return $this->payload['hash'];
    }
}
