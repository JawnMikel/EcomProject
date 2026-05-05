<?php

namespace App\Models;

class PersonalRecord
{
    public function __construct(
        public int $id,
        public int $userId,
        public int $exerciseId,
        public float $weight,
        public int $reps,
        public string $achievedAt = '',
        public ?Exercise $exercise = null
    ) {}

    /**
     * Map a RedBeanPHP bean to a typed PersonalRecord object.
     */
    public static function fromBean(object $bean): self
    {
        return new self(
            id: (int) $bean->id,
            userId: (int) $bean->user_id,
            exerciseId: (int) $bean->exercise_id,
            weight: (float) $bean->weight,
            reps: (int) $bean->reps,
            achievedAt: (string) ($bean->achieved_at ?? ''),
            exercise: $bean->ownExercise ? Exercise::fromBean($bean->ownExercise) : null
        );
    }

    /**
     * Get formatted weight with unit
     */
    public function getFormattedWeight(string $unit = 'kg'): string
    {
        return sprintf('%.1f %s', $this->weight, $unit);
    }

    /**
     * Get achieved date formatted
     */
    public function getAchievedDate(): string
    {
        return date('Y-m-d', strtotime($this->achievedAt));
    }

    /**
     * Get formatted record display
     */
    public function getRecordDisplay(string $unit = 'kg'): string
    {
        if ($this->weight == 0) {
            return sprintf('%d reps', $this->reps);
        }
        return sprintf('%.1f %s × %d', $this->weight, $unit, $this->reps);
    }
}