<?php

namespace App\ValueObjects;

use Carbon\Carbon;
use DateTimeInterface;
use InvalidArgumentException;

/**
 * Value Object representing a time slot with start and end times.
 * Encapsulates all overlap detection logic.
 */
class TimeSlot
{
    public Carbon $startTime;

    public Carbon $endTime;

    public function __construct(DateTimeInterface|string $startTime, DateTimeInterface|string $endTime)
    {
        $this->startTime = Carbon::parse($startTime);
        $this->endTime = Carbon::parse($endTime);

        if ($this->endTime->lte($this->startTime)) {
            throw new InvalidArgumentException('End time must be after start time.');
        }
    }

    /**
     * Check if this time slot overlaps with another time slot.
     * Two slots overlap if one starts before the other ends AND ends after the other starts.
     */
    public function overlapsWith(self $other): bool
    {
        return $this->startTime->lt($other->endTime) && $this->endTime->gt($other->startTime);
    }

    /**
     * Check if this time slot contains another time slot entirely.
     */
    public function contains(self $other): bool
    {
        return $this->startTime->lte($other->startTime) && $this->endTime->gte($other->endTime);
    }

    /**
     * Check if this time slot is contained entirely within another time slot.
     */
    public function isContainedIn(self $other): bool
    {
        return $other->contains($this);
    }

    /**
     * Check if this time slot starts exactly when the other ends (adjacent, no gap).
     */
    public function startsWhenOtherEnds(self $other): bool
    {
        return $this->startTime->eq($other->endTime);
    }

    /**
     * Check if this time slot ends exactly when the other starts (adjacent, no gap).
     */
    public function endsWhenOtherStarts(self $other): bool
    {
        return $this->endTime->eq($other->startTime);
    }

    /**
     * Get the duration of this time slot in minutes.
     */
    public function durationInMinutes(): int
    {
        return (int) $this->startTime->diffInMinutes($this->endTime);
    }

    /**
     * Create a new TimeSlot from a model that has start_time and end_time attributes.
     *
     * @param  object  $model  Any object with start_time and end_time properties
     */
    public static function fromModel(object $model): self
    {
        return new self($model->start_time, $model->end_time);
    }

    /**
     * Format the time slot as a human-readable string.
     */
    public function format(string $dateFormat = 'M j, Y', string $timeFormat = 'g:i A'): string
    {
        $startDate = $this->startTime->format($dateFormat);
        $endDate = $this->endTime->format($dateFormat);
        $startTime = $this->startTime->format($timeFormat);
        $endTime = $this->endTime->format($timeFormat);

        if ($startDate === $endDate) {
            return "{$startDate} {$startTime} - {$endTime}";
        }

        return "{$startDate} {$startTime} - {$endDate} {$endTime}";
    }

    /**
     * Get the date of the start time.
     */
    public function date(): string
    {
        return $this->startTime->format('Y-m-d');
    }
}
