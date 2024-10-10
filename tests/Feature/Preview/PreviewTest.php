<?php

namespace berthott\SX\Tests\Feature\Preview;

use Illuminate\Support\Facades\Route;

class PreviewTest extends PreviewTestCase
{
    public function test_routes_existence(): void
    {
        $expectedRoutes = [
            'entities.index',
            'entities.preview',
            'no_previews.index',
            'distribution_entities.preview',
        ];
        $unexpectedRoutes = [
            'no_previews.preview',
        ];
        $registeredRoutes = array_keys(Route::getRoutes()->getRoutesByName());
        foreach ($expectedRoutes as $route) {
            $this->assertContains($route, $registeredRoutes);
        }
        foreach ($unexpectedRoutes as $route) {
            $this->assertNotContains($route, $registeredRoutes);
        }
    }

    public function test_preview_route(): void
    {
        $respondentStructure = [
            'id', 'externalkey', 'collectstatus', 'collecturl', 'createts', 'closets', 'startts',
            'modifyts', 'sessioncount', 'selfurl', 'surveyurl', 'answerurl', 'senddistributionmailurl', 'sendremindermailurl'
        ];

        // create
        $id = $this->post(route('entities.preview'), [
            'form_params' => [
                    'email' => 'test@syspons.com', // string
                    's_2' => 3333, // double
                    's_5' => 'laufende Bewerbung', // single
                    's_7' => 'Georgien' // multiple
                ]
            ])
            ->assertStatus(200)
            ->assertJsonStructure($respondentStructure)->json()['id'];
        $this->assertDatabaseMissing('entities', [
            'respondentid' => $id,
            'survey' => 1325978,
            //'created' => '2021-09-02 18:49:08',
            //'modified' => '2021-10-18 16:42:00',
            'email' => 'test@syspons.com',
            's_2' => 3333,
            's_5' => 1,
            's_7' => 1,
        ]);
    }

    public function test_distribution_route(): void
    {
        $distributionEntity = DistributionEntity::create([
            'name' => fake()->name,
            'year' => fake()->year,
        ]);
        $this->get(route('distribution_entities.preview', ['distribution_entity' => $distributionEntity->id]))
            ->assertRedirect();
    }
}
