<?php
declare(strict_types=1);

namespace MyEdSpace\Lms\Domain;

use DateTimeImmutable;

final class Course
{
    /**
     * @param string $id
     * @param string $title
     * @param DateTimeImmutable $startAt
     * @param DateTimeImmutable|null $endAt
     */
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly DateTimeImmutable $startAt,
        public readonly ?DateTimeImmutable $endAt = null
    ) {
        if ($id === '') {
            throw new \InvalidArgumentException('Course id cannot be empty.');
        }
        if ($endAt !== null && $endAt < $startAt) {
            throw new \InvalidArgumentException('Course end date must be >= start date.');
        }
    }

    /**
     * @param DateTimeImmutable $at
     * @return bool
     */
    public function hasStartedAt(DateTimeImmutable $at): bool
    {
        return $at >= $this->startAt;
    }
}
