<?php

declare(strict_types=1);

namespace App\Chat\Domain\Entity;

use App\General\Domain\Entity\Interfaces\EntityInterface;
use App\General\Domain\Entity\Traits\Timestampable;
use App\General\Domain\Entity\Traits\Uuid;
use App\User\Domain\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Override;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
#[ORM\Table(name: 'chat_message_reaction')]
#[ORM\UniqueConstraint(name: 'uq_chat_message_reaction_message_user_type', columns: ['message_id', 'user_id', 'reaction'])]
#[ORM\Index(name: 'idx_chat_message_reaction_message_id', columns: ['message_id'])]
#[ORM\Index(name: 'idx_chat_message_reaction_user_id', columns: ['user_id'])]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
class ChatMessageReaction implements EntityInterface
{
    use Timestampable;
    use Uuid;

    #[ORM\Id]
    #[ORM\Column(name: 'id', type: UuidBinaryOrderedTimeType::NAME, unique: true)]
    private UuidInterface $id;

    #[ORM\ManyToOne(targetEntity: ChatMessage::class, inversedBy: 'reactions')]
    #[ORM\JoinColumn(name: 'message_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ChatMessage $message;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(name: 'reaction', type: Types::STRING, length: 32)]
    private string $reaction = '';

    public function __construct()
    {
        $this->id = $this->createUuid();
    }

    #[Override]
    public function getId(): string
    {
        return $this->id->toString();
    }

    public function getMessage(): ChatMessage
    {
        return $this->message;
    }

    public function setMessage(ChatMessage $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getReaction(): string
    {
        return $this->reaction;
    }

    public function setReaction(string $reaction): self
    {
        $this->reaction = $reaction;

        return $this;
    }
}
