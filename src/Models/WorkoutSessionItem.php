<?php

namespace App\Models;

class WorkoutSessionItem
{
    public function __construct(
        public int $id,
        public int $sessionId,
        public int $exerciseId,
        public int $setNumber,
        public int $reps,
        public float $weight = 0.0,
        public ?int $restSeconds = null,
        public bool $completed = true,
        public ?Exercise $exercise = null
    ) {}

    /**
     * Map a RedBeanPHP bean to a typed WorkoutSessionItem object.
     */
    public static function fromBean(object $bean): self
    {
        return new self(
            id: (int) $bean->id,
            sessionId: (int) $bean->session_id,
            exerciseId: (int) $bean->exercise_id,
            setNumber: (int) $bean->set_number,
            reps: (int) $bean->reps,
            weight: (float) ($bean->weight ?? 0.0),
            restSeconds: $bean->rest_seconds ? (int) $bean->rest_seconds : null,
            completed: (bool) ($bean->completed ?? true),
            exercise: $bean->ownExercise ? Exercise::fromBean($bean->ownExercise) : null
        );
    }

    /**
     * Get formatted weight with unit
     */
    public function getFormattedWeight(string $unit = 'kg'): string
    {
        if ($this->weight == 0) {
            return 'BW'; // Bodyweight
        }

        return sprintf('%.1f %s', $this->weight, $unit);
    }

    /**
     * Calculate volume (weight × reps)
     */
    public function getVolume(): float
    {
        return $this->weight * $this->reps;
    }
}