<?php

namespace Tests\Unit;

use App\Enums\ApplicationStatus;
use App\Services\ApplicationStatusTransitionService;
use PHPUnit\Framework\TestCase;

class ApplicationStatusTransitionServiceTest extends TestCase
{
    public function test_normalize_preserves_canonical_values_and_aliases(): void
    {
        $this->assertSame('Pending', ApplicationStatus::normalize('pending'));
        $this->assertSame('Compliance', ApplicationStatus::normalize('  COMPLIANCE  '));
        $this->assertSame('in-progress', ApplicationStatus::normalize('in progress'));
    }

    public function test_transition_rules_allow_expected_flows(): void
    {
        $service = new ApplicationStatusTransitionService();

        $this->assertTrue($service->canTransition('Pending', 'Compliance'));
        $this->assertTrue($service->canTransition('Compliance', 'Updated'));
        $this->assertTrue($service->canTransition('Updated', 'Qualified'));
        $this->assertTrue($service->canTransition('Qualified', 'ready'));
        $this->assertTrue($service->canTransition('ready', 'in-progress'));
        $this->assertTrue($service->canTransition('in-progress', 'submitted'));
    }

    public function test_transition_rules_block_invalid_jumps(): void
    {
        $service = new ApplicationStatusTransitionService();

        $this->assertFalse($service->canTransition('Pending', 'submitted'));
        $this->assertFalse($service->canTransition('submitted', 'Compliance'));
    }
}

