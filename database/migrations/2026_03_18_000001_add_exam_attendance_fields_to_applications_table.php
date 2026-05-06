<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasReadAt = Schema::hasColumn('applications', 'read_at');
        $hasFileRemarks = Schema::hasColumn('applications', 'file_remarks');
        $hasExamAttendanceStatus = Schema::hasColumn('applications', 'exam_attendance_status');
        $hasExamAttendanceRemark = Schema::hasColumn('applications', 'exam_attendance_remark');
        $hasExamAttendanceRespondedAt = Schema::hasColumn('applications', 'exam_attendance_responded_at');

        Schema::table('applications', function (Blueprint $table) use (
            $hasReadAt,
            $hasFileRemarks,
            $hasExamAttendanceStatus,
            $hasExamAttendanceRemark,
            $hasExamAttendanceRespondedAt
        ) {
            // Keep migration resilient across schemas where `read_at` does not exist.
            if (!$hasExamAttendanceStatus) {
                if ($hasReadAt) {
                    $table->string('exam_attendance_status', 32)->nullable()->after('read_at');
                } elseif ($hasFileRemarks) {
                    $table->string('exam_attendance_status', 32)->nullable()->after('file_remarks');
                } else {
                    $table->string('exam_attendance_status', 32)->nullable();
                }
            }

            if (!$hasExamAttendanceRemark) {
                $table->text('exam_attendance_remark')->nullable()->after('exam_attendance_status');
            }

            if (!$hasExamAttendanceRespondedAt) {
                $table->timestamp('exam_attendance_responded_at')->nullable()->after('exam_attendance_remark');
            }
        });
    }

    public function down(): void
    {
        $dropColumns = array_values(array_filter([
            Schema::hasColumn('applications', 'exam_attendance_status') ? 'exam_attendance_status' : null,
            Schema::hasColumn('applications', 'exam_attendance_remark') ? 'exam_attendance_remark' : null,
            Schema::hasColumn('applications', 'exam_attendance_responded_at') ? 'exam_attendance_responded_at' : null,
        ]));

        if (empty($dropColumns)) {
            return;
        }

        Schema::table('applications', function (Blueprint $table) use ($dropColumns) {
            $table->dropColumn($dropColumns);
        });
    }
};
