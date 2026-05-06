<?php

namespace Database\Seeders;

use App\Models\EligibilityPreset;
use Illuminate\Database\Seeder;

class EligibilityPresetSeeder extends Seeder
{
    public function run(): void
    {
        EligibilityPreset::truncate();

        $presets = [
            ['name' => 'CSC Professional Eligibility', 'legal_basis' => 'CSR 2017 / PD 807', 'level' => 'Second Level'],
            ['name' => 'CSC Subprofessional Eligibility', 'legal_basis' => 'CSR 2017 / PD 807', 'level' => 'First Level'],
            ['name' => 'Bar/Board Eligibility', 'legal_basis' => 'RA 1080', 'level' => 'Second Level'],
            ['name' => 'Honor Graduate Eligibility', 'legal_basis' => 'PD 907', 'level' => 'Second Level'],
            ['name' => 'Foreign School Honor Graduate Eligibility', 'legal_basis' => 'CSC Resolution No. 1302714', 'level' => 'Second Level'],
            ['name' => 'Scientific and Technological Specialist Eligibility', 'legal_basis' => 'PD 997', 'level' => 'Second Level'],
            ['name' => 'Electronic Data Processing Specialist Eligibility', 'legal_basis' => 'CSC Resolution No. 90-083', 'level' => 'Second Level'],
            ['name' => 'Skills Eligibility – Category II', 'legal_basis' => 'CSC MC No. 11, s. 1996, as amended', 'level' => 'First Level'],
            ['name' => 'Barangay Official Eligibility', 'legal_basis' => 'RA 7160', 'level' => 'First Level'],
            ['name' => 'Barangay Health Worker Eligibility', 'legal_basis' => 'RA 7883', 'level' => 'First Level'],
            ['name' => 'Barangay Nutrition Scholar Eligibility', 'legal_basis' => 'PD 1569', 'level' => 'First Level'],
            ['name' => 'Sanggunian Member First Level Eligibility', 'legal_basis' => 'RA 10156', 'level' => 'First Level'],
            ['name' => 'Sanggunian Member Second Level Eligibility', 'legal_basis' => 'RA 10156', 'level' => 'Second Level'],
            ['name' => 'Veteran Preference Rating Eligibility', 'legal_basis' => 'Professional or Subprofessional, depending on exam/rating', 'level' => 'Second Level'],
            ['name' => 'Career Service Eligibility – Preference Rating', 'legal_basis' => 'CSE-PR', 'level' => 'Second Level'],
            ['name' => 'Career Service Eligibility – Preference Rating for Military and Uniformed Personnel', 'legal_basis' => 'CSE-PR for MUP', 'level' => 'Second Level'],
        ];

        foreach ($presets as $preset) {
            EligibilityPreset::create($preset);
        }
    }
}
