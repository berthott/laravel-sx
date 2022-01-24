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
        $id = $this->post(route('entities.store'), [
            'form_params' => [
                    'email' => 'test@syspons.com', // string
                    'gender_rel' => 'GG0', // single
                ]
            ])
            ->assertStatus(200)->json()['id'];
        $this->assertDatabaseHas('entities', [
            'respondentid' => $id,
            'survey' => 1325978,
            'email' => 'test@syspons.com',
            'gender_rel' => '1',
        ]);

        // check creation
        /* $this->post(route('entities.import'))
            ->assertStatus(200)
            ->assertJson([
                'respondentid' => $id,
                'survey' => 1325978,
                'email' => 'test@syspons.com',
                'gender_r' => '1',
            ]); */

        // update
        $this->put(route('entities.update', [
                'entity' => $id,
                'form_params' => [
                    'gender_rel' => 'GG1'
                ],
            ]))
            ->assertStatus(200);
        $this->assertDatabaseHas('entities', [
            'respondentid' => $id,
            'survey' => 1325978,
            'email' => 'test@syspons.com',
            'gender_rel' => '2',
        ]);

        // clean up
        $this->delete(route('entities.destroy', ['entity' => $id]))
            ->assertStatus(200);
        $this->assertDatabaseMissing('entities', ['respondentid' => $id]);
    }
}
