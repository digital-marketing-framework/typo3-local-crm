<?php

namespace DigitalMarketingFramework\Typo3\LocalCrm\Service;

use DigitalMarketingFramework\Core\Context\ContextInterface;
use DigitalMarketingFramework\Core\Context\WriteableContextInterface;
use DigitalMarketingFramework\Core\Exception\InvalidIdentifierException;
use DigitalMarketingFramework\Core\Model\Identifier\IdentifierInterface;
use DigitalMarketingFramework\LocalCrm\Service\AbstractLocalCrmService;
use DigitalMarketingFramework\Typo3\LocalCrm\Domain\Model\Data\UserData;
use DigitalMarketingFramework\Typo3\LocalCrm\Domain\Model\Identifier\Typo3LocalCrmUserIdentifier;
use DigitalMarketingFramework\Typo3\LocalCrm\Domain\Repository\Data\UserDataRepository;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Crypto\Random;
use TYPO3\CMS\Extbase\Security\Cryptography\HashService;

/**
 * @extends AbstractLocalCrmService<Typo3LocalCrmUserIdentifier>
 */
class Typo3LocalCrmService extends AbstractLocalCrmService
{
    /**
     * @var int
     */
    protected const USER_ID_LENGTH = 16;

    /**
     * @var string
     */
    public const COOKIE_NAME_USER_ID = 'local_crm_user_session';

    /**
     * @var string
     */
    public const COOKIE_NAME_USER_ID_HASH = 'local_crm_user_session_hash';

    protected int $expirationTime;

    public function __construct(
        protected ExtensionConfiguration $extensionConfiguration,
        protected HashService $hashService,
        protected Random $rng,
        protected UserDataRepository $userDataRepository,
    ) {
    }

    protected function getExpirationTime(): int
    {
        if (!isset($this->expirationTime)) {
            try {
                $expirationTimeInDays = $this->extensionConfiguration->get('dmf_local_crm')['storage']['expirationTime'] ?? 30;
            } catch (ExtensionConfigurationExtensionNotConfiguredException|ExtensionConfigurationPathDoesNotExistException) {
                $expirationTimeInDays = 30;
            }

            $this->expirationTime = $expirationTimeInDays * 3600 * 24;
        }

        return $this->expirationTime;
    }

    protected function generateUserId(): string
    {
        return $this->rng->generateRandomHexString(static::USER_ID_LENGTH);
    }

    protected function generateHash(string $userId): string
    {
        return $this->hashService->generateHmac($userId);
    }

    protected function validateHash(string $userId, string $hash): bool
    {
        return $this->hashService->validateHmac($userId, $hash);
    }

    public function validateIdentifier(Typo3LocalCrmUserIdentifier $identifier, bool $throwException = false): bool
    {
        if (!$this->validateHash($identifier->getUserId(), $identifier->getHash())) {
            if ($throwException) {
                throw new InvalidIdentifierException('User ID hash does not match.');
            }

            return false;
        }

        return true;
    }

    /**
     * @param Typo3LocalCrmUserIdentifier $identifier
     */
    public function read(IdentifierInterface $identifier): ?array
    {
        $this->validateIdentifier($identifier, true);

        $userData = $this->userDataRepository->findOneByUserId($identifier->getUserId());
        if (!$userData instanceof UserData) {
            return null;
        }

        return $this->decodeData($userData->getSerializedData());
    }

    /**
     * @param Typo3LocalCrmUserIdentifier $identifier
     */
    public function write(IdentifierInterface $identifier, array $data): void
    {
        $this->validateIdentifier($identifier, true);

        $userId = $identifier->getUserId();
        $serializedData = $this->encodeData($data);

        $userData = $this->userDataRepository->findOneByUserId($userId);
        if (!$userData instanceof UserData) {
            $userData = new UserData($userId, $serializedData);
            $this->userDataRepository->add($userData);
        } else {
            $userData->setUserId($userId);
            $userData->setSerializedData($serializedData);
            $this->userDataRepository->update($userData);
        }
    }

    public function prepareContext(ContextInterface $source, WriteableContextInterface $target): void
    {
        $identifier = $this->fetchIdentifierFromContext($source);
        if (!$identifier instanceof Typo3LocalCrmUserIdentifier) {
            $identifier = $this->createIdentifier();
        } else {
            $this->renewIdentifier($identifier);
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

        $identifier = new Typo3LocalCrmUserIdentifier($userId, $hash);

        if (!$this->validateIdentifier($identifier)) {
            return null;
        }

        return $identifier;
    }

    protected function setCookie(string $name, string $value): void
    {
        $expires = time() + $this->getExpirationTime();
        $path = '/';
        $domain = '';
        $secure = true;
        $httponly = true;

        setcookie($name, $value, [
            'expires' => $expires,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httponly,
        ]);
    }

    protected function renewIdentifier(Typo3LocalCrmUserIdentifier $identifier): void
    {
        $this->setCookie(static::COOKIE_NAME_USER_ID, $identifier->getUserId());
        $this->setCookie(static::COOKIE_NAME_USER_ID_HASH, $identifier->getHash());
    }

    public function createIdentifier(): Typo3LocalCrmUserIdentifier
    {
        $userId = $this->generateUserId();
        $hash = $this->generateHash($userId);
        $identifier = new Typo3LocalCrmUserIdentifier($userId, $hash);
        $this->renewIdentifier($identifier);

        return $identifier;
    }
}
