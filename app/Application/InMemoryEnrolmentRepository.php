<?php
declare(strict_types=1);

namespace MyEdSpace\Lms\Application;

use MyEdSpace\Lms\Domain\Enrolment;

final class InMemoryEnrolmentRepository implements EnrolmentRepository
{
    /** @var array<string, Enrolment> */
    private array $items = [];

    /**
     * @param string $studentId
     * @param string $courseId
     * @return Enrolment|null
     */
    public function find(string $studentId, string $courseId): ?Enrolment
    {
        return $this->items[$this->key($studentId, $courseId)] ?? null;
    }

    /**
     * @param Enrolment $enrolment
     * @return void
     */
    public function save(Enrolment $enrolment): void
    {
        $this->items[$this->key($enrolment->studentId, $enrolment->courseId)] = $enrolment;
    }

    /**
     * @param string $studentId
     * @param string $courseId
     * @return string
     */
    private function key(string $studentId, string $courseId): string
    {
        return $studentId . '::' . $courseId;
    }
}
