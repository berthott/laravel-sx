<?php

namespace berthott\SX\Tests\Feature\Routes;

use berthott\SX\Exports\SxLabeledExport;
use berthott\SX\Exports\SxTableExport;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;

class RoutesTest extends RoutesTestCase
{
    public function test_routes_exist(): void
    {
        $expectedRoutes = [
            'entities.index',
            'entities.show',
            'entities.store',
            'entities.destroy',
            
            'entities.respondent',
            'entities.structure',
            'entities.import',
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
                    'responde' => 825478429,
                    's_12' => 2
                ],
                ['responde' => 825478569],
                ['responde' => 825479792],
                ['responde' => 834262051],
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
                    'responde' => 825478429,
                    's_12' => 'Demokratie, Zivilgesellschaft und öffentliche Verwaltung'
                ],
                ['responde' => 825478569],
                ['responde' => 825479792],
                ['responde' => 834262051],
            ]);
    }

    public function test_show_route(): void
    {
        $this->get(route('entities.show', ['entity' => 825478429]))
            ->assertStatus(200)
            ->assertJson([
                'responde' => 825478429,
                'email' => 'henrike.junge@syspons.com',
            ]);
    }

    public function test_store_and_delete_route(): void
    {
        $id = $this->post(route('entities.store'), [
            'form_params' => [
                    'email' => 'test@syspons.com',
                    's_2' => 3333
                ]
            ])
            ->assertStatus(200)
            ->assertJsonStructure([
                'id', 'externalkey', 'collectstatus', 'collecturl', 'createts', 'closets', 'startts',
                'modifyts', 'sessioncount', 'selfurl', 'surveyurl', 'answerurl', 'senddistributionmailurl', 'sendremindermailurl'
            ])->json()['id'];
        $this->assertDatabaseHas('entities', [
            'responde' => $id,
            'survey' => 1325978,
            //'created' => '2021-09-02 18:49:08',
            //'modified' => '2021-10-18 16:42:00',
            'email' => 'test@syspons.com',
            's_2' => 3333
        ]);
        $this->assertDatabaseHas('entities_long', [
            'respondent_id' => $id,
            'value_double' => 3333
        ]);

        $this->delete(route('entities.destroy', ['entity' => $id]))
            ->assertStatus(200)
            ->assertSeeText('Success');
        $this->assertDatabaseMissing('entities', ['responde' => $id]);
        $this->assertDatabaseMissing('entities_long', ['respondent_id' => $id]);
    }

    public function test_store_route_validation(): void
    {
        $this->post(route('entities.store'))
            ->assertJsonValidationErrors('form_params.email');
    }

    public function test_respondent_route(): void
    {
        $this->get(route('entities.respondent', ['entity' => 825478429]))
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
            ->assertJsonFragment(['variableName' => 'responde', 'subType' => 'Double'])
            ->assertJsonFragment(['variableName' => 'stat_1', 'subType' => 'Multiple'])
            ->assertJsonFragment(['variableName' => 'created', 'subType' => 'Date']);
    }
    
    public function test_structure_route_labeled(): void
    {
        $this->get(route('entities.structure', [
            'labeled' => true
        ]))
            ->assertStatus(200)
            ->assertJsonFragment(['variableName' => 'survey', 'subType' => 'Single'])
            ->assertJsonFragment(['variableName' => 'responde', 'subType' => 'Double'])
            ->assertJsonFragment(['variableName' => 'stat_1 - E-Mail gesendet', 'subType' => 'Multiple'])
            ->assertJsonFragment(['variableName' => 'created', 'subType' => 'Date']);
    }
    
    public function test_import_route(): void
    {
        $id = ['responde' => 841931211];
        $this->assertDatabaseCount('entities', 4);
        $this->assertDatabaseMissing('entities', $id);
        $this->post(route('entities.import'))
            ->assertStatus(200)
            ->assertJson([$id]);
        $this->assertDatabaseCount('entities', 5);
        $this->assertDatabaseHas('entities', $id);
    }

    public function test_import_route_validation(): void
    {
        $this->assertDatabaseCount('entities', 4);
        $this->post(route('entities.import'), ['fresh' => 'yes'])
            ->assertJsonValidationErrors('fresh');
        $this->assertDatabaseCount('entities', 4);
        $this->post(route('entities.import'), ['fresh' => true]);
        $this->assertDatabaseCount('entities', 1);
    }

    public function test_import_route_fresh(): void
    {
        $this->assertDatabaseCount('entities', 4);
        $this->post(route('entities.import'), ['fresh' => true]);
        $this->assertDatabaseCount('entities', 1);
    }

    public function test_import_route_update(): void
    {
        $this->assertDatabaseCount('entities', 4);
        $this->post(route('entities.import'));
        $this->assertDatabaseCount('entities', 5);
        $this->assertDatabaseHas('entities', [
            'responde' => 841931211,
            's_2' => 2020,
        ]);
        $this->assertDatabaseHas('entities_long', [
            'respondent_id' => 841931211,
            'variableName' => 's_2',
            'value_double' => 2020,
        ]);
        $this->post(route('entities.import'));
        $this->assertDatabaseCount('entities', 5);
        $this->assertDatabaseMissing('entities', [
            'responde' => 841931211,
            's_2' => 2020,
        ]);
        $this->assertDatabaseMissing('entities_long', [
            'respondent_id' => 841931211,
            'variableName' => 's_2',
            'value_double' => 2020,
        ]);
        $this->assertDatabaseHas('entities', [
            'responde' => 841931211,
            's_2' => 2021,
        ]);
        $this->assertDatabaseHas('entities_long', [
            'respondent_id' => 841931211,
            'variableName' => 's_2',
            'value_double' => 2021,
        ]);
    }

    public function test_export_wide(): void
    {
        Excel::fake();

        $this->call('GET', route('entities.export'), [
            'table' => 'wide'
        ])->assertStatus(200);
        
        $entity = [
            'id' => 1,
            'survey' => 1325978,
            'responde' => '825478429.0',
            'organiza' => 269318,
            'stat_1' => 1,
            'stat_2' => 0,
            'stat_3' => 0,
            'stat_4' => 0,
            'stat_5' => 0,
            'stat_6' => 1,
            'stat_7' => 1,
            'stat_8' => 1,
            'stat_9' => 0,
            'stat_10' => 0,
            'stat_11' => 0,
            'stat_12' => 0,
            'stat_13' => 1,
            'stat_14' => 1,
            'stat_15' => 0,
            'stat_16' => 0,
            'stat_17' => 0,
            'stat_18' => 0,
            'stat_19' => 0,
            'stat_20' => 0,
            'created' => '2021-09-02 18:49:08',
            'modified' => '2021-10-18 16:42:00',
            'closetim' => '2021-09-02 18:52:40',
            'starttim' => '2021-09-02 18:50:24',
            'difftime' => 135.407,
            'response' => '2.0',
            'numberof' => '0.0',
            'importgr' => null,
            'distribu' => null,
            'email' => 'henrike.junge@syspons.com',
            'digitald' => 1,
            'digit_1' => null,
            's_5' => 2,
            's_2' => '2021.0',
            's_3' => 3,
            'name' => null,
            's_4' => 2,
            's_1' => null,
            's_6' => null,
            's_7' => 1,
            's_14' => 'Female Future',
            's_9' => 1,
            's_15' => 'Frauenhaus in Georgien im ländlichen Gebiet',
            's_10' => 'Frauenhaus in Georgien im ländlichen Gebiet',
            's_11_1' => 0,
            's_11_2' => 0,
            's_11_3' => 1,
            's_11_4' => 0,
            's_11_5' => 1,
            's_11_6' => 0,
            's_11_7' => 0,
            's_11_8' => 0,
            's_11_9' => 0,
            's_11_10' => 0,
            's_11_11' => 0,
            's_11_12' => 0,
            's_11_13' => 0,
            's_11_14' => 0,
            's_11_15' => 0,
            's_11_16' => 0,
            's_11_17' => 0,
            's_12' => 2,
            's_17' => null,
            's_18' => null,
            'social_e' => null,
            'inclusio' => null,
            'number_j' => null,
            'still_ac' => null,
            'best_pra' => null,
            'validati' => 1,
            'lang' => 2,
            'statc_1' => 1,
            'statc_2' => 1,
            'statc_3' => 0,
            'statc_4' => 1,
            'statc_5' => 0,
            'statc_6' => 0,
            'statc_7' => 0,
            'statc_8' => 0,
            'statc_9' => 0,
            'stat_21' => 0,
            'statd_1' => 0,
            'statd_2' => 0,
            'statd_3' => 1,
            'statd_4' => 0,
            'statd_5' => 0,
            'stats_1' => 1,
            'stats_2' => 1,
            'stats_3' => 0,
            'stats_4' => 0,
            'stats_5' => 0,
            'stats_6' => 0,
            'stat_22' => 0,
            'stat_23' => 0,
            'stat_24' => 1,
            'stat_25' => 0,
            'stato_1' => 0,
            'stato_2' => 0,
            'stato_3' => 0,
            'stato_4' => 1,
            'stato_5' => 0,
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ];
        Excel::assertDownloaded('entities.xlsx', function (SxTableExport $export) use ($entity) {
            $excelEntity = json_decode(json_encode($export->collection()[0]), true);
            return empty(array_diff($export->headings(), array_keys($entity))) &&
                empty(array_diff($excelEntity, $entity));
        });
    }

    public function test_export_wide_labeled(): void
    {
        Excel::fake();

        $this->call('GET', route('entities.export', [
            'table' => 'wide_labeled'
        ]))->assertStatus(200);
        
        $entity = [
            'id' => 1,
            'survey' => 'HF 4 - GfE Applicants/participants',
            'responde' => '825478429.0',
            'organiza' => 'GIZ - PMD FMD M&Q Tool',
            'stat_1 - E-Mail gesendet' => 'Ausgewählt',
            'stat_2 - Fragebogen gedruckt' => 'Nicht ausgewählt',
            'stat_3 - Hintergrunddaten für Serienbrief exportiert' => 'Nicht ausgewählt',
            'stat_4 - Über Link verteilt' => 'Nicht ausgewählt',
            'stat_5 - Importierte Antworten' => 'Nicht ausgewählt',
            'stat_6 - Es wurden einige Fragen beantwortet.' => 'Ausgewählt',
            'stat_7 - Letzte Seite, die dem Teilnehmer angezeigt wurde' => 'Ausgewählt',
            'stat_8 - Vom Administrator erstellt' => 'Ausgewählt',
            'stat_9 - Durch Import erstellt' => 'Nicht ausgewählt',
            'stat_10 - Erstellt vom Benutzer des Eingabemoduls' => 'Nicht ausgewählt',
            'stat_11 - Vom Teilnehmer erstellt' => 'Nicht ausgewählt',
            'stat_12 - Über Webdienst erstellt' => 'Nicht ausgewählt',
            'stat_13 - Vom Teilnehmer eingegebene Antworten' => 'Ausgewählt',
            'stat_14 - Vom Administrator eingegebene Antworten' => 'Ausgewählt',
            'stat_15 - Über Interview eingegebene Antworten' => 'Nicht ausgewählt',
            'stat_16 - Über Webdienst erfasste Antworten' => 'Nicht ausgewählt',
            'stat_17 - Vom Interviewer geschlossener Teilnehmer' => 'Nicht ausgewählt',
            'stat_18 - Letzte Seite, die dem Interviewer angezeigt wurde' => 'Nicht ausgewählt',
            'stat_19 - Abgelehnt durch zurückgesendete Mail' => 'Nicht ausgewählt',
            'stat_20 - Durch Import vom Panel erstellt' => 'Nicht ausgewählt',
            'created' => '2021-09-02 18:49:08',
            'modified' => '2021-10-18 16:42:00',
            'closetim' => '2021-09-02 18:52:40',
            'starttim' => '2021-09-02 18:50:24',
            'difftime' => 135.407,
            'response' => '2.0',
            'numberof' => '0.0',
            'importgr' => null,
            'distribu' => null,
            'email' => 'henrike.junge@syspons.com',
            'digitald' => 1,
            'digit_1' => null,
            's_5' => 'abgelehnte Bewerbung',
            's_2' => '2021.0',
            's_3' => 'GfE Klassik',
            'name' => null,
            's_4' => 'weiblich',
            's_1' => null,
            's_6' => null,
            's_7' => 'Georgien',
            's_14' => 'Female Future',
            's_9' => 'Georgien',
            's_15' => 'Frauenhaus in Georgien im ländlichen Gebiet',
            's_10' => 'Frauenhaus in Georgien im ländlichen Gebiet',
            's_11_1 - 01 - no poverty' => 'Nicht ausgewählt',
            's_11_2 - 02 - zero hunger' => 'Nicht ausgewählt',
            's_11_3 - 03 - good health and well-being' => 'Ausgewählt',
            's_11_4 - 04 - quality education' => 'Nicht ausgewählt',
            's_11_5 - 05 - gender equality' => 'Ausgewählt',
            's_11_6 - 06 - clean water and sanitation' => 'Nicht ausgewählt',
            's_11_7 - 07 - affordable and clean energy' => 'Nicht ausgewählt',
            's_11_8 - 08 - decent work and economic growth' => 'Nicht ausgewählt',
            's_11_9 - 09 - industry, innovation and infrastructure' => 'Nicht ausgewählt',
            's_11_10 - 10 - reduced inequalities' => 'Nicht ausgewählt',
            's_11_11 - 11 - sustainable cities and communities' => 'Nicht ausgewählt',
            's_11_12 - 12 - responsible consumption and  production' => 'Nicht ausgewählt',
            's_11_13 - 13 - climate action' => 'Nicht ausgewählt',
            's_11_14 - 14 - life below water' => 'Nicht ausgewählt',
            's_11_15 - 15 - life on land' => 'Nicht ausgewählt',
            's_11_16 - 16 - peace, justice and strong institutions' => 'Nicht ausgewählt',
            's_11_17 - 17 - partnerships for the goals' => 'Nicht ausgewählt',
            's_12' => 'Demokratie, Zivilgesellschaft und öffentliche Verwaltung',
            's_17' => null,
            's_18' => null,
            'social_e' => null,
            'inclusio' => null,
            'number_j' => null,
            'still_ac' => null,
            'best_pra' => null,
            'validati' => 'ja',
            'lang' => 'Deutsch',
            'statc_1 - Beendet' => 'Ausgewählt',
            'statc_2 - Abgeschlossen' => 'Ausgewählt',
            'statc_3 - Abgelehnt durch zurückgesendete Mail' => 'Nicht ausgewählt',
            'statc_4 - Manuell von Administrator erstellt' => 'Ausgewählt',
            'statc_5 - Durch Administrator importiert' => 'Nicht ausgewählt',
            'statc_6 - Erstellt vom Benutzer des Eingabemoduls' => 'Nicht ausgewählt',
            'statc_7 - Von Teilnehmer über Link erstellt' => 'Nicht ausgewählt',
            'statc_8 - Über Webdienst erstellt' => 'Nicht ausgewählt',
            'statc_9 - Erstellungstyp unbekannt' => 'Nicht ausgewählt',
            'stat_21 - Durch Import vom Panel erstellt' => 'Nicht ausgewählt',
            'statd_1 - Serienbrief' => 'Nicht ausgewählt',
            'statd_2 - Ohne' => 'Nicht ausgewählt',
            'statd_3 - E-Mail' => 'Ausgewählt',
            'statd_4 - Papier' => 'Nicht ausgewählt',
            'statd_5 - Link' => 'Nicht ausgewählt',
            'stats_1 - Vom Teilnehmer eingegebene Antworten' => 'Ausgewählt',
            'stats_2 - Von einem Administrator eingegebene Antworten' => 'Ausgewählt',
            'stats_3 - Über Interview erfasste Antworten' => 'Nicht ausgewählt',
            'stats_4 - Importierte Antworten' => 'Nicht ausgewählt',
            'stats_5 - Webdienst' => 'Nicht ausgewählt',
            'stats_6 - Ursprung der Antwort unbekannt' => 'Nicht ausgewählt',
            'stat_22 - Ohne' => 'Nicht ausgewählt',
            'stat_23 - Teilweise abgeschlossen' => 'Nicht ausgewählt',
            'stat_24 - Abgeschlossen' => 'Ausgewählt',
            'stat_25 - Abgelehnt' => 'Nicht ausgewählt',
            'stato_1 - Neu' => 'Nicht ausgewählt',
            'stato_2 - Versendet' => 'Nicht ausgewählt',
            'stato_3 - Teilweise abgeschlossen' => 'Nicht ausgewählt',
            'stato_4 - Abgeschlossen' => 'Ausgewählt',
            'stato_5 - Abgelehnt' => 'Nicht ausgewählt',
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ];
        Excel::assertDownloaded('entities_labeled.xlsx', function (SxLabeledExport $export) use ($entity) {
            $excelEntity = json_decode(json_encode($export->collection()[0]), true);
            return empty(array_diff($export->headings(), array_keys($entity))) &&
                empty(array_diff($excelEntity, $entity));
        });
    }

    public function test_export_long(): void
    {
        Excel::fake();

        $this->call('GET', route('entities.export'), [
            'table' => 'long'
        ])->assertStatus(200);

        $entries = [
            [
                'respondent_id' => '825478429.0',
                'variableName' => 's_14',
                'value_single_multiple' => null,
                'value_string' => 'Female Future',
                'value_double' => null,
                'value_datetime' => null,
            ], [
                'respondent_id' => '825478429.0',
                'variableName' => 'stat_1',
                'value_single_multiple' => 1,
                'value_string' => null,
                'value_double' => null,
                'value_datetime' => null,
            ], [
                'respondent_id' => '825478429.0',
                'variableName' => 'difftime',
                'value_single_multiple' => null,
                'value_string' => null,
                'value_double' => 135.407,
                'value_datetime' => null,
            ], [
                'respondent_id' => '825478429.0',
                'variableName' => 'created',
                'value_single_multiple' => null,
                'value_string' => null,
                'value_double' => null,
                'value_datetime' => '2021-09-02 18:49:08',
            ],
        ];

        Excel::assertDownloaded('entities_long.xlsx', function (SxTableExport $export) use ($entries) {
            $correct = true;
            foreach ($entries as $entry) {
                $exportEntry = $export->collection()->where('variableName', $entry['variableName'])->where('respondent_id', 825478429)->first();
                $diff = array_diff(json_decode(json_encode($exportEntry), true), $entry);
                if (count($diff) !== 3) {
                    $correct = false;
                }
            };
            return $correct;
        });
    }
    
    public function test_export_questions(): void
    {
        Excel::fake();

        $this->call('GET', route('entities.export'), [
            'table' => 'questions'
        ])->assertStatus(200);

        $questions = [
            ['questionName' => 'statinternal', 'variableName' => 'stat_19', 'subType' => 'Multiple', 'questionText' => 'Basisstatus', 'choiceValue' => 19, 'choiceText' => 'Abgelehnt durch zurückgesendete Mail'],
            ['questionName' => 'modified', 'variableName' => 'modified', 'subType' => 'Date', 'questionText' => 'Geändert', 'choiceValue' => null, 'choiceText' => null],
            ['questionName' => 's_10', 'variableName' => 's_10', 'subType' => 'String', 'questionText' => 'business_idea_description', 'choiceValue' => null, 'choiceText' => null],
            ['questionName' => 'number_jobs', 'variableName' => 'number_j', 'subType' => 'Double', 'questionText' => 'number_jobs', 'choiceValue' => null, 'choiceText' => null],
            ['questionName' => 'lang', 'variableName' => 'lang', 'subType' => 'Single', 'questionText' => 'Sprache', 'choiceValue' => null, 'choiceText' => null],
        ];

        Excel::assertDownloaded('entity_questions.xlsx', function (SxTableExport $export) use ($questions) {
            $correct = true;
            foreach ($questions as $question) {
                $exportQuestion = $export->collection()->firstWhere('variableName', $question['variableName']);
                $diff = array_diff(json_decode(json_encode($exportQuestion), true), $question);
                if (count($diff) !== 2) {
                    $correct = false;
                }
            };
            return $correct;
        });
    }
    
    public function test_export_labels(): void
    {
        Excel::fake();

        $this->call('GET', route('entities.export'), [
            'table' => 'labels'
        ])->assertStatus(200);

        $labels = [
            ['variableName' => 'survey', 'value' => 1325978, 'label' => 'HF 4 - GfE Applicants/participants'],
            ['variableName' => 'digitald', 'value' => 1, 'label' => '1'],
            ['variableName' => 's_5', 'value' => 3, 'label' => 'teilnehmend ohne Gründung'],
            ['variableName' => 's_11_1', 'value' => 1, 'label' => 'Ausgewählt'],
            ['variableName' => 's_12', 'value' => 2, 'label' => 'Demokratie, Zivilgesellschaft und öffentliche Verwaltung'],
        ];

        Excel::assertDownloaded('entity_labels.xlsx', function (SxTableExport $export) use ($labels) {
            $correct = true;
            foreach ($labels as $label) {
                $exportLabel = $export->collection()->where('variableName', $label['variableName'])->where('value', $label['value'])->first();
                $diff = array_diff(json_decode(json_encode($exportLabel), true), $label);
                if (count($diff) !== 2) {
                    $correct = false;
                }
            };
            return $correct;
        });
    }

    public function test_export_validation_fails(): void
    {
        $this->call('GET', route('entities.export'), [
            'table' => 'wrong'
        ])->assertJsonValidationErrors('table');
    }
}
