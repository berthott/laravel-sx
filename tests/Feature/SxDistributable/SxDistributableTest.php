<?php

namespace berthott\SX\Tests\Feature\SxDistributable;

use berthott\SX\Facades\SxDistributable;
use Illuminate\Support\Facades\Route;

class SxDistributableTest extends SxDistributableTestCase
{
    public function test_distributable_found(): void
    {
        $distributable = SxDistributable::getTargetableClasses();
        $this->assertNotEmpty($distributable);
    }

    public function test_distributable_routes_exist(): void
    {
        $expectedRoutes = [
            'entity_sxes.sxcollect',
        ];
        $registeredRoutes = array_keys(Route::getRoutes()->getRoutesByName());
        foreach ($expectedRoutes as $route) {
            $this->assertContains($route, $registeredRoutes);
        }
    }

    public function test_collect_route(): void
    {
        $entity = Entity::factory()->create();
        $response = $this->get(route('entity_sxes.sxcollect', ['entity_sx' => $entity->id]))
            ->assertStatus(200);
    }
}
