<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PdsLoadingOverlayTest extends TestCase
{
    use RefreshDatabase;

    public function test_loading_overlay_and_animation_are_rendered_on_pds_c3(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/pds/c3');

        $response->assertOk();
        $content = $response->getContent();

        $this->assertStringContainsString('id="loader"', $content);
        $this->assertStringContainsString('pds-loading-spinner', $content);
        $this->assertStringContainsString('aria-live="polite"', $content);
    }

    public function test_loading_script_disables_buttons_and_avoids_duplicate_initialization(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/pds/c3');

        $response->assertOk();
        $content = $response->getContent();

        $this->assertStringContainsString('loadingDisabled', $content);
        $this->assertStringContainsString('button.disabled = true', $content);
        $this->assertStringContainsString('pds-loading-nonblocking', $content);
        $this->assertStringContainsString('__pdsLoadingInitialized', $content);
    }
}
