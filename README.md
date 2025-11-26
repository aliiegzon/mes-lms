# MyEdSpace LMS

## Notes
- No frameworks, no database.
- OO domain model with a tiny application service `AccessControl`.
- In-memory repository simulates enrolment lookup/update.

## Domain concepts
- **Course**: start date, optional end date.
- **Content**:
    - Lesson: available from its scheduled datetime.
    - Homework: available from course start.
    - Prep Material: available from course start.
- **Student**
- **Enrolment**: links student ↔ course with a start/end period; the end date can be shortened by external systems. `Period` answers whether a date/time is inside the active window.

## Access rules
A student can access content only if all are true:
1) The student is enrolled and the enrolment is active at the time.
2) The course has started.
3) The content is available.

Failure reasons are exposed via `AccessDecision`:
- `Content course mismatch`
- `Not enrolled`
- `Enrolment inactive`
- `Course not started`
- `Content not yet available`

## Tests and scenarios covered
`tests/AccessControlTest.php` covers:
- The full “A-Level Biology / Emma” timeline with an enrolment end-date shortened mid-course.
- Lessons denied before their scheduled time.
- Data-provided denial cases:
    - Content/course mismatch.
    - Not enrolled.
    - Course not started.
    - Homework before course start.
- Open-ended enrolment allowed after course start.

## Project layout
- `src/Domain`: entities/value objects (Course, Student, Enrolment, Period) and content types.
- `src/Application`: access control service, decision value object, enrolment repository abstraction + in-memory implementation.
- `src/Helpers`: date helper.
- `tests`: PHPUnit coverage of scenarios.

## Run it locally
```
composer install
composer test
```
