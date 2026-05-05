<?php

namespace App\Models;

class TrainingProgram
{
    public function __construct(
        public int $id,
        public string $nameEn,
        public string $nameFr,
        public ?string $descriptionEn = null,
        public ?string $descriptionFr = null,
        public bool $isPublic = false,
        public ?int $createdBy = null,
        public string $createdAt = '',
        public string $updatedAt = '',
        public ?array $workouts = null
    ) {}

    /**
     * Map a RedBeanPHP bean to a typed TrainingProgram object.
     */
    public static function fromBean(object $bean): self
    {
        return new self(
            id: (int) $bean->id,
            nameEn: (string) $bean->name_en,
            nameFr: (string) $bean->name_fr,
            descriptionEn: $bean->description_en ? (string) $bean->description_en : null,
            descriptionFr: $bean->description_fr ? (string) $bean->description_fr : null,
            isPublic: (bool) ($bean->is_public ?? false),
            createdBy: $bean->created_by ? (int) $bean->created_by : null,
            createdAt: (string) ($bean->created_at ?? ''),
            updatedAt: (string) ($bean->updated_at ?? ''),
            workouts: null
        );
    }

    /**
     * Get name based on locale
     */
    public function getName(string $locale = 'en'): string
    {
        return $locale === 'fr' ? $this->nameFr : $this->nameEn;
    }

    /**
     * Get description based on locale
     */
    public function getDescription(string $locale = 'en'): ?string
    {
        return $locale === 'fr' ? $this->descriptionFr : $this->descriptionEn;
    }

    /**
     * Check if program is public
     */
    public function isPublic(): bool
    {
        return $this->isPublic;
    }
}