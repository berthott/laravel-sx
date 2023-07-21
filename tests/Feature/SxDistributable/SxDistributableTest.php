<?php

namespace berthott\SX\Tests\Feature\SxDistributable;

use Facades\berthott\SX\Services\SxDistributableService;
use Illuminate\Support\Facades\Route;

class SxDistributableTest extends SxDistributableTestCase
{
    public function test_distributable_found(): void
    {
        $distributable = SxDistributableService::getTargetableClasses();
        $this->assertNotEmpty($distributable);
    }

    public function test_distributable_routes_exist(): void
    {
        $expectedRoutes = [
            'entities.sxcollect',
            'entities.sxdata',
            'entities.qrcode',
            'entities.pdf',
        ];
        $registeredRoutes = array_keys(Route::getRoutes()->getRoutesByName());
        foreach ($expectedRoutes as $route) {
            $this->assertContains($route, $registeredRoutes);
        }
    }

    public function test_collect_route(): void
    {
        $entity = Entity::factory()->create();
        $this->assertDatabaseMissing('entity_sxes', ['respondentid' => '841931211']);
        $this->get(route('entities.sxcollect', ['entity' => $entity->id]))
            ->assertRedirect();
        $this->assertDatabaseHas('entity_sxes', [
            'respondentid' => '841931211',
            's_2' => 1999,
        ]);
    }

    public function test_query_collect_route(): void
    {
        $entity = Entity::factory()->create();
        $this->assertDatabaseMissing('entity_sxes', ['respondentid' => '841931211']);
        $this->get(route('entities.sxquerycollect'))
            ->assertBadRequest();
        $this->get(route('entities.sxquerycollect', ['year' => $entity->name]))
            ->assertJsonValidationErrorFor('year');
        $this->get(route('entities.sxquerycollect', ['name' => $entity->name]))
            ->assertRedirect();
        $this->assertDatabaseHas('entity_sxes', [
            'respondentid' => '841931211',
            's_2' => 1999,
        ]);
    }

    public function test_data_route(): void
    {
        $entity = Entity::factory()->create();
        $this->get(route('entities.sxdata', ['entity' => $entity->id]))
            ->assertSuccessful()
            ->assertExactJson([
                'name' => $entity->name
            ]);
    }

    public function test_qrcode_route(): void
    {
        $entity = Entity::factory()->create();
        $this->get(route('entities.qrcode', ['entity' => $entity->id]))
            ->assertSuccessful()
            ->assertExactJson([
                'data' => 'data:image/png;base64,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'
            ]);
    }

    public function test_pdf_route(): void
    {
        $expectedName = 'test';
        $entity = Entity::factory()->create();
        $response = $this->get(route('entities.pdf', ['entity' => $entity->id]))
            ->assertSuccessful();

        $this->assertTrue($response->headers->get('content-type') == 'application/pdf');
        $this->assertExpectedFileResponse($response, __DIR__.'/'.$expectedName, '.pdf');

    }
}
