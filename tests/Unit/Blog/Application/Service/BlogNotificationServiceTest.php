<?php

declare(strict_types=1);

namespace App\Tests\Unit\Blog\Application\Service;

use App\Blog\Application\Service\BlogNotificationService;
use App\Blog\Domain\Entity\Blog;
use App\Blog\Domain\Entity\BlogComment;
use App\Blog\Domain\Entity\BlogPost;
use App\Notification\Application\Service\NotificationPublisher;
use App\User\Domain\Entity\User;
use PHPUnit\Framework\TestCase;

final class BlogNotificationServiceTest extends TestCase
{
    public function testNotifyCommentCreatedOnPostTargetsPostAuthor(): void
    {
        $author = $this->createUser('Adam', 'Author');
        $commenter = $this->createUser('Rami', 'User');

        $post = (new BlogPost())
            ->setAuthor($author)
            ->setBlog(new Blog())
            ->setContent('My first post title');

        $comment = (new BlogComment())
            ->setPost($post)
            ->setAuthor($commenter)
            ->setContent('nice post');

        $publisher = $this->createMock(NotificationPublisher::class);
        $publisher->expects(self::once())
            ->method('publish')
            ->with(
                $commenter,
                $author,
                'Rami User commented your post "My first post title"',
                BlogNotificationService::BLOG_NOTIFICATION_TYPE,
            );

        (new BlogNotificationService($publisher))->notifyCommentCreated($comment);
    }

    public function testNotifyReactionCreatedOnReplyTargetsCommentAuthor(): void
    {
        $postAuthor = $this->createUser('Adam', 'Author');
        $replyAuthor = $this->createUser('Sara', 'Reply');
        $reactor = $this->createUser('Rami', 'User');

        $post = (new BlogPost())
            ->setAuthor($postAuthor)
            ->setBlog(new Blog())
            ->setContent('General update');

        $parent = (new BlogComment())
            ->setPost($post)
            ->setAuthor($postAuthor)
            ->setContent('parent');

        $reply = (new BlogComment())
            ->setPost($post)
            ->setParent($parent)
            ->setAuthor($replyAuthor)
            ->setContent('reply content');

        $publisher = $this->createMock(NotificationPublisher::class);
        $publisher->expects(self::once())
            ->method('publish')
            ->with(
                $reactor,
                $replyAuthor,
                'Rami User liked your comment "reply content"',
                BlogNotificationService::BLOG_NOTIFICATION_TYPE,
            );

        (new BlogNotificationService($publisher))->notifyReactionCreated($reply, $reactor, 'like');
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
