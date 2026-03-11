<?php

declare(strict_types=1);

namespace App\School\Transport\Controller\Api\V1\Input;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateGradeInput
{
    #[Assert\NotNull]
    #[Assert\Type(type: 'numeric')]
    #[Assert\Range(min: 0, max: 20)]
    public float $score = 0.0;

    #[Assert\NotBlank]
    #[Assert\Uuid]
    public string $studentId = '';

    #[Assert\NotBlank]
    #[Assert\Uuid]
    public string $examId = '';
}
