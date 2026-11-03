<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('university_id')->nullable()->unique()->after('name');
        });

        $known = [
            'admin@uniflow.test' => '1001',
            'teacher@uniflow.test' => '2001',
            'student@uniflow.test' => '3001',
        ];

        foreach ($known as $email => $universityId) {
            DB::table('users')->where('email', $email)->update([
                'university_id' => $universityId,
            ]);
        }

        foreach (DB::table('users')->whereNull('university_id')->orderBy('id')->get() as $user) {
            DB::table('users')->where('id', $user->id)->update([
                'university_id' => '9'.str_pad((string) $user->id, 5, '0', STR_PAD_LEFT),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['university_id']);
            $table->dropColumn('university_id');
        });
    }
};
