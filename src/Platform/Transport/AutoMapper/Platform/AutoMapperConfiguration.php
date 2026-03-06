<?php

declare(strict_types=1);

namespace App\Platform\Transport\AutoMapper\Platform;

use App\General\Transport\AutoMapper\RestAutoMapperConfiguration;
use App\Platform\Application\DTO\Platform\PlatformCreate;
use App\Platform\Application\DTO\Platform\PlatformPatch;
use App\Platform\Application\DTO\Platform\PlatformUpdate;

/**
 * @package App\Platform
 */
class AutoMapperConfiguration extends RestAutoMapperConfiguration
{
    /**
     * Classes to use specified request mapper.
     *
     * @var array<int, class-string>
     */
    protected static array $requestMapperClasses = [
        PlatformCreate::class,
        PlatformUpdate::class,
        PlatformPatch::class,
    ];

    public function __construct(
        RequestMapper $requestMapper,
    ) {
        parent::__construct($requestMapper);
    }
}
