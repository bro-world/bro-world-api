<?php

declare(strict_types=1);

namespace App\Tests\Unit\Platform\Domain\Entity;

use App\Platform\Domain\Entity\Application;
use App\Platform\Domain\Entity\Platform;
use App\Platform\Domain\Entity\Plugin;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Throwable;

class PhotoGenerationTest extends TestCase
{
    /**
     * @throws Throwable
     */
    #[TestDox('It generates avatar URL when plugin photo is not provided.')]
    public function testPluginPhotoDefaultsToAvatarUrl(): void
    {
        $plugin = (new Plugin())
            ->setName('Payment Plugin')
            ->setPhoto('')
            ->ensureGeneratedPhoto();

        self::assertStringStartsWith('https://ui-avatars.com/api/?name=Payment+Plugin', $plugin->getPhoto());
    }

    /**
     * @throws Throwable
     */
    #[TestDox('It normalizes plugin photo into uploads URL when a file name is provided.')]
    public function testPluginPhotoNormalizesToUploadsPath(): void
    {
        $plugin = (new Plugin())
            ->setPhoto('plugin-photo.png')
            ->ensureGeneratedPhoto();

        self::assertSame('/uploads/plugins/plugin-photo.png', $plugin->getPhoto());
    }

    /**
     * @throws Throwable
     */
    #[TestDox('It normalizes platform photo into uploads URL when a file name is provided.')]
    public function testPlatformPhotoNormalizesToUploadsPath(): void
    {
        $platform = (new Platform())
            ->setPhoto('platform-photo.png')
            ->ensureGeneratedPhoto();

        self::assertSame('/uploads/platforms/platform-photo.png', $platform->getPhoto());
    }

    /**
     * @throws Throwable
     */
    #[TestDox('It normalizes application photo into uploads URL when a file name is provided.')]
    public function testApplicationPhotoNormalizesToUploadsPath(): void
    {
        $application = (new Application())
            ->setPhoto('application-photo.png')
            ->ensureGeneratedPhoto();

        self::assertSame('/uploads/applications/application-photo.png', $application->getPhoto());
    }
}
