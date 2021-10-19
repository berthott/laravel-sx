<?php

namespace berthott\SX\Tests\Feature\Routes;

use Illuminate\Support\Facades\Route;

class RoutesTest extends RoutesTestCase
{
    public function test_routes_exist(): void
    {
        $expectedRoutes = [
            'entities.index',
        ];
        $registeredRoutes = array_keys(Route::getRoutes()->getRoutesByName());
        foreach ($expectedRoutes as $route) {
            $this->assertContains($route, $registeredRoutes);
        }
    }

    public function test_index_route(): void
    {
        $this->get(route('entities.index'))
            ->assertStatus(200)
            ->assertJson([
                ['responde' => 825478429],
                ['responde' => 825478569],
                ['responde' => 825479792],
                ['responde' => 834262051],
            ]);
    }
}
