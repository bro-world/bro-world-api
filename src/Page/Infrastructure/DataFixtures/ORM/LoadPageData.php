<?php

declare(strict_types=1);

namespace App\Page\Infrastructure\DataFixtures\ORM;

use App\Page\Domain\Entity\About;
use App\Page\Domain\Entity\Contact;
use App\Page\Domain\Entity\Faq;
use App\Page\Domain\Entity\Home;
use App\Page\Domain\Entity\PageLanguage;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Override;

final class LoadPageData extends Fixture implements OrderedFixtureInterface
{
    #[Override]
    public function load(ObjectManager $manager): void
    {
        $language = (new PageLanguage())
            ->setCode('fr')
            ->setLabel('Français');

        $manager->persist($language);

        $manager->persist((new Home())
            ->setLanguage($language)
            ->setContent([
                'hero' => [
                    'title' => 'Bienvenue sur Bro World',
                    'subtitle' => 'Une plateforme unifiée pour vos applications.',
                    'cta' => [
                        'label' => 'Commencer',
                        'url' => '/signup',
                    ],
                ],
                'highlights' => [
                    ['title' => 'Rapide', 'description' => 'Mise en route en quelques minutes.'],
                    ['title' => 'Sécurisé', 'description' => 'Protection des données de bout en bout.'],
                    ['title' => 'Flexible', 'description' => 'S’adapte à vos besoins métier.'],
                ],
            ]));

        $manager->persist((new About())
            ->setLanguage($language)
            ->setContent([
                'title' => 'À propos',
                'mission' => 'Aider les équipes à livrer plus vite avec une expérience cohérente.',
                'values' => [
                    'Qualité',
                    'Transparence',
                    'Innovation',
                ],
            ]));

        $manager->persist((new Contact())
            ->setLanguage($language)
            ->setContent([
                'title' => 'Contact',
                'email' => 'contact@bro-world.dev',
                'phone' => '+33 1 23 45 67 89',
                'address' => '10 Rue de la Paix, 75002 Paris, France',
            ]));

        $manager->persist((new Faq())
            ->setLanguage($language)
            ->setContent([
                'title' => 'FAQ',
                'items' => [
                    [
                        'question' => 'Comment créer un compte ?',
                        'answer' => 'Cliquez sur "Commencer" puis suivez les étapes d’inscription.',
                    ],
                    [
                        'question' => 'Puis-je changer de langue ?',
                        'answer' => 'Oui, vous pouvez sélectionner votre langue depuis les paramètres.',
                    ],
                ],
            ]));

        $manager->flush();
    }

    #[Override]
    public function getOrder(): int
    {
        return 10;
    }
}
