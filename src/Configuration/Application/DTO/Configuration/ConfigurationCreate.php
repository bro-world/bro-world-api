<?php

declare(strict_types=1);

namespace App\Configuration\Application\DTO\Configuration;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @package App\Configuration
 */
class ConfigurationCreate extends Configuration
{
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Assert\Length(min: 2, max: 255)]
    protected string $configurationKey = '';

    /**
     * @var array<string, mixed>
     */
    #[Assert\NotNull]
    protected array $configurationValue = [];

    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Assert\Choice(callback: [self::class, 'getScopeValues'])]
    protected string $scope = 'system';

    #[Assert\NotNull]
    protected bool $private = false;
}
