<?php

declare(strict_types=1);

namespace App\Recruit\Domain\Repository\Interfaces;

use App\Recruit\Domain\Entity\Recruit;

interface RecruitRepositoryInterface
{
    public function findOneByApplicationSlug(string $applicationSlug): ?Recruit;
}
