<?php

namespace App\Models;

class Category
{
    public function __construct(
        public int $id,
        public string $nameEn,
        public string $nameFr,
        public ?string $descriptionEn = null,
        public ?string $descriptionFr = null,
        public string $createdAt = ''
    ) {}

    /**
     * Map a RedBeanPHP bean to a typed Category object.
     */
    public static function fromBean(object $bean): self
    {
        return new self(
            id: (int) $bean->id,
            nameEn: (string) $bean->name_en,
            nameFr: (string) $bean->name_fr,
            descriptionEn: $bean->description_en ? (string) $bean->description_en : null,
            descriptionFr: $bean->description_fr ? (string) $bean->description_fr : null,
            createdAt: (string) ($bean->created_at ?? '')
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