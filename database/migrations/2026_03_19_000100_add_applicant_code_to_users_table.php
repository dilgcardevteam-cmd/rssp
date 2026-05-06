<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('applicant_code', 20)->nullable()->after('id');
        });

        $sequenceByYear = [];

        DB::table('users')
            ->select(['id', 'created_at'])
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->each(function ($user) use (&$sequenceByYear): void {
                $year = !empty($user->created_at)
                    ? date('y', strtotime((string) $user->created_at))
                    : now()->format('y');

                $sequenceByYear[$year] = ($sequenceByYear[$year] ?? 0) + 1;
                $applicantCode = 'APP-' . $year . '-' . str_pad((string) $sequenceByYear[$year], 5, '0', STR_PAD_LEFT);

                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['applicant_code' => $applicantCode]);
            });

        Schema::table('users', function (Blueprint $table) {
            $table->unique('applicant_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['applicant_code']);
            $table->dropColumn('applicant_code');
        });
    }
};
