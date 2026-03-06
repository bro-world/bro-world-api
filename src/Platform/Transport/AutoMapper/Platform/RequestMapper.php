<?php

declare(strict_types=1);

namespace App\Platform\Transport\AutoMapper\Platform;

use App\General\Transport\AutoMapper\RestRequestMapper;

/**
 * @package App\Platform
 */
class RequestMapper extends RestRequestMapper
{
    /**
     * @var array<int, non-empty-string>
     */
    protected static array $properties = [
        'name',
        'description',
        'private',
        'photo',
        'enabled',
    ];
}
