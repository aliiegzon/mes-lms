<?php
declare(strict_types=1);

namespace MyEdSpace\Lms\Tests;

use DateTimeImmutable;
use Exception;
use MyEdSpace\Lms\Application\AccessControl;
use MyEdSpace\Lms\Application\InMemoryEnrolmentRepository;
use MyEdSpace\Lms\Domain\Content\Content;
use MyEdSpace\Lms\Domain\Content\Homework;
use MyEdSpace\Lms\Domain\Content\Lesson;
use MyEdSpace\Lms\Domain\Content\PrepMaterial;
use MyEdSpace\Lms\Domain\Course;
use MyEdSpace\Lms\Domain\Enrolment;
use MyEdSpace\Lms\Domain\Period;
use MyEdSpace\Lms\Domain\Student;
use MyEdSpace\Lms\Enums\AccessDecisionReasonEnum;
use MyEdSpace\Lms\Helpers\Date;
use PHPUnit\Framework\TestCase;

final class AccessControlTest extends TestCase
{
    /**
     * @return void
     * @throws Exception
     */
    public function test_scenario_a_level_biology_emma(): void
    {
        $course = new Course(
            id: 'C1',
            title: 'A-Level Biology',
            startAt: Date::startOfDay('2025-05-13'),
            endAt: Date::endOfDay('2025-06-12'),
        );

        $lesson = new Lesson('L1', $course->id, 'Cell Structure', Date::at('2025-05-15 10:00:00'));
        $homework = new Homework('H1', $course->id, 'Label a Plant Cell');
        $prep = new PrepMaterial('P1', $course->id, 'Biology Reading Guide');
        $student = new Student('S1', 'Emma');
        $enrolment = $this->enrolment($student, $course, '2025-05-01', '2025-05-30');

        $repo = $this->repoWith($enrolment);
        $access = new AccessControl($repo);

        $decision = $access->decide($student, $course, $prep, Date::startOfDay('2025-05-01'));
        $this->assertFalse($decision->allowed);
        $this->assertSame(AccessDecisionReasonEnum::REASON_COURSE_NOT_STARTED, $decision->reason);

        $this->assertTrue($access->canAccess($student, $course, $prep, Date::startOfDay('2025-05-13')));
        $this->assertTrue($access->canAccess($student, $course, $lesson, Date::at('2025-05-15 10:01:00')));

        $enrolment->shortenEndDate(Date::endOfDay('2025-05-20'));
        $repo->save($enrolment);

        $decision = $access->decide($student, $course, $homework, Date::startOfDay('2025-05-21'));
        $this->assertFalse($decision->allowed);
        $this->assertSame(AccessDecisionReasonEnum::REASON_ENROLMENT_INACTIVE, $decision->reason);

        $this->assertFalse($access->canAccess($student, $course, $homework, Date::startOfDay('2025-05-30')));
        $this->assertFalse($access->canAccess($student, $course, $prep, Date::startOfDay('2025-06-10')));
    }

    /**
     * @return void
     * @throws Exception
     */
    public function test_denies_lesson_before_scheduled_time(): void
    {
        $course = new Course('C1', 'Physics', Date::startOfDay('2025-05-13'));
        $lesson = new Lesson('L1', $course->id, 'Gravity', Date::at('2025-05-15 10:00:00'));
        $student = new Student('S1', 'Egzon');
        $enrolment = $this->enrolment($student, $course, '2025-05-01', '2025-05-30');

        $access = new AccessControl($this->repoWith($enrolment));
        $decision = $access->decide($student, $course, $lesson, Date::at('2025-05-15 09:59:00'));

        $this->assertFalse($decision->allowed);
        $this->assertSame(AccessDecisionReasonEnum::REASON_CONTENT_NOT_YET_AVAILABLE, $decision->reason);
    }

