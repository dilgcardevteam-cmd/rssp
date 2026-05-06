<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Vacancies:\n";
try {
    \App\Models\JobVacancy::all()->each(function($v) {
        echo $v->vacancy_id . ' - ' . $v->position_title . ' (ID: ' . $v->id . ')' . "\n";
    });

    echo "\nCheck specific demo users:\n";
    $users = \App\Models\User::where('email', 'like', '%@demo.com')->get();
    echo "Found " . $users->count() . " demo users.\n";
    foreach($users as $u) {
        echo $u->email . " (ID: " . $u->id . ")\n";
        $apps = \App\Models\Applications::where('user_id', $u->id)->get();
        foreach($apps as $app) {
            echo "  - Applied to Vacancy ID: " . $app->vacancy_id . " (Status: " . $app->status . ")\n";
        }
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
