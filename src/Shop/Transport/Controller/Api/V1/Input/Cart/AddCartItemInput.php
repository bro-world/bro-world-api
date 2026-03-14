<?php

declare(strict_types=1);

namespace App\Shop\Transport\Controller\Api\V1\Input\Cart;

use Symfony\Component\Validator\Constraints as Assert;

final class AddCartItemInput
{
    #[Assert\NotBlank(message: 'productId is required.')]
    public string $productId = '';

    #[Assert\GreaterThan(value: 0, message: 'quantity must be greater than 0.')]
    public int $quantity = 1;

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        $input = new self();
        $input->productId = trim((string)($payload['productId'] ?? ''));
        $input->quantity = (int)($payload['quantity'] ?? 1);

        return $input;
    }
}
