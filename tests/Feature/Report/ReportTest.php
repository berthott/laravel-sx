<?php

namespace berthott\SX\Tests\Feature\Report;

class ReportTest extends ReportTestCase
{
    public function test_report_all(): void
    {
        $this->get(route('entities.report'))
        ->assertStatus(200)
        ->assertJson([
            'startTime' => [
                'type' => 'Date',
                'question' => 'Beginn',
                'answers' => [
                    '2021-09-02 18:50:24',
                    '2021-09-02 19:33:06',
                ],
                'num' => 4,
                'numValid' => 2,
                'numInvalid' => 2,
            ],
            's_2' => [
                'type' => 'Double',
                'question' => 'year',
                'answers' => [2021, 2020],
                'average' => 2020.5,
                'num' => 4,
                'numValid' => 2,
                'numInvalid' => 2,
            ],
            's_5' => [
                'type' => 'Single',
                'question' => 'status',
                'labels' => [
                    1 => 'laufende Bewerbung',
                    2 => 'abgelehnte Bewerbung',
                    3 => 'teilnehmend ohne Gründung',
                    4 => 'teilnehmend mit Gründung',
                ],
                'answers' => [2, 3],
                'answersPercent' => [
                    1 => 0,
                    2 => 50,
                    3 => 50,
                    4 => 0,
                ],
                'average' => 2.5,
                'num' => 4,
                'numValid' => 2,
                'numInvalid' => 2,
            ],
            's_10' => [
                'type' => 'String',
                'question' => 'business_idea_description',
                'answers' => [
                    'Frauenhaus in Georgien im ländlichen Gebiet',
                    'Eine kurze Beschreibung der Testidee',
                ],
                'num' => 4,
                'numValid' => 2,
                'numInvalid' => 2,
            ],
            's_11' => [
                'type' => 'Multiple',
                'question' => 'SDG',
                'labels' => [
                    1 => '01 - no poverty',
                    2 => '02 - zero hunger',
                    3 => '03 - good health and well-being',
                    4 => '04 - quality education',
                    5 => '05 - gender equality',
                    6 => '06 - clean water and sanitation',
                    7 => '07 - affordable and clean energy',
                    8 => '08 - decent work and economic growth',
                    9 => '09 - industry, innovation and infrastructure',
                    10 => '10 - reduced inequalities',
                    11 => '11 - sustainable cities and communities',
                    12 => '12 - responsible consumption and  production',
                    13 => '13 - climate action',
                    14 => '14 - life below water',
                    15 => '15 - life on land',
                    16 => '16 - peace, justice and strong institutions',
                    17 => '17 - partnerships for the goals',
                ],
                'answers' => [
                    [3, 5],
                    [6, 15],
                ],
                'answersPercent' => [
                    1 => 0,
                    2 => 0,
                    3 => 25, // TODO: Clarify if this should be of all or of valid answers
                    4 => 0,
                    5 => 25,
                    6 => 25,
                    7 => 0,
                    8 => 0,
                    9 => 0,
                    10 => 0,
                    11 => 0,
                    12 => 0,
                    13 => 0,
                    14 => 0,
                    15 => 25,
                    16 => 0,
                    17 => 0,
                ],
                'num' => 4,
                'numValid' => 2,
                'numInvalid' => 2,
            ],
        ]);
    }

