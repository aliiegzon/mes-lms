<?php
declare(strict_types=1);

namespace MyEdSpace\Lms\Domain;

use DateTimeImmutable;

final class Enrolment
{
    /**
     * @param string $studentId
     * @param string $courseId
     * @param Period $period
     */
    public function __construct(
        public readonly string $studentId,
        public readonly string $courseId,
        private Period $period
    ) {
        if ($studentId === '' || $courseId === '') {
            throw new \InvalidArgumentException('Student id and Course id cannot be empty.');
        }
    }

    /**
     * @param DateTimeImmutable $at
     * @return bool
     */
    public function isActiveAt(DateTimeImmutable $at): bool
    {
        return $this->period->contains($at);
    }

    /**
     * @param DateTimeImmutable $newEndAt
     * @return void
     */
    public function shortenEndDate(DateTimeImmutable $newEndAt): void
    {
        $this->period = $this->period->withEndAt($newEndAt);
    }
}
