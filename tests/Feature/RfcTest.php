<?php

namespace Tests\Feature;

use App\Http\Controllers\EndRfcController;
use App\Http\Controllers\PublishRfcController;
use App\Http\Controllers\RfcAdminController;
use App\Http\Controllers\RfcCreateController;
use App\Http\Controllers\RfcEditController;
use App\Http\Controllers\RfcMetaImageController;
use App\Models\Rfc;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RfcTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function rfc_management_only_accessible_by_admin()
    {
        $this->login();

        $this->get('/admin/rfc')
            ->assertRedirect('/');
    }

    /** @test */
    public function rfc_management_page_can_be_rendered()
    {
        $this->login(null, true);

        $this->get(action(RfcAdminController::class))
            ->assertViewIs('rfc-admin')
            ->assertOk();
    }

    /** @test */
    public function create_rfc_screen_can_be_rendered()
    {
        $this->login(null, true);

        $this->get(action([RfcCreateController::class, 'create']))
            ->assertViewIs('rfc-form')
            ->assertOk();
    }

    /** @test */
    public function create_rfc_returns_validation_errors()
    {
        $this->login(null, true);

        $this->post(action([RfcCreateController::class, 'store']))
            ->assertSessionHasErrors(['title', 'description', 'url']);
    }

    /** @test */
    public function it_can_create_rfc()
    {
        $this->login(null, true);

        $this->withoutExceptionHandling();

        $this->post(action([RfcCreateController::class, 'store']), [
            'title' => $this->faker->text(10),
            'teaser' => $this->faker->text(50),
            'description' => $this->faker->text(50),
            'url' => $this->faker->url,
        ])
            ->assertRedirect();

        $this->assertDatabaseCount('rfcs', 1);
    }

    /** @test */
    public function edit_rfc_screen_can_be_rendered()
    {
        $rfc = Rfc::factory()->create();
        $this->login(null, true);

        $this->get(action([RfcEditController::class, 'edit'], $rfc))
            ->assertViewIs('rfc-form')
            ->assertSee($rfc->title)
            ->assertSee($rfc->url)
            ->assertSee($rfc->id)
            ->assertSee($rfc->description)
            ->assertOk();
    }

    /** @test */
    public function rfc_can_be_updated()
    {
        $rfc = Rfc::factory()->create();

        $this->login(isAdmin: true);

        $newUrl = $this->faker->url;

        $this->post(action([RfcEditController::class, 'update'], $rfc),
            [
                'title' => 'updated_title',
                'description' => 'updated_description',
                'teaser' => 'updated_teaser',
                'url' => $newUrl,
            ])
            ->assertRedirect(action([RfcEditController::class, 'update'], $rfc));

        $this->assertDatabaseCount('rfcs', 1);
        $this->assertDatabaseHas('rfcs', [
            'title' => 'updated_title',
            'description' => 'updated_description',
            'teaser' => 'updated_teaser',
            'url' => $newUrl,
        ]);
    }

    /** @test */
    public function rfc_can_be_published()
    {
        $rfc = Rfc::factory()->create();
        $this->login(null, true);
        $this->post(action(PublishRfcController::class, $rfc))
            ->assertRedirect(action(RfcAdminController::class));
    }

    /** @test */
    public function rfc_can_be_ended()
    {
        $rfc = Rfc::factory()->create();
        $this->login(null, true);
        $this->post(action(EndRfcController::class, $rfc))
            ->assertRedirect(action(RfcAdminController::class));
    }

    /** @test */
    public function rfc_meta_image_has_no_cache_headers()
    {
        $rfc = Rfc::factory()->create();
        $response = $this->get(action(RfcMetaImageController::class, $rfc))
            ->assertHeader('Content-Type', 'image/png')
            ->assertHeader('Cache-Control', 'max-age=900, public, s-maxage=900, stale-if-error=900, stale-while-revalidate=900');

        $expires = $response->headers->get('Expires');

        $this->assertEquals(now()->format('d-m-Y'), Carbon::parse($expires)->format('d-m-Y'));
    }
}
