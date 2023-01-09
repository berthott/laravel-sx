<?php

namespace berthott\SX\Tests\Feature\Report;

class ReportTest extends ReportTestCase
{
    public function test_report_all(): void
    {
        $this->get(route('entities.report'))
        ->assertStatus(200)
        ->assertJson([
            ['respondentid' => 825478429],
            ['respondentid' => 825478569],
            ['respondentid' => 825479792],
            ['respondentid' => 834262051],
        ]);
    }

    public function test_report_filtered_one_single(): void
    {
        $this->get(route('entities.report', [
            'filter' => [
                's_5' => 2,
            ]
        ]))
        ->assertStatus(200)
        ->assertJson([
            ['respondentid' => 825478429],
        ])
            ->assertJsonMissing([
            ['respondentid' => 825479792],
            ['respondentid' => 825478569],
            ['respondentid' => 834262051],
        ]);
    }

    public function test_report_filtered_multiple_single(): void
    {
        $this->get(route('entities.report', [
            'filter' => [
                's_5' => [2,3],
            ]
        ]))
        ->assertStatus(200)
        ->assertJson([
            ['respondentid' => 825478429],
            ['respondentid' => 825479792],
        ])
        ->assertJsonMissing([
            ['respondentid' => 825478569],
            ['respondentid' => 834262051],
        ]);
    }

    public function test_report_filtered_one_multiple(): void
    {
        $this->get(route('entities.report', [
            'filter' => [
                'statinternal' => 7,
            ]
        ]))
        ->assertStatus(200)
        ->assertJson([
            ['respondentid' => 825478429],
            ['respondentid' => 825479792],
        ])
        ->assertJsonMissing([
            ['respondentid' => 825478569],
            ['respondentid' => 834262051],
        ]);
    }

    public function test_report_filtered_multiple_multiple(): void
    {
        $this->get(route('entities.report', [
            'filter' => [
                'statinternal' => [4,7],
            ]
        ]))
        ->assertStatus(200)
        ->assertJson([
            ['respondentid' => 825478429],
            ['respondentid' => 825479792],
            ['respondentid' => 834262051],
        ])
        ->assertJsonMissing([
            ['respondentid' => 825478569],
        ]);
    }

    public function test_report_select_field(): void
    {
        $this->get(route('entities.report', [
            'fields' => [
                'entities' => 'respondentid'
            ],
        ]))
        ->assertStatus(200)
        ->assertExactJson([
            ['respondentid' => 825478429],
            ['respondentid' => 825478569],
            ['respondentid' => 825479792],
            ['respondentid' => 834262051],
        ]);
    }
}
