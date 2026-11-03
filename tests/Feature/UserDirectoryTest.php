<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_admin_lists_students_and_teachers_without_passwords(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'admin123']);
        User::factory()->student()->create(['university_id' => '3001', 'password' => 'student123']);
        User::factory()->student()->create(['university_id' => '3002', 'password' => 'student3002']);
        User::factory()->teacher()->create(['university_id' => '2001', 'password' => 'teacher123']);
        User::factory()->teacher()->create(['university_id' => '2002', 'password' => 'teacher2002']);

        $token = $this->postJson('/api/login', [
            'university_id' => $admin->university_id,
            'password' => 'admin123',
        ])->json('token');

        $this->withToken($token)->getJson('/api/students')
            ->assertOk()
            ->assertJsonCount(2, 'students')
            ->assertJsonPath('students.0.university_id', '3001')
            ->assertJsonPath('students.0.role', 'student')
            ->assertJsonPath('students.1.university_id', '3002')
            ->assertJsonMissingPath('students.0.password');

        $this->withToken($token)->getJson('/api/teachers')
            ->assertOk()
            ->assertJsonCount(2, 'teachers')
            ->assertJsonPath('teachers.0.university_id', '2001')
            ->assertJsonPath('teachers.0.role', 'teacher')
            ->assertJsonMissingPath('teachers.0.password');
    }

    public function test_other_roles_cannot_list_students_or_teachers(): void
    {
        $teacher = User::factory()->teacher()->create(['password' => 'teacher123']);
        $student = User::factory()->student()->create(['password' => 'student123']);

        $teacherToken = $this->postJson('/api/login', [
            'university_id' => $teacher->university_id,
            'password' => 'teacher123',
        ])->json('token');

        $this->withToken($teacherToken)->getJson('/api/students')->assertForbidden();
        $this->withToken($teacherToken)->getJson('/api/teachers')->assertForbidden();

        $this->app['auth']->forgetGuards();

        $studentToken = $this->postJson('/api/login', [
            'university_id' => $student->university_id,
            'password' => 'student123',
        ])->json('token');

        $this->withToken($studentToken)->getJson('/api/students')->assertForbidden();
        $this->withToken($studentToken)->getJson('/api/teachers')->assertForbidden();
    }

    public function test_a_guest_cannot_list_students_or_teachers(): void
    {
        $this->getJson('/api/students')->assertUnauthorized();
        $this->getJson('/api/teachers')->assertUnauthorized();
    }
}
