<?php

namespace berthott\SX\Tests\Feature\BrackgroundVariables;

use berthott\SX\Exports\SxLabeledExport;
use berthott\SX\Exports\SxTableExport;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;

class BrackgroundVariablesTest extends BrackgroundVariablesTestCase
{
    public function test_background_variables(): void
    {
        // create
        $id = intval($this->post(route('entities.create_respondent'), [
            'form_params' => [
                    'email' => 'test@syspons.com',
                    'gender_rel' => 'GG1',
                    's_5' => 'teilnehmend mit Gründung', // neccesary for gender_rel
                ]
            ])
            ->assertStatus(200)->json()['id']);
        $this->assertDatabaseHas('entities', [
            'respondentid' => $id,
            'gender_rel' => 2,
            's_5' => 4,
        ]);

        // check creation
        $this->post(route('entities.sync'))
            ->assertStatus(200)
            ->assertJsonFragment([
                'respondentid' => $id,
                'gender_rel' => 2,
                's_5' => 4,
            ]);

        // update
        $this->put(route('entities.update_respondent', [
                'entity' => $id,
                'form_params' => [
                    'gender_rel' => 'GG2'
                ],
            ]))
            ->assertStatus(200);
        $this->assertDatabaseHas('entities', [
            'respondentid' => $id,
            'gender_rel' => 3,
        ]);


        // check creation
        $this->post(route('entities.sync'))
            ->assertStatus(200)
            ->assertJsonFragment([
                'respondentid' => $id,
                'gender_rel' => 3,
            ]);

        // clean up
        $this->delete(route('entities.destroy', ['entity' => $id]))
            ->assertStatus(200);
        $this->assertDatabaseMissing('entities', ['respondentid' => $id]);
    }
}
