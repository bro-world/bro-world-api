<?php

declare(strict_types=1);

namespace App\Recruit\Application\Service;

use App\Recruit\Domain\Entity\Recruit;
use App\Recruit\Domain\Repository\Interfaces\RecruitRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RecruitResolverService
{
    public function __construct(
        private readonly RecruitRepositoryInterface $recruitRepository,
    ) {
    }

    public function resolveByApplicationSlug(string $applicationSlug): Recruit
    {
        $recruit = $this->recruitRepository->findOneByApplicationSlug($applicationSlug);

        if (!$recruit instanceof Recruit) {
            throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, 'Unknown "applicationSlug".');
        }

        return $recruit;
    }
}
