<?php

namespace Tests\Feature;

use Tests\TestCase;

class AssetOriginTest extends TestCase
{
    public function test_login_page_uses_current_request_origin_for_logo_assets(): void
    {
        config(['app.asset_url' => 'http://192.168.26.38:8000']);

        $response = $this->get('http://localhost/login');

        $response->assertOk();
        $response->assertSee('http://localhost/images/dilg_logo.png', false);
        $response->assertDontSee('http://192.168.26.38:8000/images/dilg_logo.png', false);
    }
}
