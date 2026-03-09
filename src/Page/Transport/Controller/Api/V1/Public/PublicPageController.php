<?php

declare(strict_types=1);

namespace App\Page\Transport\Controller\Api\V1\Public;

use App\Page\Domain\Entity\About;
use App\Page\Domain\Entity\Contact;
use App\Page\Domain\Entity\Faq;
use App\Page\Domain\Entity\Home;
use App\Page\Domain\Entity\PageLanguage;
use App\Page\Infrastructure\Repository\AboutRepository;
use App\Page\Infrastructure\Repository\ContactRepository;
use App\Page\Infrastructure\Repository\FaqRepository;
use App\Page\Infrastructure\Repository\HomeRepository;
use App\Page\Infrastructure\Repository\PageLanguageRepository;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[OA\Tag(name: 'Page Public')]
final class PublicPageController
{
    public function __construct(
        private readonly PageLanguageRepository $pageLanguageRepository,
        private readonly HomeRepository $homeRepository,
        private readonly AboutRepository $aboutRepository,
        private readonly ContactRepository $contactRepository,
        private readonly FaqRepository $faqRepository,
    ) {
    }

    #[Route(path: '/v1/page/public/home/{languageCode}', methods: [Request::METHOD_GET])]
    #[OA\Get(security: [])]
    public function home(string $languageCode): JsonResponse
    {
        $language = $this->resolveLanguage($languageCode);

        /** @var Home|null $entity */
        $entity = $this->homeRepository->findOneBy(['language' => $language]);

        return $this->jsonContentOr404($entity?->getContent());
    }

    #[Route(path: '/v1/page/public/about/{languageCode}', methods: [Request::METHOD_GET])]
    #[OA\Get(security: [])]
    public function about(string $languageCode): JsonResponse
    {
        $language = $this->resolveLanguage($languageCode);

        /** @var About|null $entity */
        $entity = $this->aboutRepository->findOneBy(['language' => $language]);

        return $this->jsonContentOr404($entity?->getContent());
    }

    #[Route(path: '/v1/page/public/contact/{languageCode}', methods: [Request::METHOD_GET])]
    #[OA\Get(security: [])]
    public function contact(string $languageCode): JsonResponse
    {
        $language = $this->resolveLanguage($languageCode);

        /** @var Contact|null $entity */
        $entity = $this->contactRepository->findOneBy(['language' => $language]);

        return $this->jsonContentOr404($entity?->getContent());
    }

    #[Route(path: '/v1/page/public/faq/{languageCode}', methods: [Request::METHOD_GET])]
    #[OA\Get(security: [])]
    public function faq(string $languageCode): JsonResponse
    {
        $language = $this->resolveLanguage($languageCode);

        /** @var Faq|null $entity */
        $entity = $this->faqRepository->findOneBy(['language' => $language]);

        return $this->jsonContentOr404($entity?->getContent());
    }

    private function resolveLanguage(string $languageCode): PageLanguage
    {
        /** @var PageLanguage|null $language */
        $language = $this->pageLanguageRepository->findOneBy(['code' => $languageCode]);

        if ($language === null) {
            throw new NotFoundHttpException('Language not found.');
        }

        return $language;
    }

    /** @param array<string, mixed>|null $content */
    private function jsonContentOr404(?array $content): JsonResponse
    {
        if ($content === null) {
            throw new NotFoundHttpException('Page content not found.');
        }

        return new JsonResponse($content);
    }
}
