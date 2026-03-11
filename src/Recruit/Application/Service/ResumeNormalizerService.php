<?php

declare(strict_types=1);

namespace App\Recruit\Application\Service;

use App\Recruit\Domain\Entity\Resume;

class ResumeNormalizerService
{
    /**
     * @param list<Resume> $resumes
     *
     * @return list<array<string, mixed>>
     */
    public function normalizeCollection(array $resumes): array
    {
        return array_map($this->normalize(...), $resumes);
    }

    /**
     * @return array<string, mixed>
     */
    public function normalize(Resume $resume): array
    {
        return [
            'id' => $resume->getId(),
            'documentUrl' => $resume->getDocumentUrl(),
            'experiences' => $this->normalizeSections($resume->getExperiences()->toArray()),
            'educations' => $this->normalizeSections($resume->getEducations()->toArray()),
            'skills' => $this->normalizeSections($resume->getSkills()->toArray()),
            'languages' => $this->normalizeSections($resume->getLanguages()->toArray()),
            'certifications' => $this->normalizeSections($resume->getCertifications()->toArray()),
            'projects' => $this->normalizeSections($resume->getProjects()->toArray()),
            'references' => $this->normalizeSections($resume->getReferences()->toArray()),
            'hobbies' => $this->normalizeSections($resume->getHobbies()->toArray()),
        ];
    }

    /**
     * @param array<int, object> $sections
     *
     * @return array<int, array<string, string>>
     */
    private function normalizeSections(array $sections): array
    {
        return array_map(
            static fn (object $section): array => [
                'id' => $section->getId(),
                'title' => $section->getTitle(),
                'description' => $section->getDescription(),
            ],
            $sections,
        );
    }
}
