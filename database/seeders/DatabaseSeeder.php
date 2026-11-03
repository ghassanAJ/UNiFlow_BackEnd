<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->accounts() as $account) {
            User::query()->updateOrCreate(
                ['university_id' => $account['university_id']],
                [
                    'name' => $account['name'],
                    'email' => $account['email'],
                    'password' => $account['password'],
                    'role' => $account['role'],
                    'email_verified_at' => now(),
                ],
            );
        }
    }

    /**
     * @return list<array{university_id: string, name: string, email: string, password: string, role: Role}>
     */
    private function accounts(): array
    {
        return [
            ['university_id' => '1001', 'name' => 'سامي خوري', 'email' => 'admin@uniflow.test', 'password' => 'admin123', 'role' => Role::Admin],
            ['university_id' => '1002', 'name' => 'ليلى عبد النور', 'email' => 'layla.abdulnour@uniflow.test', 'password' => 'admin1002', 'role' => Role::Admin],
            ['university_id' => '1003', 'name' => 'كريم حداد', 'email' => 'karim.haddad@uniflow.test', 'password' => 'admin1003', 'role' => Role::Admin],
            ['university_id' => '1004', 'name' => 'نور الهدى صالح', 'email' => 'nour.saleh@uniflow.test', 'password' => 'admin1004', 'role' => Role::Admin],

            ['university_id' => '2001', 'name' => 'هاني منصور', 'email' => 'teacher@uniflow.test', 'password' => 'teacher123', 'role' => Role::Teacher],
            ['university_id' => '2002', 'name' => 'رنا قاسم', 'email' => 'rana.kassem@uniflow.test', 'password' => 'teacher2002', 'role' => Role::Teacher],
            ['university_id' => '2003', 'name' => 'وليد عدرة', 'email' => 'walid.adra@uniflow.test', 'password' => 'teacher2003', 'role' => Role::Teacher],
            ['university_id' => '2004', 'name' => 'ميساء بركات', 'email' => 'maisa.barakat@uniflow.test', 'password' => 'teacher2004', 'role' => Role::Teacher],
            ['university_id' => '2005', 'name' => 'طارق نجار', 'email' => 'tarek.najjar@uniflow.test', 'password' => 'teacher2005', 'role' => Role::Teacher],
            ['university_id' => '2006', 'name' => 'سمر ديب', 'email' => 'samar.deeb@uniflow.test', 'password' => 'teacher2006', 'role' => Role::Teacher],
            ['university_id' => '2007', 'name' => 'فادي كنعان', 'email' => 'fadi.kanaan@uniflow.test', 'password' => 'teacher2007', 'role' => Role::Teacher],
            ['university_id' => '2008', 'name' => 'هبة شحادة', 'email' => 'hiba.shahadeh@uniflow.test', 'password' => 'teacher2008', 'role' => Role::Teacher],
            ['university_id' => '2009', 'name' => 'مازن عيتاني', 'email' => 'mazen.aitani@uniflow.test', 'password' => 'teacher2009', 'role' => Role::Teacher],
            ['university_id' => '2010', 'name' => 'لينا فرنجية', 'email' => 'lina.frangieh@uniflow.test', 'password' => 'teacher2010', 'role' => Role::Teacher],

            ['university_id' => '3001', 'name' => 'أحمد الخطيب', 'email' => 'student@uniflow.test', 'password' => 'student123', 'role' => Role::Student],
            ['university_id' => '3002', 'name' => 'جنى سعيد', 'email' => 'jana.saeed@uniflow.test', 'password' => 'student3002', 'role' => Role::Student],
            ['university_id' => '3003', 'name' => 'يوسف مرعي', 'email' => 'youssef.merhi@uniflow.test', 'password' => 'student3003', 'role' => Role::Student],
            ['university_id' => '3004', 'name' => 'تالا خوري', 'email' => 'tala.khoury@uniflow.test', 'password' => 'student3004', 'role' => Role::Student],
            ['university_id' => '3005', 'name' => 'عمر شامية', 'email' => 'omar.shamiyeh@uniflow.test', 'password' => 'student3005', 'role' => Role::Student],
            ['university_id' => '3006', 'name' => 'ميراي نصور', 'email' => 'miray.nassour@uniflow.test', 'password' => 'student3006', 'role' => Role::Student],
            ['university_id' => '3007', 'name' => 'كرم بعلبكي', 'email' => 'karam.baalbaki@uniflow.test', 'password' => 'student3007', 'role' => Role::Student],
            ['university_id' => '3008', 'name' => 'سلمى عواد', 'email' => 'salma.awwad@uniflow.test', 'password' => 'student3008', 'role' => Role::Student],
            ['university_id' => '3009', 'name' => 'بشار قضماني', 'email' => 'bashar.kodmani@uniflow.test', 'password' => 'student3009', 'role' => Role::Student],
            ['university_id' => '3010', 'name' => 'ريتا معلوف', 'email' => 'rita.maalouf@uniflow.test', 'password' => 'student3010', 'role' => Role::Student],
        ];
    }
}
