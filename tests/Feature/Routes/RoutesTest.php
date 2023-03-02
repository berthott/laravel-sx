<?php

namespace berthott\SX\Tests\Feature\Routes;

use berthott\SX\Services\SxRespondentService;

class RoutesTest extends RoutesTestCase
{
    public function test_store_update_delete_route(): void
    {
        $respondentStructure = [
            'id', 'externalkey', 'collectstatus', 'collecturl', 'createts', 'closets', 'startts',
            'modifyts', 'sessioncount', 'selfurl', 'surveyurl', 'answerurl', 'senddistributionmailurl', 'sendremindermailurl'
        ];

        // create
        $id = $this->post(route('entities.create_respondent'), [
            'form_params' => [
                    'email' => 'test@syspons.com', // string
                    's_2' => 3333, // double
                    's_5' => 'laufende Bewerbung', // single
                    's_7' => 'Georgien' // multiple
                ]
            ])
            ->assertStatus(200)
            ->assertJsonStructure($respondentStructure)->json()['id'];
        $this->assertDatabaseHas('entities', [
            'respondentid' => $id,
            'survey' => 1325978,
            //'created' => '2021-09-02 18:49:08',
            //'modified' => '2021-10-18 16:42:00',
            'email' => 'test@syspons.com',
            's_2' => 3333,
            's_5' => 1,
            's_7' => 1,
        ]);
        $this->assertDatabaseHas('entities_long', [
            'respondent_id' => $id,
            'variableName' => 's_2',
            'value_double' => 3333
        ]);
        $this->assertDatabaseHas('entities_long', [
            'respondent_id' => $id,
            'variableName' => 's_5',
            'value_single_multiple' => 1
        ]);
        $this->assertDatabaseHas('entities_long', [
            'respondent_id' => $id,
            'variableName' => 's_7',
            'value_single_multiple' => 1
        ]);

        // update
        $this->put(route('entities.update_respondent', [
                'entity' => $id,
                'form_params' => [
                    's_2' => 4444,
                    's_5' => 'teilnehmend mit Gründung',
                    's_7' => 'Indonesien',
                ],
            ]))
            ->assertStatus(200)
            ->assertJsonStructure($respondentStructure);
        $this->assertDatabaseHas('entities', [
            'respondentid' => $id,
            'survey' => 1325978,
            //'created' => '2021-09-02 18:49:08',
            //'modified' => '2021-10-18 16:42:00',
            'email' => 'test@syspons.com',
            's_2' => 4444,
            's_5' => 4,
            's_7' => 4,
        ]);
        $this->assertDatabaseHas('entities_long', [
            'respondent_id' => $id,
            'value_double' => 4444
        ]);
        $this->assertDatabaseHas('entities_long', [
            'respondent_id' => $id,
            'variableName' => 's_5',
            'value_single_multiple' => 4
        ]);
        $this->assertDatabaseHas('entities_long', [
            'respondent_id' => $id,
            'variableName' => 's_7',
            'value_single_multiple' => 4
        ]);

        // delete
        $this->delete(route('entities.destroy', ['entity' => $id]))
            ->assertStatus(200)
            ->assertSeeText('Success');
        $this->assertDatabaseMissing('entities', ['respondentid' => $id]);
        $this->assertDatabaseMissing('entities_long', ['respondent_id' => $id]);
    }

    public function test_store_delete_many_route(): void
    {
        // create
        $id1 = $this->post(route('entities.create_respondent'), [
            'form_params' => [
                    'email' => 'test@syspons.com', // string
                    's_2' => 3333, // double
                    's_5' => 'laufende Bewerbung', // single
                    's_7' => 'Georgien' // multiple
                ]
            ])
            ->assertStatus(200)
            ->json()['id'];
        $id2 = $this->post(route('entities.create_respondent'), [
            'form_params' => [
                    'email' => 'test@syspons.com', // string
                    's_2' => 3333, // double
                    's_5' => 'laufende Bewerbung', // single
                    's_7' => 'Georgien' // multiple
                ]
            ])
            ->assertStatus(200)
            ->json()['id'];
        $this->assertDatabaseHas('entities', ['respondentid' => $id1]);
        $this->assertDatabaseHas('entities', ['respondentid' => $id2]);

        // delete_many
        $this->delete(route('entities.destroy_many'), ['ids' => [$id1, $id2]])
            ->assertStatus(200)
            ->assertJson([
                $id1 => 'Success',
                $id2 => 'Success',
            ]);
        $this->assertDatabaseMissing('entities', ['respondentid' => $id1]);
        $this->assertDatabaseMissing('entities', ['respondentid' => $id2]);
    }

    public function test_sync_route(): void
    {
        // create first
        $id1 = $this->post(route('entities.create_respondent'), [
            'form_params' => [
                    'email' => 'test@syspons.com', // string
                    's_2' => 3333, // double
                    's_5' => 'laufende Bewerbung', // single
                    's_7' => 'Georgien' // multiple
                ]
            ])
            ->assertStatus(200)
            ->json()['id'];

        // update respondent manually without touching our database
        $key1 = (new SxRespondentService($id1))->getRespondent()->externalkey();
        (new SxRespondentService($key1))->updateRespondentAnswers(['form_params' => ['s_2' => 4444]]);

        // create second
        $id2 = $this->post(route('entities.create_respondent'), [
            'form_params' => [
                    'email' => 'test@syspons.com', // string
                    's_2' => 3333, // double
                    's_5' => 'laufende Bewerbung', // single
                    's_7' => 'Georgien' // multiple
                ]
            ])
            ->assertStatus(200)
            ->json()['id'];
        
        // update respondent manually without touching our database
        $key2 = (new SxRespondentService($id2))->getRespondent()->externalkey();
        (new SxRespondentService($key2))->updateRespondentAnswers(['form_params' => ['s_2' => 4444]]);

        $this->assertDatabaseHas('entities', [
            'respondentid' => $id1,
            'statinternal_1' => null,
        ]);
        $this->assertDatabaseHas('entities', [
            'respondentid' => $id2,
            'statinternal_1' => null,
        ]);

        // sync
        $this->post(route('entities.sync'));
        $this->assertDatabaseHas('entities', [
            'respondentid' => $id1,
            'statinternal_1' => 0,
        ]);
        $this->assertDatabaseHas('entities', [
            'respondentid' => $id2,
            'statinternal_1' => 0,
        ]);

        // clean up
        $this->delete(route('entities.destroy_many'), ['ids' => [$id1, $id2]])
            ->assertStatus(200)
            ->assertJson([
                $id1 => 'Success',
                $id2 => 'Success',
            ]);
        $this->assertDatabaseMissing('entities', ['respondentid' => $id1]);
        $this->assertDatabaseMissing('entities', ['respondentid' => $id2]);
    }
}
