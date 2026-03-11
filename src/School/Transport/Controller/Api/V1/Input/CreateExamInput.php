<?php

declare(strict_types=1);

namespace App\School\Transport\Controller\Api\V1\Input;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateExamInput
{
    #[Assert\NotBlank]
    public string $title = '';

    #[Assert\NotBlank]
    #[Assert\Uuid]
    public string $classId = '';

    #[Assert\NotBlank]
    #[Assert\Uuid]
    public string $teacherId = '';
}
