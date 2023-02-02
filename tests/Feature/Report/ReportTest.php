<?php

namespace berthott\SX\Tests\Feature\Report;

class ReportTest extends ReportTestCase
{
    public function test_report_all(): void
    {
        $this->get(route('entities.report'))
        ->assertStatus(200)
        ->assertJsonFragment([
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
        ])
        ->assertJsonFragment([
            's_2' => [
                'type' => 'Double',
                'question' => 'year',
                'answers' => [2021, 2020],
                'average' => 2020.5,
                'num' => 4,
                'numValid' => 2,
                'numInvalid' => 2,
            ],
        ])
        ->assertJsonFragment([
            'trainer_1' => [
                'type' => 'Single',
                'question' => 'Trainer 1',
                'labels' => [
                    // -7 => 'keine Angabe', // must be missing
                    1 => 'gar nicht zufrieden',
                    2 => 'kaum zufrieden',
                    3 => 'teilweise zufrieden',
                    4 => 'eher zufrieden',
                    5 => 'sehr zufrieden',
                ],
                'answers' => [1, 2],
                'answersCount' => [
                    // -7 => 0, // must be missing
                    1 => 1,
                    2 => 1,
                    3 => 0,
                    4 => 0,
                    5 => 0,
                ],
                'answersPercent' => [
                    // -7 => 0, // must be missing
                    1 => 50,
                    2 => 50,
                    3 => 0,
                    4 => 0,
                    5 => 0,
                ],
                'average' => 1.5,
                'num' => 4,
                'numValid' => 2,
                'numInvalid' => 2,
            ],
        ])
        ->assertJsonFragment([
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
        ])
        ->assertJsonFragment([
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
                'answersCount' => [
                    1 => 0,
                    2 => 0,
                    3 => 1,
                    4 => 0,
                    5 => 1,
                    6 => 1,
                    7 => 0,
                    8 => 0,
                    9 => 0,
                    10 => 0,
                    11 => 0,
                    12 => 0,
                    13 => 0,
                    14 => 0,
                    15 => 1,
                    16 => 0,
                    17 => 0,
                ],
                'answersPercent' => [
                    1 => 0,
                    2 => 0,
                    3 => 25,
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
            'trainer_1' => [
                'type' => 'Single',
                'question' => 'Trainer 1',
                'labels' => [
                    1 => 'gar nicht zufrieden',
                    2 => 'kaum zufrieden',
                    3 => 'teilweise zufrieden',
                    4 => 'eher zufrieden',
                    5 => 'sehr zufrieden',
                ],
                'answers' => [1],
                'answersCount' => [
                    1 => 1,
                    2 => 0,
                    3 => 0,
                    4 => 0,
                ],
                'answersPercent' => [
                    1 => 100,
                    2 => 0,
                    3 => 0,
                    4 => 0,
                ],
                'average' => 1,
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
                    3 => 1,
                    4 => 0,
                    5 => 1,
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
                'answersPercent' => [
                    1 => 0,
                    2 => 0,
                    3 => 100,
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
            'trainer_1' => [
                'type' => 'Single',
                'question' => 'Trainer 1',
                'answers' => [1],
                'answersCount' => [
                    1 => 1,
                    2 => 0,
                    3 => 0,
                    4 => 0,
                ],
                'answersPercent' => [
                    1 => 100,
                    2 => 0,
                    3 => 0,
                    4 => 0,
                ],
                'average' => 1,
                'num' => 2,
                'numValid' => 1,
                'numInvalid' => 1,
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
                'answersCount' => [
                    1 => 0,
                    2 => 0,
                    3 => 1,
                    4 => 0,
                    5 => 1,
                    6 => 1,
                    7 => 0,
                    8 => 0,
                    9 => 0,
                    10 => 0,
                    11 => 0,
                    12 => 0,
                    13 => 0,
                    14 => 0,
                    15 => 1,
                    16 => 0,
                    17 => 0,
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
            'trainer_1' => [
                'type' => 'Single',
                'question' => 'Trainer 1',
                'answers' => [1],
                'answersCount' => [
                    1 => 1,
                    2 => 0,
                    3 => 0,
                    4 => 0,
                ],
                'answersPercent' => [
                    1 => 100,
                    2 => 0,
                    3 => 0,
                    4 => 0,
                ],
                'average' => 1,
                'num' => 2,
                'numValid' => 1,
                'numInvalid' => 1,
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
                'answersCount' => [
                    1 => 0,
                    2 => 0,
                    3 => 1,
                    4 => 0,
                    5 => 1,
                    6 => 1,
                    7 => 0,
                    8 => 0,
                    9 => 0,
                    10 => 0,
                    11 => 0,
                    12 => 0,
                    13 => 0,
                    14 => 0,
                    15 => 1,
                    16 => 0,
                    17 => 0,
                ],
                'answersPercent' => [
                    1 => 0,
                    2 => 0,
                    3 => 50,
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
            'trainer_1' => [
                'type' => 'Single',
                'question' => 'Trainer 1',
                'answers' => [1],
                'answersCount' => [
                    1 => 1,
                    2 => 0,
                    3 => 0,
                    4 => 0,
                ],
                'answersPercent' => [
                    1 => 100,
                    2 => 0,
                    3 => 0,
                    4 => 0,
                ],
                'average' => 1,
                'num' => 3,
                'numValid' => 1,
                'numInvalid' => 2,
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
                'answersCount' => [
                    1 => 0,
                    2 => 0,
                    3 => 1,
                    4 => 0,
                    5 => 1,
                    6 => 1,
                    7 => 0,
                    8 => 0,
                    9 => 0,
                    10 => 0,
                    11 => 0,
                    12 => 0,
                    13 => 0,
                    14 => 0,
                    15 => 1,
                    16 => 0,
                    17 => 0,
                ],
                'answersPercent' => [
                    1 => 0,
                    2 => 0,
                    3 => 33.3,
                    4 => 0,
                    5 => 33.3,
                    6 => 33.3,
                    7 => 0,
                    8 => 0,
                    9 => 0,
                    10 => 0,
                    11 => 0,
                    12 => 0,
                    13 => 0,
                    14 => 0,
                    15 => 33.3,
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

    public function test_no_aggregate(): void
    {
        $this->get(route('entities.report'))
        ->assertStatus(200)
        ->assertJson([
            'trainer_1' => [
                'type' => 'Single',
                'question' => 'Trainer 1',
                'answers' => [1, 2],
                'answersCount' => [
                    1 => 1,
                    2 => 1,
                    3 => 0,
                    4 => 0,
                    5 => 0,
                ],
                'answersPercent' => [
                    1 => 50,
                    2 => 50,
                    3 => 0,
                    4 => 0,
                    5 => 0,
                ],
                'average' => 1.5,
                'num' => 4,
                'numValid' => 2,
                'numInvalid' => 2,
            ],
            'trainer_2' => [
                'type' => 'Single',
                'question' => 'Trainer 2',
                'answers' => [4, 5, 3, 4],
                'answersCount' => [
                    1 => 0,
                    2 => 0,
                    3 => 1,
                    4 => 2,
                    5 => 1,
                ],
                'answersPercent' => [
                    1 => 0,
                    2 => 0,
                    3 => 25,
                    4 => 50,
                    5 => 25,
                ],
                'average' => 4,
                'num' => 4,
                'numValid' => 4,
                'numInvalid' => 0,
            ],
            'trainer_3' => [
                'type' => 'Single',
                'question' => 'Trainer 3',
                'answers' => [5, 5, 5],
                'answersCount' => [
                    1 => 0,
                    2 => 0,
                    3 => 0,
                    4 => 0,
                    5 => 3,
                ],
                'answersPercent' => [
                    1 => 0,
                    2 => 0,
                    3 => 0,
                    4 => 0,
                    5 => 100,
                ],
                'average' => 5,
                'num' => 4,
                'numValid' => 3,
                'numInvalid' => 1,
            ],
        ]);
    }

    public function test_aggregate(): void
    {
        $this->get(route('entities.report', [
            'aggregate' => [
                'trainer' => [
                    'trainer_1',
                    'trainer_2',
                    'trainer_3',
                ],
            ]
        ]))
        ->assertStatus(200)
        ->assertJson([
            'trainer' => [
                'answers' => [1, 4, 5, 2, 5, 5, 3, 5, 4],
                'answersCount' => [
                    1 => 1,
                    2 => 1,
                    3 => 1,
                    4 => 2,
                    5 => 4,
                ],
                'answersPercent' => [
                    1 => 11.1,
                    2 => 11.1,
                    3 => 11.1,
                    4 => 22.2,
                    5 => 44.4,
                ],
                'average' => 3.8,
                'num' => 12,
                'numValid' => 9,
                'numInvalid' => 3,
            ],
        ]);
    }
}
