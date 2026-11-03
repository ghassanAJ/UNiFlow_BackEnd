<?php

namespace Tests\Feature;

use App\Enums\EnrollmentStatus;
use App\Enums\Role;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\PdfFile;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use App\Models\Video;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseStructureTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_teacher_can_own_many_courses_and_a_course_has_one_teacher(): void
    {
        $teacher = User::factory()->teacher()->create();
        $courses = Course::factory()->count(2)->for($teacher, 'teacher')->create();

        $this->assertTrue($teacher->isTeacher());
        $this->assertCount(2, $teacher->taughtCourses);
        $this->assertTrue($courses[0]->teacher->is($teacher));
        $this->assertTrue($courses[1]->teacher->is($teacher));
    }

    public function test_a_course_has_chapters_with_videos_pdfs_and_one_quiz(): void
    {
        $chapter = Chapter::factory()->create(['title' => 'Intro']);
        Video::factory()->count(2)->for($chapter)->create();
        PdfFile::factory()->count(2)->for($chapter)->create();
        $quiz = Quiz::factory()->for($chapter)->create(['title' => 'Chapter quiz']);
        Question::factory()->count(3)->for($quiz)->create();

        $chapter->refresh();

        $this->assertCount(2, $chapter->videos);
        $this->assertCount(2, $chapter->pdfFiles);
        $this->assertTrue($chapter->quiz->is($quiz));
        $this->assertCount(3, $chapter->quiz->questions);
        $this->assertTrue($chapter->course->chapters->contains($chapter));
    }

    public function test_a_chapter_cannot_have_two_quizzes(): void
    {
        $chapter = Chapter::factory()->create();
        Quiz::factory()->for($chapter)->create();

        $this->expectException(UniqueConstraintViolationException::class);

        Quiz::factory()->for($chapter)->create();
    }

    public function test_a_student_enrolls_in_a_course_once(): void
    {
        $student = User::factory()->student()->create();
        $course = Course::factory()->create();

        $enrollment = Enrollment::factory()
            ->for($student, 'student')
            ->for($course)
            ->approved()
            ->create();

        $this->assertSame(Role::Student, $student->role);
        $this->assertSame(EnrollmentStatus::Approved, $enrollment->status);
        $this->assertTrue($student->enrolledCourses->contains($course));
        $this->assertTrue($course->students->contains($student));

        $this->expectException(UniqueConstraintViolationException::class);

        Enrollment::factory()
            ->for($student, 'student')
            ->for($course)
            ->create();
    }
}
