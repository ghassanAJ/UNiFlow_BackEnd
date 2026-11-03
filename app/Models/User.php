<?php

namespace App\Models;

use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'university_id',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'role' => Role::class,
        ];
    }

    public function taughtCourses(): HasMany
    {
        return $this->hasMany(Course::class, 'teacher_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function enrolledCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'enrollments')
            ->withPivot('status')
            ->withTimestamps();
    }

    public function isAdmin(): bool
    {
        return $this->role === Role::Admin;
    }

    public function isTeacher(): bool
    {
        return $this->role === Role::Teacher;
    }

    public function isStudent(): bool
    {
        return $this->role === Role::Student;
    }

    public function createApiToken(): string
    {
        $minutes = (int) config('sanctum.expiration');

        return $this->createToken(
            'api',
            ['*'],
            $minutes > 0 ? now()->addMinutes($minutes) : null,
        )->plainTextToken;
    }

    protected function password(): Attribute
    {
        return Attribute::make(
            set: function (string $value): string {
                if (! Hash::isHashed($value)) {
                    $this->ensurePasswordIsUnique($value);
                    $value = Hash::make($value);
                }

                return $value;
            },
        );
    }

    private function ensurePasswordIsUnique(string $plain): void
    {
        $others = static::query()
            ->when($this->exists, fn ($query) => $query->whereKeyNot($this->getKey()))
            ->get(['password']);

        foreach ($others as $other) {
            if (Hash::check($plain, $other->password)) {
                throw ValidationException::withMessages([
                    'password' => 'كلمة المرور مستخدمة بحساب ثاني.',
                ]);
            }
        }
    }

    /**
     * @return array{id: int, name: string, university_id: string, email: string, role: string}
     */
    public function toAuthArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'university_id' => $this->university_id,
            'email' => $this->email,
            'role' => $this->role->value,
        ];
    }

}
