<?php

namespace App\Models;

class Exercise
{
    public const DIFFICULTY_BEGINNER = 'beginner';
    public const DIFFICULTY_INTERMEDIATE = 'intermediate';
    public const DIFFICULTY_ADVANCED = 'advanced';

    public function __construct(
        public int $id,
        public ?int $categoryId,
        public string $nameEn,
        public string $nameFr,
        public ?string $descriptionEn = null,
        public ?string $descriptionFr = null,
        public string $difficulty = self::DIFFICULTY_BEGINNER,
        public ?string $equipment = null,
        public ?string $imageUrl = null,
        public bool $isActive = true,
        public string $createdAt = '',
        public string $updatedAt = '',
        public ?Category $category = null
    ) {}

    /**
     * Map a RedBeanPHP bean to a typed Exercise object.
     */
    public static function fromBean(object $bean): self
    {
        return new self(
            id: (int) $bean->id,
            categoryId: $bean->category_id ? (int) $bean->category_id : null,
            nameEn: (string) $bean->name_en,
            nameFr: (string) $bean->name_fr,
            descriptionEn: $bean->description_en ? (string) $bean->description_en : null,
            descriptionFr: $bean->description_fr ? (string) $bean->description_fr : null,
            difficulty: (string) ($bean->difficulty ?? self::DIFFICULTY_BEGINNER),
            equipment: $bean->equipment ? (string) $bean->equipment : null,
            imageUrl: $bean->image_url ? (string) $bean->image_url : null,
            isActive: (bool) ($bean->is_active ?? true),
            createdAt: (string) ($bean->created_at ?? ''),
            updatedAt: (string) ($bean->updated_at ?? ''),
            category: $bean->ownCategory ? Category::fromBean($bean->ownCategory) : null
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
     * Get difficulty label based on locale
     */
    public function getDifficultyLabel(string $locale = 'en'): string
    {
        $labels = [
            'en' => [
                self::DIFFICULTY_BEGINNER => 'Beginner',
                self::DIFFICULTY_INTERMEDIATE => 'Intermediate',
                self::DIFFICULTY_ADVANCED => 'Advanced'
            ],
            'fr' => [
                self::DIFFICULTY_BEGINNER => 'Débutant',
                self::DIFFICULTY_INTERMEDIATE => 'Intermédiaire',
                self::DIFFICULTY_ADVANCED => 'Avancé'
            ]
        ];

        return $labels[$locale][$this->difficulty] ?? $this->difficulty;
    }

    /**
     * Check if exercise is beginner level
     */
    public function isBeginner(): bool
    {
        return $this->difficulty === self::DIFFICULTY_BEGINNER;
    }

    /**
     * Check if exercise is intermediate level
     */
    public function isIntermediate(): bool
    {
        return $this->difficulty === self::DIFFICULTY_INTERMEDIATE;
    }

    /**
     * Check if exercise is advanced level
     */
    public function isAdvanced(): bool
    {
        return $this->difficulty === self::DIFFICULTY_ADVANCED;
    }
}