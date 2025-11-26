<?php
declare(strict_types=1);

namespace MyEdSpace\Lms\Domain\Content;

use DateTimeImmutable;
use MyEdSpace\Lms\Domain\Course;

interface Content
{
    /**
     * @return string
     */
    public function id(): string;

    /**
     * @return string
     */
    public function title(): string;

    /**
     * @return string
     */
    public function courseId(): string;

    /**
     * When does this content become available?
     * Lessons: scheduled datetime
     * Homework/PrepMaterial: from course start
     *
     * @param Course $course
     * @return DateTimeImmutable
     */
    public function availableFrom(Course $course): DateTimeImmutable;
}
