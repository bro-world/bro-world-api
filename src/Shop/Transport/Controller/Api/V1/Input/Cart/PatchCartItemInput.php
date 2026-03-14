<?php

declare(strict_types=1);

namespace App\Shop\Transport\Controller\Api\V1\Input\Cart;

use Symfony\Component\Validator\Constraints as Assert;

final class PatchCartItemInput
{
    #[Assert\GreaterThan(value: 0, message: 'quantity must be greater than 0.')]
    public int $quantity = 0;

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        $input = new self();
        $input->quantity = (int)($payload['quantity'] ?? 0);

        return $input;
    }
}
