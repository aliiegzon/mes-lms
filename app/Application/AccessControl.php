<?php
declare(strict_types=1);

namespace MyEdSpace\Lms\Application;

use DateTimeImmutable;
use MyEdSpace\Lms\Domain\Course;
use MyEdSpace\Lms\Domain\Student;
use MyEdSpace\Lms\Domain\Content\Content;
use MyEdSpace\Lms\Enums\AccessDecisionReasonEnum;

final class AccessControl
{
    /**
     * @param EnrolmentRepository $enrolments
     */
    public function __construct(private readonly EnrolmentRepository $enrolments) {}

    /**
     * @param Student $student
     * @param Course $course
     * @param Content $content
     * @param DateTimeImmutable $at
     * @return AccessDecision
     */
    public function decide(Student $student, Course $course, Content $content, DateTimeImmutable $at): AccessDecision
    {
        // Ensure we are checking the right course/content combination
        if ($content->courseId() !== $course->id) {
            return AccessDecision::deny(AccessDecisionReasonEnum::REASON_CONTENT_COURSE_MISMATCH);
        }

        $enrolment = $this->enrolments->find($student->id, $course->id);

        // It needs to pass all these rules in order for access to be allowed! Rules follow as such in the next comments:
        // Must be currently enrolled
        if ($enrolment === null) {
            return AccessDecision::deny(AccessDecisionReasonEnum::REASON_NOT_ENROLLED);
        }
        // Enrolment must be active
        if (!$enrolment->isActiveAt($at)) {
            return AccessDecision::deny(AccessDecisionReasonEnum::REASON_ENROLMENT_INACTIVE);
        }

        // Course must have started
        if (!$course->hasStartedAt($at)) {
            return AccessDecision::deny(AccessDecisionReasonEnum::REASON_COURSE_NOT_STARTED);
        }

        // Content must be available
        $availableFrom = $content->availableFrom($course);
        if ($at < $availableFrom) {
            return AccessDecision::deny(AccessDecisionReasonEnum::REASON_CONTENT_NOT_YET_AVAILABLE);
        }

        return AccessDecision::allow();
    }

    /**
     * @param Student $student
     * @param Course $course
     * @param Content $content
     * @param DateTimeImmutable $at
     * @return bool
     */
    public function canAccess(Student $student, Course $course, Content $content, DateTimeImmutable $at): bool
    {
        return $this->decide($student, $course, $content, $at)->allowed;
    }
}
