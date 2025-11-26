<?php
declare(strict_types=1);

namespace MyEdSpace\Lms\Domain;

use DateTimeImmutable;

final class Period
{
    /**
     * @param DateTimeImmutable $startAt
     * @param DateTimeImmutable|null $endAt
     */
    public function __construct(
        public readonly DateTimeImmutable $startAt,
        public readonly ?DateTimeImmutable $endAt = null
    ) {
        if ($endAt !== null && $endAt < $startAt) {
            throw new \InvalidArgumentException('Period end date must be >= start date.');
        }
    }

    /**
     * @param DateTimeImmutable $at
     * @return bool
     */
    public function contains(DateTimeImmutable $at): bool
    {
        if ($at < $this->startAt) {
            return false;
        }

        if ($this->endAt === null) {
            return true;
        }

        return $at <= $this->endAt;
    }

    /**
     * @param DateTimeImmutable $newEndAt
     * @return self
     */
    public function withEndAt(DateTimeImmutable $newEndAt): self
    {
        return new self($this->startAt, $newEndAt);
    }
}
