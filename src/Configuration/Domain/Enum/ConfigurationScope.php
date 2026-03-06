<?php

declare(strict_types=1);

namespace App\Configuration\Domain\Enum;

/**
 * @package App\Configuration
 */
enum ConfigurationScope: string
{
    case SYSTEM = 'system';
    case USER = 'user';
    case PLATFORM = 'platform';
    case PLUGIN = 'plugin';
    case PUBLIC = 'public';
}
