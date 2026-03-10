<?php

declare(strict_types=1);

namespace App\Tests\Unit\Notification\Application\Service;

use App\Notification\Application\Service\NotificationPublisher;
use App\Notification\Domain\Entity\Notification;
use App\Notification\Infrastructure\Repository\NotificationRepository;
use App\User\Domain\Entity\User;
use PHPUnit\Framework\TestCase;

final class NotificationPublisherTest extends TestCase
{
    public function testPublishSkipsSelfNotifications(): void
    {
        $user = $this->createUser('Rami', 'User');

        $repository = $this->createMock(NotificationRepository::class);
        $repository->expects(self::never())->method('save');

        $publisher = new NotificationPublisher($repository);
        $publisher->publish($user, $user, 'title', 'blog_notification');
    }

    public function testPublishPersistsNotification(): void
    {
        $from = $this->createUser('Rami', 'User');
        $recipient = $this->createUser('Adam', 'Author');

        $repository = $this->createMock(NotificationRepository::class);
        $repository->expects(self::once())
            ->method('save')
            ->with(self::callback(static function (Notification $notification) use ($from, $recipient): bool {
                return $notification->getFrom() === $from
                    && $notification->getRecipient() === $recipient
                    && $notification->getTitle() === 'Rami commented your post "Post title"'
                    && $notification->getType() === 'blog_notification'
                    && $notification->getDescription() === '';
            }));

        $publisher = new NotificationPublisher($repository);
        $publisher->publish($from, $recipient, 'Rami commented your post "Post title"', 'blog_notification');
    }

    private function createUser(string $firstName, string $lastName): User
    {
        return (new User())
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setUsername(strtolower($firstName . '.' . $lastName))
            ->setEmail(strtolower($firstName . '.' . $lastName . '@example.com'));
    }
}
