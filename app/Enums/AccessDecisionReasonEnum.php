<?php
declare(strict_types=1);

namespace MyEdSpace\Lms\Enums;

enum AccessDecisionReasonEnum: string
{
    case REASON_CONTENT_COURSE_MISMATCH = 'Content course mismatch';
    case REASON_NOT_ENROLLED = 'Not enrolled';
    case REASON_ENROLMENT_INACTIVE = 'Enrolment inactive';
    case REASON_COURSE_NOT_STARTED = 'Course not started';
    case REASON_CONTENT_NOT_YET_AVAILABLE = 'Content not yet available';
}
