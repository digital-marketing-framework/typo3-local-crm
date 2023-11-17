<?php

namespace DigitalMarketingFramework\Typo3\LocalCrm\Service;

use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Core\Context\WriteableContextInterface;
use DigitalMarketingFramework\Core\Model\Identifier\IdentifierInterface;
use DigitalMarketingFramework\LocalCrm\Service\AbstractLocalCrmService;
use DigitalMarketingFramework\Typo3\LocalCrm\Domain\Model\Data\UserData;
use DigitalMarketingFramework\Typo3\LocalCrm\Domain\Model\Identifier\Typo3LocalCrmUserIdentifier;
use DigitalMarketingFramework\Typo3\LocalCrm\Domain\Repository\Data\UserDataRepository;

/**
 * @extends AbstractLocalCrmService<Typo3LocalCrmUserIdentifier>
 */
class Typo3LocalCrmService extends AbstractLocalCrmService
{
    /**
     * @var string
     */
    public const COOKIE_NAME_USER_ID = 'local_crm_user_session';

    /**
     * @var string
     */
    public const COOKIE_NAME_USER_ID_HASH = 'local_crm_user_session_hash';

    public function __construct(
        protected UserDataRepository $userDataRepository,
    ) {
    }

    protected function generateUserId(): string
    {
        // TODO implement actual user ID generation
        return uniqid();
    }

    protected function generateHash(string $userId): string
    {
        // TODO implement actual salted hash
        return md5('FF1D547B4539893830EBA2322F0E9737::' . $userId);
    }

    protected function validateHash(string $userId, string $hash): bool
    {
        return $hash === $this->generateHash($userId);
    }

    public function validateIdentifier(Typo3LocalCrmUserIdentifier $identifier): bool
    {
        return $this->validateHash($identifier->getUserId(), $identifier->getHash());
    }

    /**
     * @param Typo3LocalCrmUserIdentifier $identifier
     */
    public function read(IdentifierInterface $identifier): ?array
    {
        if (!$this->validateIdentifier($identifier)) {
            return null;
        }

        $userData = $this->userDataRepository->findOneByUserId($identifier->getUserId());
        if (!$userData instanceof UserData) {
            return null;
        }

        return $this->decodeData($userData->getSerializedUserData());
    }

    /**
     * @param Typo3LocalCrmUserIdentifier $identifier
     */
    public function write(IdentifierInterface $identifier, array $data): void
    {
        $userId = $identifier->getUserId();
        $serializedData = $this->encodeData($data);

        $userData = $this->userDataRepository->findOneByUserId($userId);
        if (!$userData instanceof UserData) {
            $userData = new UserData($userId, $serializedData);
            $this->userDataRepository->add($userData);
        } else {
            $userData->setUserId($userId);
            $userData->setSerializedUserData($serializedData);
            $this->userDataRepository->update($userData);
        }
    }

    public function prepareContext(ContextInterface $source, WriteableContextInterface $target): void
    {
        $identifier = $this->fetchIdentifierFromContext($source);
        if (!$identifier instanceof Typo3LocalCrmUserIdentifier) {
            $identifier = $this->createIdentifier();
        }

        $target->setCookie(static::COOKIE_NAME_USER_ID, $identifier->getUserId());
        $target->setCookie(static::COOKIE_NAME_USER_ID_HASH, $identifier->getHash());
    }

    public function fetchIdentifierFromContext(ContextInterface $context): ?Typo3LocalCrmUserIdentifier
    {
        $userId = $context->getCookie(static::COOKIE_NAME_USER_ID);
        if ($userId === null) {
            return null;
        }

        $hash = $context->getCookie(static::COOKIE_NAME_USER_ID_HASH);
        if ($hash === null) {
            return null;
        }

        return $this->validateHash($userId, $hash) ? new Typo3LocalCrmUserIdentifier($userId, $hash) : null;
    }

    public function createIdentifier(): Typo3LocalCrmUserIdentifier
    {
        // TODO implement actual user ID generation and hash generation
        $userId = $this->generateUserId();
        $hash = $this->generateHash($userId);

        // TODO expiration date, path, domain?
        setcookie(static::COOKIE_NAME_USER_ID, $userId);
        setcookie(static::COOKIE_NAME_USER_ID_HASH, $hash);

        return new Typo3LocalCrmUserIdentifier($userId, $hash);
    }
}
