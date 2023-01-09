<?php

namespace berthott\SX\Tests\Feature\Routes;

use Illuminate\Support\Facades\Route;

class RoutesMockedTest extends RoutesMockedTestCase
{
    public function test_routes_exist(): void
    {
        $expectedRoutes = [
            'entities.index',
            'entities.show',
            'entities.create_respondent',
            'entities.update_respondent',
            'entities.destroy',
            
            'entities.report',
            'entities.show_respondent',
            'entities.structure',
            'entities.sync',
            'entities.export',
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
                [
                    'respondentid' => 825478429,
                    's_12' => 2
                ],
                ['respondentid' => 825478569],
                ['respondentid' => 825479792],
                ['respondentid' => 834262051],
            ]);
        
        $this->assertDatabaseCount('entities', 4);
    }

    public function test_index_route_labeled(): void
    {
        $this->get(route('entities.index', [
            'labeled' => true
        ]))
            ->assertStatus(200)
            ->assertJson([
                [
                    'respondentid' => 825478429,
                    's_12' => 'Demokratie, Zivilgesellschaft und öffentliche Verwaltung'
                ],
                ['respondentid' => 825478569],
                ['respondentid' => 825479792],
                ['respondentid' => 834262051],
            ]);
    }

    public function test_show_route(): void
    {
        $this->get(route('entities.show', ['entity' => 825478429]))
            ->assertStatus(200)
            ->assertJson([
                'respondentid' => 825478429,
                'email' => 'henrike.junge@syspons.com',
            ]);
    }

    public function test_delete_many_validation(): void
    {
        $this->delete(route('entities.destroy_many'))
            ->assertStatus(422)
            ->assertJsonValidationErrors('ids');
        $this->delete(route('entities.destroy_many'), ['ids' => [12345]])
            ->assertStatus(422)
            ->assertJsonValidationErrors('ids.0');
    }

    public function test_store_route_validation(): void
    {
        $this->post(route('entities.create_respondent'))
            ->assertStatus(422)
            ->assertJsonValidationErrors('form_params.email');
    }

    public function test_respondent_route(): void
    {
        $this->get(route('entities.show_respondent', ['entity' => 825478429]))
            ->assertStatus(200)
            ->assertJson([
                'id' => 825478429,
                'externalkey' => 'TYCAN7PPW33U',
                'collectstatus' => 'completed',
                'collecturl' => 'http://www.survey-xact.dk/answer?key=TYCAN7PPW33U',
                'createts' => '2021-09-02 18:49:08',
                'closets' => '2021-09-02 18:52:40',
                'startts' => '2021-09-02 18:50:24',
                'modifyts' => '2021-10-18 16:42:00',
                'sessioncount' => '2',
                'selfurl' => 'https://rest.survey-xact.dk/rest/respondents/TYCAN7PPW33U',
                'surveyurl' => 'https://rest.survey-xact.dk/rest/surveys/1325978',
                'answerurl' => 'https://rest.survey-xact.dk/rest/respondents/TYCAN7PPW33U/answer',
                'senddistributionmailurl' => 'https://rest.survey-xact.dk/rest/respondents/TYCAN7PPW33U/sendmail/DistributionByMail',
                'sendremindermailurl' => 'https://rest.survey-xact.dk/rest/respondents/TYCAN7PPW33U/sendmail/ReminderEmail',
            ]);
    }

    public function test_structure_route(): void
    {
        $this->get(route('entities.structure'))
            ->assertStatus(200)
            ->assertJsonFragment(['variableName' => 'survey', 'subType' => 'Single'])
            ->assertJsonFragment(['variableName' => 'respondentid', 'subType' => 'Double'])
            ->assertJsonFragment(['variableName' => 'statinternal_1', 'subType' => 'Multiple'])
            ->assertJsonFragment(['variableName' => 'created', 'subType' => 'Date']);
    }
    
    public function test_structure_route_labeled(): void
    {
        $this->get(route('entities.structure', [
            'labeled' => true
        ]))
            ->assertStatus(200)
            ->assertJsonFragment(['variableName' => 'survey', 'subType' => 'Single'])
            ->assertJsonFragment(['variableName' => 'respondentid', 'subType' => 'Double'])
            ->assertJsonFragment(['variableName' => 'statinternal_1 - E-Mail gesendet', 'subType' => 'Multiple'])
            ->assertJsonFragment(['variableName' => 'created', 'subType' => 'Date']);
    }

    public function test_labels_route(): void
    {
        $this->get(route('entities.labels'))
            ->assertStatus(200)
            ->assertJsonFragment([
                'variableName' => 'survey',
                'value' => '1325978',
                'label' => 'HF 4 - GfE Applicants/participants',
            ])
            ->assertJsonFragment([
                'variableName' => 'statinternal_1',
                'value' => '0',
                'label' => 'Nicht ausgewählt',
            ])
            ->assertJsonFragment([
                'variableName' => 'statinternal_1',
                'value' => '1',
                'label' => 'Ausgewählt',
            ])
            ->assertJsonFragment([
                'variableName' => 's_5',
                'value' => '1',
                'label' => 'laufende Bewerbung',
            ])
            ->assertJsonFragment([
                'variableName' => 's_5',
                'value' => '2',
                'label' => 'abgelehnte Bewerbung',
            ])
            ->assertJsonFragment([
                'variableName' => 's_5',
                'value' => '3',
                'label' => 'teilnehmend ohne Gründung',
            ])
            ->assertJsonFragment([
                'variableName' => 's_5',
                'value' => '4',
                'label' => 'teilnehmend mit Gründung',
            ]);
    }

    public function test_labels_route_labeled(): void
    {
        $this->get(route('entities.labels', [ 'labeled' => true ]))
            ->assertStatus(200)
            ->assertJsonFragment([
                'variableName' => 'statinternal_1 - E-Mail gesendet',
                'value' => '0',
                'label' => 'Nicht ausgewählt',
            ]);
    }
}
