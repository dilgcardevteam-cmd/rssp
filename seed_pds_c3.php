<?php

use App\Models\VoluntaryWork;
use App\Models\LearningAndDevelopment;
use App\Models\OtherInformation;
use Carbon\Carbon;

$userId = 34;

// 1. Clear existing data for user 34
VoluntaryWork::where('user_id', $userId)->delete();
LearningAndDevelopment::where('user_id', $userId)->delete();
OtherInformation::where('user_id', $userId)->delete();

echo "Cleared existing data for User ID $userId\n";

// 2. Add 10 Voluntary Works
for ($i = 1; $i <= 10; $i++) {
    VoluntaryWork::create([
        'user_id' => $userId,
        'voluntary_org' => "Voluntary Organization $i",
        'voluntary_from' => Carbon::now()->subMonths(24 - $i)->format('Y-m-d'),
        'voluntary_to' => Carbon::now()->subMonths(23 - $i)->format('Y-m-d'),
        'voluntary_hours' => $i * 10,
        'voluntary_position' => "Volunteer Position $i",
    ]);
}
echo "Added 10 Voluntary Work entries\n";

// 3. Add 20 Learning and Development entries
$types = ['Managerial', 'Supervisory', 'Technical', 'Others'];
for ($i = 1; $i <= 20; $i++) {
    LearningAndDevelopment::create([
        'user_id' => $userId,
        'learning_title' => "Training Program $i: Advanced Skills in " . ($i % 2 == 0 ? "Leadership" : "Technology"),
        'learning_type' => $types[$i % 4],
        'learning_from' => Carbon::now()->subMonths(12 - $i)->format('Y-m-d'),
        'learning_to' => Carbon::now()->subMonths(12 - $i)->addDays(5)->format('Y-m-d'),
        'learning_hours' => ($i % 5 + 1) * 8,
        'learning_conducted' => "Training Institution " . ($i % 3 + 1),
    ]);
}
echo "Added 20 L&D entries\n";

// 4. Add Other Information
OtherInformation::create([
    'user_id' => $userId,
    'skill' => ['Programming', 'Public Speaking', 'Graphic Design', 'Data Analysis'],
    'distinction' => ['Employee of the Quarter 2024', 'Highest Sales Award 2023', 'Dean\'s List 2020'],
    'organization' => ['Rotary Club of Baguio', 'Philippine Computer Society', 'Red Cross Youth'],
]);
echo "Added Other Information\n";

echo "Done seeding data for User ID $userId\n";
