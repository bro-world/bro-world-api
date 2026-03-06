<?php

declare(strict_types=1);

namespace App\Platform\Domain\Repository\Interfaces;

/**
 * @package App\Platform
 */
interface PluginRepositoryInterface
{
    /**
     * @return array<int, \App\Platform\Domain\Entity\Plugin>
     */
    public function findPublicEnabled(): array;
}
