<?php

namespace berthott\SX\Tests\Feature\RouteOptions;

use Illuminate\Support\Facades\Route;

class RouteOptionsTest extends RouteOptionsTestCase
{
    public function test_only_routes(): void
    {
        $expectedRoutes = [
            'only_entities.index',
        ];
        $unexpectedRoutes = [
            'only_entities.show', 
            'only_entities.show_respondent',
            'only_entities.create_respondent', 
            'only_entities.update_respondent', 
            'only_entities.destroy', 
            'only_entities.destroy_many', 
            'only_entities.structure', 
            'only_entities.sync', 
            'only_entities.export', 
            'only_entities.labels', 
            'only_entities.report', 
            'only_entities.report_pdf', 
            'only_entities.languages',
        ];
        $registeredRoutes = array_keys(Route::getRoutes()->getRoutesByName());
        foreach ($expectedRoutes as $route) {
            $this->assertContains($route, $registeredRoutes);
        }
        foreach ($unexpectedRoutes as $route) {
            $this->assertNotContains($route, $registeredRoutes);
        }
    }
    public function test_except_routes(): void
    {
        $expectedRoutes = [
            'except_entities.index',
            'except_entities.show', 
            'except_entities.show_respondent',
            'except_entities.create_respondent', 
            'except_entities.update_respondent', 
            'except_entities.structure', 
            'except_entities.sync', 
            'except_entities.export', 
            'except_entities.labels', 
            'except_entities.report', 
            'except_entities.report_pdf', 
            'except_entities.languages',
        ];
        $unexpectedRoutes = [
            'except_entities.destroy', 
            'except_entities.destroy_many', 
        ];
        $registeredRoutes = array_keys(Route::getRoutes()->getRoutesByName());
        foreach ($expectedRoutes as $route) {
            $this->assertContains($route, $registeredRoutes);
        }
        foreach ($unexpectedRoutes as $route) {
            $this->assertNotContains($route, $registeredRoutes);
        }
    }
}
