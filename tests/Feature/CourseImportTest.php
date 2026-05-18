<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\CoursePreset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CourseImportTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'username' => 'course_admin',
            'name' => 'Course Admin',
            'office' => 'HR',
            'designation' => 'HR Officer',
            'email' => 'course-admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    #[Test]
    public function it_previews_uploaded_program_rows_with_duplicate_statuses(): void
    {
        CoursePreset::query()->create([
            'course_code' => 'COLLEGE_EXISTING_PROGRAM',
            'course_name' => 'Existing Program',
            'program_level' => 'COLLEGE',
        ]);

        $file = UploadedFile::fake()->createWithContent(
            'programs.csv',
            "program_name\nBachelor of Science in Forestry\nExisting Program\nBachelor of Science in Forestry\n"
        );

        $response = $this->actingAs($this->admin, 'admin')->postJson(route('admin.courses.preview_import'), [
            'program_level' => 'COLLEGE',
            'import_file' => $file,
        ]);

        $response->assertOk()
            ->assertJsonPath('summary.total_rows', 3)
            ->assertJsonPath('summary.ready_rows', 1)
            ->assertJsonPath('summary.skipped_rows', 2)
            ->assertJsonPath('items.0.status', 'ready')
            ->assertJsonPath('items.1.status', 'duplicate_existing')
            ->assertJsonPath('items.2.status', 'duplicate_file');
    }

    #[Test]
    public function it_imports_only_new_programs_from_uploaded_file(): void
    {
        CoursePreset::query()->create([
            'course_code' => 'COLLEGE_EXISTING_PROGRAM',
            'course_name' => 'Existing Program',
            'program_level' => 'COLLEGE',
        ]);

        $file = UploadedFile::fake()->createWithContent(
            'programs.csv',
            "program_name\nBachelor of Science in Forestry\nExisting Program\nBachelor of Public Management\nBachelor of Science in Forestry\n"
        );

        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.courses.import'), [
            'program_level' => 'COLLEGE',
            'import_file' => $file,
        ]);

        $response->assertRedirect(route('admin.courses.index'));
        $response->assertSessionHas('success', '2 programs imported successfully. 2 rows were skipped.');

        $this->assertDatabaseHas('course_presets', [
            'course_name' => 'Bachelor of Science in Forestry',
            'program_level' => 'COLLEGE',
        ]);

        $this->assertDatabaseHas('course_presets', [
            'course_name' => 'Bachelor of Public Management',
            'program_level' => 'COLLEGE',
        ]);

        $this->assertSame(3, CoursePreset::query()->count());
    }
}