    /**
     * @param Course $course
     * @param Content $content
     * @param Student $student
     * @param InMemoryEnrolmentRepository $repo
     * @param DateTimeImmutable $at
     * @param AccessDecisionReasonEnum $expectedReason
     * @return void
     *
     * @dataProvider denialCases
     */
    public function test_denial_scenarios(
        Course $course,
        Content $content,
        Student $student,
        InMemoryEnrolmentRepository $repo,
        DateTimeImmutable $at,
        AccessDecisionReasonEnum $expectedReason
    ): void {
        $access = new AccessControl($repo);
        $decision = $access->decide($student, $course, $content, $at);

        $this->assertFalse($decision->allowed);
        $this->assertSame($expectedReason, $decision->reason);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function test_allows_open_ended_enrolment_after_course_start(): void
    {
        $course = new Course('C1', 'Maths', Date::startOfDay('2025-05-10'));
        $student = new Student('S1', 'Egzon');
        $prep = new PrepMaterial('P1', $course->id, 'Prep Algebra');
        $enrolment = $this->enrolment($student, $course, '2025-05-01');

        $access = new AccessControl($this->repoWith($enrolment));

        $this->assertTrue($access->canAccess($student, $course, $prep, Date::startOfDay('2025-05-20')));
    }

    /**
     * @return iterable
     * @throws Exception
     */
    public static function denialCases(): iterable
    {
        yield 'content course mismatch' => self::denialCaseContentMismatch();
        yield 'not enrolled' => self::denialCaseNotEnrolled();
        yield 'course not started' => self::denialCaseCourseNotStarted();
        yield 'homework before course start' => self::denialCaseHomeworkBeforeCourseStart();
    }

    /**
     * @return array
     * @throws Exception
     */
    private static function denialCaseContentMismatch(): array
    {
        $course = new Course('C1', 'Maths', Date::startOfDay('2025-05-10'));
        $otherCourse = new Course('C2', 'Geography', Date::startOfDay('2025-05-10'));
        $student = new Student('S1', 'Egzon');
        $enrolment = new Enrolment(
            studentId: $student->id,
            courseId: $course->id,
            period: new Period(Date::startOfDay('2025-05-01'), Date::endOfDay('2025-05-31'))
        );

        $repo = new InMemoryEnrolmentRepository();
        $repo->save($enrolment);

        return [
            $course,
            new Homework('H1', $otherCourse->id, 'Solve x in this math equation'),
            $student,
            $repo,
            Date::startOfDay('2025-05-15'),
            AccessDecisionReasonEnum::REASON_CONTENT_COURSE_MISMATCH,
        ];
    }

    /**
     * @return array
     * @throws Exception
     */
    private static function denialCaseNotEnrolled(): array
    {
        $course = new Course('C1', 'Chemistry', Date::startOfDay('2025-05-10'));
        $student = new Student('S1', 'Egzon');
        $homework = new Homework('H1', $course->id, 'List 5 chemical elements');

        return [
            $course,
            $homework,
            $student,
            new InMemoryEnrolmentRepository(),
            Date::startOfDay('2025-05-15'),
            AccessDecisionReasonEnum::REASON_NOT_ENROLLED,
        ];
    }

    /**
     * @return array
     * @throws Exception
     */
    private static function denialCaseCourseNotStarted(): array
    {
        $course = new Course('C1', 'English', Date::startOfDay('2025-06-01'));
        $student = new Student('S1', 'Egzon');
        $homework = new Homework('H1', $course->id, 'Read The Stranger by Albert Camus');
        $enrolment = new Enrolment(
            studentId: $student->id,
            courseId: $course->id,
            period: new Period(Date::startOfDay('2025-05-01'), Date::endOfDay('2025-06-10'))
        );

        $repo = new InMemoryEnrolmentRepository();
        $repo->save($enrolment);

        return [
            $course,
            $homework,
            $student,
            $repo,
            Date::startOfDay('2025-05-20'),
            AccessDecisionReasonEnum::REASON_COURSE_NOT_STARTED,
        ];
    }

    /**
     * @return array
     * @throws Exception
     */
    private static function denialCaseHomeworkBeforeCourseStart(): array
    {
        $course = new Course('C1', 'English', Date::startOfDay('2025-06-01'));
        $student = new Student('S1', 'Egzon');
        $homework = new Homework('H1', $course->id, 'Write an essay');
        $enrolment = new Enrolment(
            studentId: $student->id,
            courseId: $course->id,
            period: new Period(Date::startOfDay('2025-05-01'), Date::endOfDay('2025-06-10'))
        );

        $repo = new InMemoryEnrolmentRepository();
        $repo->save($enrolment);

        return [
            $course,
            $homework,
            $student,
            $repo,
            Date::startOfDay('2025-05-25'),
            AccessDecisionReasonEnum::REASON_COURSE_NOT_STARTED,
        ];
    }

    /**
     * @param Enrolment ...$enrolments
     * @return InMemoryEnrolmentRepository
     */
    private function repoWith(Enrolment ...$enrolments): InMemoryEnrolmentRepository
    {
        $repo = new InMemoryEnrolmentRepository();
        foreach ($enrolments as $enrolment) {
            $repo->save($enrolment);
        }

        return $repo;
    }

    /**
     * @param Student $student
     * @param Course $course
     * @param string $startDate
     * @param string|null $endDate
     * @return Enrolment
     * @throws Exception
     */
    private function enrolment(Student $student, Course $course, string $startDate, ?string $endDate = null): Enrolment
    {
        return new Enrolment(
            studentId: $student->id,
            courseId: $course->id,
            period: new Period(
                startAt: Date::startOfDay($startDate),
                endAt: $endDate === null ? null : Date::endOfDay($endDate)
            )
        );
    }
}
