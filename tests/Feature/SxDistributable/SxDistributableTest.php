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
            'entities.sxcollect',
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
}
