<?php

namespace App\Models;

class WorkoutSession
{
    public function __construct(
        public int $id,
        public int $userId,
        public ?int $programWorkoutId,
        public string $startedAt,
        public ?string $completedAt = null,
        public ?int $durationSeconds = null,
        public ?string $notes = null,
        public ?array $items = null
    ) {}

    /**
     * Map a RedBeanPHP bean to a typed WorkoutSession object.
     */
    public static function fromBean(object $bean): self
    {
        return new self(
            id: (int) $bean->id,
            userId: (int) $bean->user_id,
            programWorkoutId: $bean->program_workout_id ? (int) $bean->program_workout_id : null,
            startedAt: (string) $bean->started_at,
            completedAt: $bean->completed_at ? (string) $bean->completed_at : null,
            durationSeconds: $bean->duration_seconds ? (int) $bean->duration_seconds : null,
            notes: $bean->notes ? (string) $bean->notes : null,
            items: null
        );
    }

    /**
     * Check if session is completed
     */
    public function isCompleted(): bool
    {
        return $this->completedAt !== null;
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDuration(): string
    {
        if ($this->durationSeconds === null) {
            return '--:--';
        }

        $hours = floor($this->durationSeconds / 3600);
        $minutes = floor(($this->durationSeconds % 3600) / 60);
        $seconds = $this->durationSeconds % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    /**
     * Get start date formatted
     */
    public function getStartDate(): string
    {
        return date('Y-m-d', strtotime($this->startedAt));
    }

    /**
     * Get start time formatted
     */
    public function getStartTime(): string
    {
        return date('H:i', strtotime($this->startedAt));
    }
}