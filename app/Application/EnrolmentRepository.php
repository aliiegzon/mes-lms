<?php
declare(strict_types=1);

namespace MyEdSpace\Lms\Application;

use MyEdSpace\Lms\Domain\Enrolment;

interface EnrolmentRepository
{
    /**
     * @param string $studentId
     * @param string $courseId
     * @return Enrolment|null
     */
    public function find(string $studentId, string $courseId): ?Enrolment;

    /**
     * @param Enrolment $enrolment
     * @return void
     */
    public function save(Enrolment $enrolment): void;
}
