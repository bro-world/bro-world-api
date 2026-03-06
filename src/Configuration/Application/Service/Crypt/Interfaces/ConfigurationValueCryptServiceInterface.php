<?php

declare(strict_types=1);

namespace App\Configuration\Application\Service\Crypt\Interfaces;

use App\Configuration\Domain\Entity\Configuration;

/**
 * @package App\Configuration
 */
interface ConfigurationValueCryptServiceInterface
{
    public function encryptValue(Configuration $configuration): void;

    public function decryptValue(Configuration $configuration): void;
}
