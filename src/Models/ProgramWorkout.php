<?php

namespace App\Models;

class ProgramWorkout
{
    public function __construct(
        public int $id,
        public int $programId,
        public string $nameEn,
        public string $nameFr,
        public int $dayOrder,
        public ?string $descriptionEn = null,
        public ?string $descriptionFr = null,
        public ?array $exercises = null
    ) {}

    /**
     * Map a RedBeanPHP bean to a typed ProgramWorkout object.
     */
    public static function fromBean(object $bean): self
    {
        return new self(
            id: (int) $bean->id,
            programId: (int) $bean->program_id,
            nameEn: (string) $bean->name_en,
            nameFr: (string) $bean->name_fr,
            dayOrder: (int) $bean->day_order,
            descriptionEn: $bean->description_en ? (string) $bean->description_en : null,
            descriptionFr: $bean->description_fr ? (string) $bean->description_fr : null,
            exercises: null
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
}