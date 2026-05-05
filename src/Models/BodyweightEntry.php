<?php

namespace App\Models;

class BodyweightEntry
{
    public const UNIT_KG = 'kg';
    public const UNIT_LBS = 'lbs';

    public function __construct(
        public int $id,
        public int $userId,
        public float $weight,
        public string $unit = self::UNIT_KG,
        public string $recordedAt = ''
    ) {}

    /**
     * Map a RedBeanPHP bean to a typed BodyweightEntry object.
     */
    public static function fromBean(object $bean): self
    {
        return new self(
            id: (int) $bean->id,
            userId: (int) $bean->user_id,
            weight: (float) $bean->weight,
            unit: (string) ($bean->unit ?? self::UNIT_KG),
            recordedAt: (string) ($bean->recorded_at ?? '')
        );
    }

    /**
     * Get formatted weight with unit
     */
    public function getFormattedWeight(): string
    {
        return sprintf('%.1f %s', $this->weight, $this->unit);
    }

    /**
     * Get weight in kilograms
     */
    public function getWeightInKg(): float
    {
        if ($this->unit === self::UNIT_LBS) {
            return $this->weight * 0.453592;
        }
        return $this->weight;
    }

    /**
     * Get recorded date formatted
     */
    public function getRecordedDate(): string
    {
        return date('Y-m-d', strtotime($this->recordedAt));
    }

    /**
     * Check if unit is kilograms
     */
    public function isKg(): bool
    {
        return $this->unit === self::UNIT_KG;
    }

    /**
     * Check if unit is pounds
     */
    public function isLbs(): bool
    {
        return $this->unit === self::UNIT_LBS;
    }
}