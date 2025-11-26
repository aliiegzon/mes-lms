<?php
declare(strict_types=1);

namespace MyEdSpace\Lms\Domain\Content;

use DateTimeImmutable;
use MyEdSpace\Lms\Domain\Course;

final class Lesson implements Content
{
    /**
     * @param string $id
     * @param string $courseId
     * @param string $title
     * @param DateTimeImmutable $scheduledAt
     */
    public function __construct(
        private readonly string $id,
        private readonly string $courseId,
        private readonly string $title,
        private readonly DateTimeImmutable $scheduledAt
    ) {
        if ($id === '' || $courseId === '') {
            throw new \InvalidArgumentException('Lesson id and Course id cannot be empty.');
        }
    }

    /**
     * @return string
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function courseId(): string
    {
        return $this->courseId;
    }

    /**
     * @param Course $course
     * @return DateTimeImmutable
     */
    public function availableFrom(Course $course): DateTimeImmutable
    {
        return $this->scheduledAt;
    }
}
