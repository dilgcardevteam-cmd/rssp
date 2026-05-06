<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eligibility_presets', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('legal_basis')->nullable();
            $table->string('level')->nullable();
            $table->timestamps();
        });

        $now = now();

        DB::table('eligibility_presets')->insert([
            [
                'name' => 'CSC Professional Eligibility',
                'legal_basis' => 'CSR 2017/PD 807',
                'level' => 'Second Level',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Bar/Board Eligibility',
                'legal_basis' => 'RA 1080',
                'level' => 'Second Level',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Honor Graduate Eligibility',
                'legal_basis' => 'PD 907',
                'level' => 'Second Level',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Subprofessional (Sub-Prof) Eligibility',
                'legal_basis' => 'CSR 2017/PD 807',
                'level' => 'First Level',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Barangay Health Worker Eligibility',
                'legal_basis' => 'RA 7883',
                'level' => 'First Level',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Barangay Nutrition Scholar Eligibility',
                'legal_basis' => 'PD 1569',
                'level' => 'First Level',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Barangay Official Eligibility',
                'legal_basis' => 'RA 7160',
                'level' => 'First Level',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Sanggunian Member Eligibility',
                'legal_basis' => 'RA 10156',
                'level' => 'First Level',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Skills Eligibility-Category II',
                'legal_basis' => 'CSC MC 11, s.1996',
                'level' => 'First Level',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Electronic Data Processing Specialist Eligibility',
                'legal_basis' => 'CSC Res. 90-083',
                'level' => 'Second Level',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Foreign School Honor Graduate Eligibility',
                'legal_basis' => 'CSC Res. 1302714',
                'level' => 'Second Level',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Scientific and Technological Specialist Eligibility',
                'legal_basis' => 'PD 997',
                'level' => 'Second Level',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('eligibility_presets');
    }
};

