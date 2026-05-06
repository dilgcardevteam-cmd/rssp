<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\JobVacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreVacancyTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        // Create an admin user
        $this->admin = Admin::create([
            'username' => 'admin_test',
            'name' => 'Admin Test',
            'office' => 'HR',
            'designation' => 'HR Officer',
            'email' => 'admin_test@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    #[Test]
    public function it_creates_vacancy_with_default_status_open()
    {
        $data = [
            'position_title' => 'Test Position',
            'vacancy_type' => 'Plantilla',
            'closing_date' => now()->addMonth()->format('Y-m-d'),
            'monthly_salary' => 50000,
            'salary_grade' => 'SG-15',
            'place_of_assignment' => 'Baguio City',
            'qualification_education' => 'Bachelor Degree',
            'qualification_training' => 'None',
            'qualification_experience' => 'None',
            'qualification_eligibility' => 'CSC Professional',
            'to_person' => 'Regional Director',
            'to_position' => 'Director IV',
            'to_office' => 'DILG CAR',
            'to_office_address' => 'Baguio City',
            // No status provided
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('plantilla.store'), $data);

        $response->assertRedirect(route('vacancies_management'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('job_vacancies', [
            'position_title' => 'Test Position',
            'status' => 'OPEN',
        ]);
    }

    #[Test]
    public function it_ignores_status_input_and_forces_open()
    {
        $data = [
            'position_title' => 'Test Position 2',
            'vacancy_type' => 'COS',
            'closing_date' => now()->addMonth()->format('Y-m-d'),
            'monthly_salary' => 30000,
            'place_of_assignment' => 'Baguio City',
            'qualification_education' => 'Bachelor Degree',
            'qualification_training' => 'None',
            'qualification_experience' => 'None',
            'qualification_eligibility' => 'CSC Professional',
            'to_person' => 'Regional Director',
            'to_position' => 'Director IV',
            'to_office' => 'DILG CAR',
            'to_office_address' => 'Baguio City',
            'status' => 'CLOSED', // Trying to force CLOSED
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('vacancies.store'), $data);

        $response->assertRedirect(route('vacancies_management'));

        $this->assertDatabaseHas('job_vacancies', [
            'position_title' => 'Test Position 2',
            'status' => 'OPEN', // Should still be OPEN
        ]);
        
        $this->assertDatabaseMissing('job_vacancies', [
            'position_title' => 'Test Position 2',
            'status' => 'CLOSED',
        ]);
    }
}
