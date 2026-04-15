<?php

declare(strict_types=1);

namespace App\Shop\Infrastructure\Payment;

use App\Shop\Domain\Service\Interfaces\PaymentProviderInterface;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Webhook;
use UnexpectedValueException;

use function hash;
use function is_array;
use function is_string;

final readonly class StripePaymentProvider implements PaymentProviderInterface
{
    public function __construct(
        private string $secretKey,
        private string $webhookSecret,
    ) {
        Stripe::setApiKey($this->secretKey);
    }

    /**
     * @param array<string,mixed> $metadata
     * @return array<string,mixed>
     */
    public function createIntent(
        string $orderId,
        int $amount,
        string $currency,
        array $metadata = [],
    ): array {
        $intent = PaymentIntent::create([
            'amount' => $amount, // cents
            'currency' => strtolower($currency),
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
            'metadata' => array_merge($metadata, [
                'orderId' => $orderId,
            ]),
        ]);

        return [
            'provider' => 'stripe',
            'providerReference' => $intent->id,
            'status' => 'requires_confirmation',
            'payload' => [
                'clientSecret' => $intent->client_secret,
                'paymentIntentId' => $intent->id,
            ],
        ];
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function confirm(string $providerReference, array $payload = []): array
    {
        $intent = PaymentIntent::retrieve($providerReference);

        return [
            'provider' => 'stripe',
            'providerReference' => $intent->id,
            'status' => $this->mapIntentStatus((string) $intent->status),
            'payload' => $intent->toArray(),
        ];
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>|null
     */
    public function verifyWebhook(array $payload, ?string $signature = null): ?array
    {
        if ($signature === null || $signature === '') {
            return null;
        }

        try {
            $event = Webhook::constructEvent(
                json_encode($payload, JSON_THROW_ON_ERROR),
                $signature,
                $this->webhookSecret
            );
        } catch (UnexpectedValueException|SignatureVerificationException|\JsonException) {
            return null;
        }

        $object = $event->data->object;

        if (!isset($object->id)) {
            return null;
        }

        $providerReference = (string) $object->id;

        return match ($event->type) {
            'payment_intent.succeeded' => [
                'provider' => 'stripe',
                'providerReference' => $providerReference,
                'status' => 'succeeded',
                'webhookKey' => $this->buildWebhookKey($event->id),
                'payload' => $payload,
            ],

            'payment_intent.payment_failed' => [
                'provider' => 'stripe',
                'providerReference' => $providerReference,
                'status' => 'failed',
                'webhookKey' => $this->buildWebhookKey($event->id),
                'payload' => $payload,
            ],

            'payment_intent.requires_action' => [
                'provider' => 'stripe',
                'providerReference' => $providerReference,
                'status' => 'requires_confirmation',
                'webhookKey' => $this->buildWebhookKey($event->id),
                'payload' => $payload,
            ],

            default => null,
        };
    }

    private function mapIntentStatus(string $status): string
    {
        return match ($status) {
            'succeeded' => 'succeeded',
            'requires_payment_method',
            'requires_confirmation',
            'requires_action',
            'processing' => 'requires_confirmation',
            'canceled' => 'failed',
            default => 'created',
        };
    }

    private function buildWebhookKey(string $eventId): string
    {
        return hash('sha256', 'stripe_' . $eventId);
    }
}
