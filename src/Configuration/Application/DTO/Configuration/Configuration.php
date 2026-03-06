<?php

declare(strict_types=1);

namespace App\Configuration\Application\DTO\Configuration;

use App\Configuration\Domain\Entity\Configuration as Entity;
use App\Configuration\Domain\Enum\ConfigurationScope;
use App\General\Application\DTO\Interfaces\RestDtoInterface;
use App\General\Application\DTO\RestDto;
use App\General\Domain\Entity\Interfaces\EntityInterface;
use Override;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @package App\Configuration
 *
 * @method self|RestDtoInterface get(string $id)
 * @method self|RestDtoInterface patch(RestDtoInterface $dto)
 * @method Entity|EntityInterface update(EntityInterface $entity)
 */
class Configuration extends RestDto
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
    protected string $scope = ConfigurationScope::SYSTEM->value;

    #[Assert\NotNull]
    protected bool $private = false;

    public function getConfigurationKey(): string
    {
        return $this->configurationKey;
    }

    public function setConfigurationKey(string $configurationKey): self
    {
        $this->setVisited('configurationKey');
        $this->configurationKey = $configurationKey;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfigurationValue(): array
    {
        return $this->configurationValue;
    }

    /**
     * @param array<string, mixed> $configurationValue
     */
    public function setConfigurationValue(array $configurationValue): self
    {
        $this->setVisited('configurationValue');
        $this->configurationValue = $configurationValue;

        return $this;
    }

    public function getScope(): string
    {
        return $this->scope;
    }

    public function setScope(string $scope): self
    {
        $this->setVisited('scope');
        $this->scope = $scope;

        return $this;
    }

    public function isPrivate(): bool
    {
        return $this->private;
    }

    public function setPrivate(bool $private): self
    {
        $this->setVisited('private');
        $this->private = $private;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param EntityInterface|Entity $entity
     */
    #[Override]
    public function load(EntityInterface $entity): self
    {
        if ($entity instanceof Entity) {
            $this->id = $entity->getId();
            $this->configurationKey = $entity->getConfigurationKey();
            $this->configurationValue = $entity->getConfigurationValue();
            $this->scope = $entity->getScopeValue();
            $this->private = $entity->isPrivate();
        }

        return $this;
    }

    /**
     * @return array<int, string>
     */
    public static function getScopeValues(): array
    {
        return array_map(
            static fn (ConfigurationScope $scope): string => $scope->value,
            ConfigurationScope::cases(),
        );
    }
}
