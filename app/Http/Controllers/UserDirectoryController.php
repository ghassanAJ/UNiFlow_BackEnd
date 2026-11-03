<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserDirectoryController extends Controller
{
    public function students(): JsonResponse
    {
        return response()->json([
            'students' => $this->usersWithRole(Role::Student),
        ]);
    }

    public function teachers(): JsonResponse
    {
        return response()->json([
            'teachers' => $this->usersWithRole(Role::Teacher),
        ]);
    }

    /**
     * @return list<array{id: int, name: string, university_id: string, email: string, role: string}>
     */
    private function usersWithRole(Role $role): array
    {
        return User::query()
            ->where('role', $role)
            ->orderBy('university_id')
            ->get()
            ->map(fn (User $user) => $user->toAuthArray())
            ->all();
    }
}