    public function test_report_select_field(): void
    {
        $this->get(route('entities.report', [
            'fields' => [
                'entities' => 's_5'
            ],
        ]))
        ->assertStatus(200)
        ->assertJsonStructure([
            's_5',
        ])
        ->assertJsonMissing([
            'startTime',
            '2',
            '10',
            '11',
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
            'startTime' => [
                'type' => 'Date',
                'question' => 'Beginn',
                'answers' => [
                    '2021-09-02 18:50:24',
                ],
                'num' => 1,
                'numValid' => 1,
                'numInvalid' => 0,
            ],
            's_2' => [
                'type' => 'Double',
                'question' => 'year',
                'answers' => [2021],
                'average' => 2021,
                'num' => 1,
                'numValid' => 1,
                'numInvalid' => 0,
            ],
            's_5' => [
                'type' => 'Single',
                'question' => 'status',
                'answers' => [2],
                'answersPercent' => [
                    1 => 0,
                    2 => 100,
                    3 => 0,
                    4 => 0,
                ],
                'average' => 2,
                'num' => 1,
                'numValid' => 1,
                'numInvalid' => 0,
            ],
            's_10' => [
                'type' => 'String',
                'question' => 'business_idea_description',
                'answers' => [
                    'Frauenhaus in Georgien im ländlichen Gebiet',
                ],
                'num' => 1,
                'numValid' => 1,
                'numInvalid' => 0,
            ],
            's_11' => [
                'type' => 'Multiple',
                'question' => 'SDG',
                'answers' => [
                    [3, 5],
                ],
                'answersPercent' => [
                    1 => 0,
                    2 => 0,
                    3 => 100, // TODO: Clarify if this should be of all or of valid answers
                    4 => 0,
                    5 => 100,
                    6 => 0,
                    7 => 0,
                    8 => 0,
                    9 => 0,
                    10 => 0,
                    11 => 0,
                    12 => 0,
                    13 => 0,
                    14 => 0,
                    15 => 0,
                    16 => 0,
                    17 => 0,
                ],
                'num' => 1,
                'numValid' => 1,
                'numInvalid' => 0,
            ],
        ]);
        /* ->assertJson([
            ['respondentid' => 825478429],
        ])
            ->assertJsonMissing([
            ['respondentid' => 825479792],
            ['respondentid' => 825478569],
            ['respondentid' => 834262051],
        ]); */
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
            'startTime' => [
                'type' => 'Date',
                'question' => 'Beginn',
                'answers' => [
                    '2021-09-02 18:50:24',
                    '2021-09-02 19:33:06',
                ],
                'num' => 2,
                'numValid' => 2,
                'numInvalid' => 0,
            ],
            's_2' => [
                'type' => 'Double',
                'question' => 'year',
                'answers' => [2021, 2020],
                'average' => 2020.5,
                'num' => 2,
                'numValid' => 2,
                'numInvalid' => 0,
            ],
            's_5' => [
                'type' => 'Single',
                'question' => 'status',
                'answers' => [2, 3],
                'answersPercent' => [
                    1 => 0,
                    2 => 50,
                    3 => 50,
                    4 => 0,
                ],
                'average' => 2.5,
                'num' => 2,
                'numValid' => 2,
                'numInvalid' => 0,
            ],
            's_10' => [
                'type' => 'String',
                'question' => 'business_idea_description',
                'answers' => [
                    'Frauenhaus in Georgien im ländlichen Gebiet',
                    'Eine kurze Beschreibung der Testidee',
                ],
                'num' => 2,
                'numValid' => 2,
                'numInvalid' => 0,
            ],
            's_11' => [
                'type' => 'Multiple',
                'question' => 'SDG',
                'answers' => [
                    [3, 5],
                    [6, 15],
                ],
                'answersPercent' => [
                    1 => 0,
                    2 => 0,
                    3 => 50, // TODO: Clarify if this should be of all or of valid answers
                    4 => 0,
                    5 => 50,
                    6 => 50,
                    7 => 0,
                    8 => 0,
                    9 => 0,
                    10 => 0,
                    11 => 0,
                    12 => 0,
                    13 => 0,
                    14 => 0,
                    15 => 50,
                    16 => 0,
                    17 => 0,
                ],
                'num' => 2,
                'numValid' => 2,
                'numInvalid' => 0,
            ],
        ]);
        /* ->assertStatus(200)
        ->assertJson([
            ['respondentid' => 825478429],
            ['respondentid' => 825479792],
        ])
        ->assertJsonMissing([
            ['respondentid' => 825478569],
            ['respondentid' => 834262051],
        ]); */
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
            'startTime' => [
                'type' => 'Date',
                'question' => 'Beginn',
                'answers' => [
                    '2021-09-02 18:50:24',
                    '2021-09-02 19:33:06',
                ],
                'num' => 2,
                'numValid' => 2,
                'numInvalid' => 0,
            ],
            's_2' => [
                'type' => 'Double',
                'question' => 'year',
                'answers' => [2021, 2020],
                'average' => 2020.5,
                'num' => 2,
                'numValid' => 2,
                'numInvalid' => 0,
            ],
            's_5' => [
                'type' => 'Single',
                'question' => 'status',
                'answers' => [2, 3],
                'answersPercent' => [
                    1 => 0,
                    2 => 50,
                    3 => 50,
                    4 => 0,
                ],
                'average' => 2.5,
                'num' => 2,
                'numValid' => 2,
                'numInvalid' => 0,
            ],
            's_10' => [
                'type' => 'String',
                'question' => 'business_idea_description',
                'answers' => [
                    'Frauenhaus in Georgien im ländlichen Gebiet',
                    'Eine kurze Beschreibung der Testidee',
                ],
                'num' => 2,
                'numValid' => 2,
                'numInvalid' => 0,
            ],
            's_11' => [
                'type' => 'Multiple',
                'question' => 'SDG',
                'answers' => [
                    [3, 5],
                    [6, 15],
                ],
                'answersPercent' => [
                    1 => 0,
                    2 => 0,
                    3 => 50, // TODO: Clarify if this should be of all or of valid answers
                    4 => 0,
                    5 => 50,
                    6 => 50,
                    7 => 0,
                    8 => 0,
                    9 => 0,
                    10 => 0,
                    11 => 0,
                    12 => 0,
                    13 => 0,
                    14 => 0,
                    15 => 50,
                    16 => 0,
                    17 => 0,
                ],
                'num' => 2,
                'numValid' => 2,
                'numInvalid' => 0,
            ],
        ]);
        /* ->assertStatus(200)
        ->assertJson([
            ['respondentid' => 825478429],
            ['respondentid' => 825479792],
        ])
        ->assertJsonMissing([
            ['respondentid' => 825478569],
            ['respondentid' => 834262051],
        ]); */
    }

    public function test_report_filtered_multiple_multiple(): void
    {
        $this->get(route('entities.report', [
            'filter' => [
                'statinternal' => [4,7],
            ]
        ]))
        ->assertStatus(200)
        ->assertStatus(200)
        ->assertJson([
            'startTime' => [
                'type' => 'Date',
                'question' => 'Beginn',
                'answers' => [
                    '2021-09-02 18:50:24',
                    '2021-09-02 19:33:06',
                ],
                'num' => 3,
                'numValid' => 2,
                'numInvalid' => 1,
            ],
            's_2' => [
                'type' => 'Double',
                'question' => 'year',
                'answers' => [2021, 2020],
                'average' => 2020.5,
                'num' => 3,
                'numValid' => 2,
                'numInvalid' => 1,
            ],
            's_5' => [
                'type' => 'Single',
                'question' => 'status',
                'answers' => [2, 3],
                'answersPercent' => [
                    1 => 0,
                    2 => 50,
                    3 => 50,
                    4 => 0,
                ],
                'average' => 2.5,
                'num' => 3,
                'numValid' => 2,
                'numInvalid' => 1,
            ],
            's_10' => [
                'type' => 'String',
                'question' => 'business_idea_description',
                'answers' => [
                    'Frauenhaus in Georgien im ländlichen Gebiet',
                    'Eine kurze Beschreibung der Testidee',
                ],
                'num' => 3,
                'numValid' => 2,
                'numInvalid' => 1,
            ],
            's_11' => [
                'type' => 'Multiple',
                'question' => 'SDG',
                'answers' => [
                    [3, 5],
                    [6, 15],
                ],
                'answersPercent' => [
                    1 => 0,
                    2 => 0,
                    3 => 33.33, // TODO: Clarify if this should be of all or of valid answers
                    4 => 0,
                    5 => 33.33,
                    6 => 33.33,
                    7 => 0,
                    8 => 0,
                    9 => 0,
                    10 => 0,
                    11 => 0,
                    12 => 0,
                    13 => 0,
                    14 => 0,
                    15 => 33.33,
                    16 => 0,
                    17 => 0,
                ],
                'num' => 3,
                'numValid' => 2,
                'numInvalid' => 1,
            ],
        ]);
        /* ->assertJson([
            ['respondentid' => 825478429],
            ['respondentid' => 825479792],
            ['respondentid' => 834262051],
        ])
        ->assertJsonMissing([
            ['respondentid' => 825478569],
        ]); */
    }
}
