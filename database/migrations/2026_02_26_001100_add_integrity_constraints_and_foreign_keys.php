<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        // Align legacy integer column with users.id (unsigned big integer) before FK creation.
        if (in_array($driver, ['mysql', 'mariadb'], true) && Schema::hasTable('applications') && Schema::hasColumn('applications', 'user_id')) {
            DB::statement('ALTER TABLE applications MODIFY user_id BIGINT UNSIGNED NOT NULL');
        } elseif ($driver === 'pgsql' && Schema::hasTable('applications') && Schema::hasColumn('applications', 'user_id')) {
            DB::statement('ALTER TABLE applications ALTER COLUMN user_id TYPE BIGINT');
        }

        $this->addApplicationConstraints();
        $this->addUploadedDocumentConstraints();
        $this->addExamConstraints();
        $this->addJobVacancyChecks($driver);
    }

    public function down(): void
    {
        $this->dropIfExists('applications', 'applications_user_vacancy_unique');
        $this->dropIfExists('applications', 'applications_user_id_fk');
        $this->dropIfExists('applications', 'applications_vacancy_id_fk');
        $this->dropIfExists('applications', 'applications_updated_by_admin_id_fk');
        $this->dropIfExists('uploaded_documents', 'uploaded_documents_vacancy_id_fk');
        $this->dropIfExists('exam_details', 'exam_details_vacancy_id_fk');
        $this->dropIfExists('exam_items', 'exam_items_vacancy_id_fk');
        $this->dropIfExists('exam_tab_violations', 'exam_tab_violations_user_id_fk');
        $this->dropIfExists('exam_tab_violations', 'exam_tab_violations_vacancy_id_fk');
        $this->dropIfExists('job_vacancies', 'chk_job_vacancies_status');
        $this->dropIfExists('job_vacancies', 'chk_job_vacancies_type');
    }

    private function addApplicationConstraints(): void
    {
        if (!Schema::hasTable('applications')) {
            return;
        }

        $duplicatesExist = DB::table('applications')
            ->select('user_id', 'vacancy_id', DB::raw('COUNT(*) as duplicate_count'))
            ->groupBy('user_id', 'vacancy_id')
            ->having('duplicate_count', '>', 1)
            ->exists();

        if (!$duplicatesExist) {
            try {
                Schema::table('applications', function (Blueprint $table) {
                    $table->unique(['user_id', 'vacancy_id'], 'applications_user_vacancy_unique');
                });
            } catch (\Throwable $e) {
                Log::warning('Unable to add unique index on applications(user_id, vacancy_id).', ['error' => $e->getMessage()]);
            }
        } else {
            Log::warning('Skipped unique applications(user_id, vacancy_id) due to duplicate data.');
        }

        $orphanUsers = DB::table('applications as a')
            ->leftJoin('users as u', 'u.id', '=', 'a.user_id')
            ->whereNull('u.id')
            ->count();

        if ($orphanUsers === 0) {
            try {
                Schema::table('applications', function (Blueprint $table) {
                    $table->foreign('user_id', 'applications_user_id_fk')
                        ->references('id')
                        ->on('users')
                        ->cascadeOnDelete();
                });
            } catch (\Throwable $e) {
                Log::warning('Unable to add applications.user_id foreign key.', ['error' => $e->getMessage()]);
            }
        }

        $orphanVacancies = DB::table('applications as a')
            ->leftJoin('job_vacancies as v', 'v.vacancy_id', '=', 'a.vacancy_id')
            ->whereNull('v.vacancy_id')
            ->count();

        if ($orphanVacancies === 0) {
            try {
                Schema::table('applications', function (Blueprint $table) {
                    $table->foreign('vacancy_id', 'applications_vacancy_id_fk')
                        ->references('vacancy_id')
                        ->on('job_vacancies')
                        ->cascadeOnDelete();
                });
            } catch (\Throwable $e) {
                Log::warning('Unable to add applications.vacancy_id foreign key.', ['error' => $e->getMessage()]);
            }
        }

        $orphanAdmins = DB::table('applications as a')
            ->leftJoin('admins as ad', 'ad.id', '=', 'a.updated_by_admin_id')
            ->whereNotNull('a.updated_by_admin_id')
            ->whereNull('ad.id')
            ->count();

        if ($orphanAdmins === 0) {
            try {
                Schema::table('applications', function (Blueprint $table) {
                    $table->foreign('updated_by_admin_id', 'applications_updated_by_admin_id_fk')
                        ->references('id')
                        ->on('admins')
                        ->nullOnDelete();
                });
            } catch (\Throwable $e) {
                Log::warning('Unable to add applications.updated_by_admin_id foreign key.', ['error' => $e->getMessage()]);
            }
        }
    }

    private function addUploadedDocumentConstraints(): void
    {
        if (!Schema::hasTable('uploaded_documents') || !Schema::hasColumn('uploaded_documents', 'vacancy_id')) {
            return;
        }

        $orphanVacancyDocs = DB::table('uploaded_documents as d')
            ->leftJoin('job_vacancies as v', 'v.vacancy_id', '=', 'd.vacancy_id')
            ->whereNotNull('d.vacancy_id')
            ->whereNull('v.vacancy_id')
            ->count();

        if ($orphanVacancyDocs === 0) {
            try {
                Schema::table('uploaded_documents', function (Blueprint $table) {
                    $table->foreign('vacancy_id', 'uploaded_documents_vacancy_id_fk')
                        ->references('vacancy_id')
                        ->on('job_vacancies')
                        ->nullOnDelete();
                });
            } catch (\Throwable $e) {
                Log::warning('Unable to add uploaded_documents.vacancy_id foreign key.', ['error' => $e->getMessage()]);
            }
        }
    }

    private function addExamConstraints(): void
    {
        if (Schema::hasTable('exam_details')) {
            $orphanRows = DB::table('exam_details as e')
                ->leftJoin('job_vacancies as v', 'v.vacancy_id', '=', 'e.vacancy_id')
                ->whereNull('v.vacancy_id')
                ->count();

            if ($orphanRows === 0) {
                try {
                    Schema::table('exam_details', function (Blueprint $table) {
                        $table->foreign('vacancy_id', 'exam_details_vacancy_id_fk')
                            ->references('vacancy_id')
                            ->on('job_vacancies')
                            ->cascadeOnDelete();
                    });
                } catch (\Throwable $e) {
                    Log::warning('Unable to add exam_details.vacancy_id foreign key.', ['error' => $e->getMessage()]);
                }
            }
        }

        if (Schema::hasTable('exam_items')) {
            $orphanRows = DB::table('exam_items as e')
                ->leftJoin('job_vacancies as v', 'v.vacancy_id', '=', 'e.vacancy_id')
                ->whereNull('v.vacancy_id')
                ->count();

            if ($orphanRows === 0) {
                try {
                    Schema::table('exam_items', function (Blueprint $table) {
                        $table->foreign('vacancy_id', 'exam_items_vacancy_id_fk')
                            ->references('vacancy_id')
                            ->on('job_vacancies')
                            ->cascadeOnDelete();
                    });
                } catch (\Throwable $e) {
                    Log::warning('Unable to add exam_items.vacancy_id foreign key.', ['error' => $e->getMessage()]);
                }
            }
        }

        if (Schema::hasTable('exam_tab_violations')) {
            $orphanUsers = DB::table('exam_tab_violations as e')
                ->leftJoin('users as u', 'u.id', '=', 'e.user_id')
                ->whereNull('u.id')
                ->count();

            if ($orphanUsers === 0) {
                try {
                    Schema::table('exam_tab_violations', function (Blueprint $table) {
                        $table->foreign('user_id', 'exam_tab_violations_user_id_fk')
                            ->references('id')
                            ->on('users')
                            ->cascadeOnDelete();
                    });
                } catch (\Throwable $e) {
                    Log::warning('Unable to add exam_tab_violations.user_id foreign key.', ['error' => $e->getMessage()]);
                }
            }

            $orphanVacancies = DB::table('exam_tab_violations as e')
                ->leftJoin('job_vacancies as v', 'v.vacancy_id', '=', 'e.vacancy_id')
                ->whereNull('v.vacancy_id')
                ->count();

            if ($orphanVacancies === 0) {
                try {
                    Schema::table('exam_tab_violations', function (Blueprint $table) {
                        $table->foreign('vacancy_id', 'exam_tab_violations_vacancy_id_fk')
                            ->references('vacancy_id')
                            ->on('job_vacancies')
                            ->cascadeOnDelete();
                    });
                } catch (\Throwable $e) {
                    Log::warning('Unable to add exam_tab_violations.vacancy_id foreign key.', ['error' => $e->getMessage()]);
                }
            }
        }
    }

    private function addJobVacancyChecks(string $driver): void
    {
        // MySQL supports CHECK constraints in recent versions, but they can fail on older deployments.
        // Keep this best-effort and non-blocking.
        if (!in_array($driver, ['mysql', 'mariadb'], true) || !Schema::hasTable('job_vacancies')) {
            return;
        }

        try {
            DB::statement("ALTER TABLE job_vacancies ADD CONSTRAINT chk_job_vacancies_status CHECK (status IN ('OPEN','CLOSED'))");
        } catch (\Throwable $e) {
            Log::warning('Unable to add job_vacancies status CHECK constraint.', ['error' => $e->getMessage()]);
        }

        try {
            DB::statement("ALTER TABLE job_vacancies ADD CONSTRAINT chk_job_vacancies_type CHECK (vacancy_type IN ('COS','Plantilla'))");
        } catch (\Throwable $e) {
            Log::warning('Unable to add job_vacancies vacancy_type CHECK constraint.', ['error' => $e->getMessage()]);
        }
    }

    private function dropIfExists(string $table, string $constraint): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $blueprint) use ($constraint) {
                $blueprint->dropUnique($constraint);
            });
            return;
        } catch (\Throwable $e) {
            // noop; try foreign/check drop below
        }

        try {
            Schema::table($table, function (Blueprint $blueprint) use ($constraint) {
                $blueprint->dropForeign($constraint);
            });
            return;
        } catch (\Throwable $e) {
            // noop; try raw statement below
        }

        try {
            DB::statement("ALTER TABLE {$table} DROP CHECK {$constraint}");
        } catch (\Throwable $e) {
            // noop
        }
    }
};
