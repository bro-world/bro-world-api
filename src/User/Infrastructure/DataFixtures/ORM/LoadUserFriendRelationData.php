<?php

declare(strict_types=1);

namespace App\User\Infrastructure\DataFixtures\ORM;

use App\User\Domain\Entity\User;
use App\User\Domain\Entity\UserFriendRelation;
use App\User\Domain\Enum\FriendStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Override;
use Throwable;

final class LoadUserFriendRelationData extends Fixture implements DependentFixtureInterface
{
    /**
     * @throws Throwable
     */
    #[Override]
    public function load(ObjectManager $manager): void
    {
        /** @var User $john */
        $john = $this->getReference('User-john', User::class);
        /** @var User $alice */
        $alice = $this->getReference('User-alice', User::class);
        /** @var User $bruno */
        $bruno = $this->getReference('User-bruno', User::class);
        /** @var User $clara */
        $clara = $this->getReference('User-clara', User::class);

        $pending = (new UserFriendRelation())
            ->setRequester($alice)
            ->setAddressee($john)
            ->setStatus(FriendStatus::PENDING);

        $accepted = (new UserFriendRelation())
            ->setRequester($john)
            ->setAddressee($bruno)
            ->setStatus(FriendStatus::ACCEPTED);

        $blocked = (new UserFriendRelation())
            ->setRequester($clara)
            ->setAddressee($john)
            ->setStatus(FriendStatus::BLOCKED);

        $manager->persist($pending);
        $manager->persist($accepted);
        $manager->persist($blocked);
        $manager->flush();
    }

    /** @return array<int, class-string> */
    #[Override]
    public function getDependencies(): array
    {
        return [LoadUserData::class];
    }
}
