<?php

declare(strict_types=1);

namespace App\Configuration\Transport\EventListener;

use App\Configuration\Application\Service\Crypt\Interfaces\ConfigurationValueCryptServiceInterface;
use App\Configuration\Domain\Entity\Configuration;
use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * @package App\Configuration
 */
class ConfigurationEntityEventListener
{
    public function __construct(
        private readonly ConfigurationValueCryptServiceInterface $configurationValueCryptService,
    ) {
    }

    public function prePersist(LifecycleEventArgs $event): void
    {
        $this->process($event, 'encrypt');
    }

    public function postPersist(LifecycleEventArgs $event): void
    {
        $this->process($event, 'decrypt');
    }

    public function preUpdate(LifecycleEventArgs $event): void
    {
        $this->process($event, 'encrypt');
    }

    public function postUpdate(LifecycleEventArgs $event): void
    {
        $this->process($event, 'decrypt');
    }

    public function postLoad(LifecycleEventArgs $event): void
    {
        $this->process($event, 'decrypt');
    }

    private function process(LifecycleEventArgs $event, string $action): void
    {
        $configuration = $event->getObject();

        if (!$configuration instanceof Configuration) {
            return;
        }

        $action === 'encrypt'
            ? $this->configurationValueCryptService->encryptValue($configuration)
            : $this->configurationValueCryptService->decryptValue($configuration);
    }
}
