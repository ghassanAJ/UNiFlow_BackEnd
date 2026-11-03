<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_invalid_credentials_do_not_return_a_token(): void
    {
        User::factory()->student()->create([
            'university_id' => '3001',
            'email' => 'student@uniflow.test',
            'password' => 'student123',
        ]);

        $this->postJson('/api/login', [
            'university_id' => '3001',
            'password' => 'wrong-password',
        ])->assertUnprocessable()->assertJsonValidationErrors('university_id');

        $this->postJson('/api/login', [
            'email' => 'student@uniflow.test',
            'password' => 'student123',
        ])->assertUnprocessable()->assertJsonValidationErrors('university_id');
    }

    public function test_a_request_without_a_token_is_unauthorized(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
        $this->putJson('/api/password', [
            'current_password' => 'student123',
            'password' => 'student3001a',
            'password_confirmation' => 'student3001a',
        ])->assertUnauthorized();
        $this->getJson('/api/admin')->assertUnauthorized();
        $this->getJson('/api/teacher')->assertUnauthorized();
        $this->getJson('/api/student')->assertUnauthorized();
    }

    public function test_admin_can_open_only_the_admin_area(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'admin123']);
        $token = $this->tokenFor($admin, 'admin123');

        $this->withToken($token)->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('user.role', 'admin')
            ->assertJsonPath('user.university_id', $admin->university_id)
            ->assertJsonPath('user.email', $admin->email)
            ->assertJsonMissingPath('permissions');

        $this->withToken($token)->getJson('/api/admin')
            ->assertOk()
            ->assertJsonPath('role', 'admin')
            ->assertJsonMissingPath('permissions');

        $this->withToken($token)->getJson('/api/teacher')
            ->assertForbidden()
            ->assertJsonPath('message', 'ليس لديك صلاحية للوصول إلى هذا الطلب.');
        $this->withToken($token)->getJson('/api/student')->assertForbidden();
    }

    public function test_teacher_can_open_only_the_teacher_area(): void
    {
        $teacher = User::factory()->teacher()->create(['password' => 'teacher123']);
        $token = $this->tokenFor($teacher, 'teacher123');

        $this->withToken($token)->getJson('/api/teacher')
            ->assertOk()
            ->assertJsonPath('role', 'teacher')
            ->assertJsonMissingPath('permissions');

        $this->withToken($token)->getJson('/api/admin')->assertForbidden();
        $this->withToken($token)->getJson('/api/student')->assertForbidden();
    }

    public function test_student_can_open_only_the_student_area(): void
    {
        $student = User::factory()->student()->create(['password' => 'student123']);
        $token = $this->tokenFor($student, 'student123');

        $this->withToken($token)->getJson('/api/student')
            ->assertOk()
            ->assertJsonPath('role', 'student')
            ->assertJsonMissingPath('permissions');

        $this->withToken($token)->getJson('/api/admin')->assertForbidden();
        $this->withToken($token)->getJson('/api/teacher')->assertForbidden();
    }

    public function test_logout_revokes_every_device_token(): void
    {
        $student = User::factory()->student()->create(['password' => 'student123']);
        $token = $this->tokenFor($student, 'student123');
        $otherDevice = $this->tokenFor($student, 'student123');

        $this->assertDatabaseCount('personal_access_tokens', 2);

        $this->withToken($token)->postJson('/api/logout')
            ->assertOk()
            ->assertJsonPath('message', 'تم تسجيل الخروج.');

        $this->assertDatabaseCount('personal_access_tokens', 0);

        // The test process keeps the authenticated user until the guards are cleared.
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/me')->assertUnauthorized();

        $this->app['auth']->forgetGuards();
        $this->withToken($otherDevice)->getJson('/api/me')->assertUnauthorized();
    }

    public function test_an_expired_token_is_rejected(): void
    {
        $student = User::factory()->student()->create(['password' => 'student123']);
        $token = $this->tokenFor($student, 'student123');

        $this->assertTrue($student->tokens()->first()->expires_at->greaterThan(now()->addHours(23)));

        $this->travel(1441)->minutes();
        $this->app['auth']->forgetGuards();

        $this->withToken($token)->getJson('/api/me')->assertUnauthorized();
    }

    public function test_a_user_can_change_password_and_other_tokens_stop_working(): void
    {
        $student = User::factory()->student()->create([
            'university_id' => '3001',
            'password' => 'student123',
        ]);
        $token = $this->tokenFor($student, 'student123');
        $otherDevice = $this->tokenFor($student, 'student123');

        $this->withToken($token)->putJson('/api/password', [
            'current_password' => 'wrong-password',
            'password' => 'student3001a',
            'password_confirmation' => 'student3001a',
        ])->assertUnprocessable()->assertJsonValidationErrors('current_password');

        $this->withToken($token)->putJson('/api/password', [
            'current_password' => 'student123',
            'password' => 'student123',
            'password_confirmation' => 'student123',
        ])->assertUnprocessable()->assertJsonValidationErrors('password');

        $this->withToken($token)->putJson('/api/password', [
            'current_password' => 'student123',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])->assertUnprocessable()->assertJsonValidationErrors('password');

        $response = $this->withToken($token)->putJson('/api/password', [
            'current_password' => 'student123',
            'password' => 'student3001a',
            'password_confirmation' => 'student3001a',
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'تم تغيير كلمة المرور.')
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.university_id', '3001');

        $newToken = $response->json('token');
        $this->assertNotSame($token, $newToken);
        $this->assertDatabaseCount('personal_access_tokens', 1);

        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/me')->assertUnauthorized();

        $this->app['auth']->forgetGuards();
        $this->withToken($otherDevice)->getJson('/api/me')->assertUnauthorized();

        $this->app['auth']->forgetGuards();
        $this->withToken($newToken)->getJson('/api/me')->assertOk();

        $this->postJson('/api/login', [
            'university_id' => '3001',
            'password' => 'student123',
        ])->assertUnprocessable();

        $this->postJson('/api/login', [
            'university_id' => '3001',
            'password' => 'student3001a',
        ])->assertOk();
    }

    public function test_a_new_password_cannot_match_another_account(): void
    {
        User::factory()->teacher()->create(['password' => 'teacher123']);
        $student = User::factory()->student()->create(['password' => 'student123']);
        $token = $this->tokenFor($student, 'student123');

        $this->withToken($token)->putJson('/api/password', [
            'current_password' => 'student123',
            'password' => 'teacher123',
            'password_confirmation' => 'teacher123',
        ])->assertUnprocessable()->assertJsonValidationErrors('password');

        $this->postJson('/api/login', [
            'university_id' => $student->university_id,
            'password' => 'student123',
        ])->assertOk();
    }

    public function test_two_accounts_cannot_share_a_password(): void
    {
        User::factory()->create(['password' => 'same-password']);

        $this->expectException(ValidationException::class);

        User::factory()->create(['password' => 'same-password']);
    }

    private function tokenFor(User $user, string $password): string
    {
        $response = $this->postJson('/api/login', [
            'university_id' => $user->university_id,
            'password' => $password,
        ]);

        $response->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.role', $user->role->value)
            ->assertJsonPath('user.university_id', $user->university_id)
            ->assertJsonPath('user.email', $user->email)
            ->assertJsonStructure([
                'token',
                'token_type',
                'user' => ['id', 'name', 'university_id', 'email', 'role'],
            ]);

        return $response->json('token');
    }
}
