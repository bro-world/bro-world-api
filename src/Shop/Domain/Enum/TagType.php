<?php

declare(strict_types=1);

namespace App\Shop\Domain\Enum;

enum TagType: string
{
    case MARKETING = 'marketing';
    case INVENTORY = 'inventory';
    case SEASONAL = 'seasonal';
}
