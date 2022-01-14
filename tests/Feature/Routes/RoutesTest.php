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
            'entities.update',
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

    public function test_store_update_delete_route(): void
    {
        $respondentStructure = [
            'id', 'externalkey', 'collectstatus', 'collecturl', 'createts', 'closets', 'startts',
            'modifyts', 'sessioncount', 'selfurl', 'surveyurl', 'answerurl', 'senddistributionmailurl', 'sendremindermailurl'
        ];

        // create
        $id = $this->post(route('entities.store'), [
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
        $this->put(route('entities.update', [
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
    
    public function test_import_route(): void
    {
        $id = ['respondentid' => 841931211];
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
            'respondentid' => 841931211,
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
            'respondentid' => 841931211,
            's_2' => 2020,
        ]);
        $this->assertDatabaseMissing('entities_long', [
            'respondent_id' => 841931211,
            'variableName' => 's_2',
            'value_double' => 2020,
        ]);
        $this->assertDatabaseHas('entities', [
            'respondentid' => 841931211,
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
            'respondentid' => '825478429.0',
            'organization' => 269318,
            'statinternal_1' => 1,
            'statinternal_2' => 0,
            'statinternal_3' => 0,
            'statinternal_4' => 0,
            'statinternal_5' => 0,
            'statinternal_6' => 1,
            'statinternal_7' => 1,
            'statinternal_8' => 1,
            'statinternal_9' => 0,
            'statinternal_10' => 0,
            'statinternal_11' => 0,
            'statinternal_12' => 0,
            'statinternal_13' => 1,
            'statinternal_14' => 1,
            'statinternal_15' => 0,
            'statinternal_16' => 0,
            'statinternal_17' => 0,
            'statinternal_18' => 0,
            'statinternal_19' => 0,
            'statinternal_20' => 0,
            'created' => '2021-09-02 18:49:08',
            'modified' => '2021-10-18 16:42:00',
            'closetime' => '2021-09-02 18:52:40',
            'starttime' => '2021-09-02 18:50:24',
            'difftime' => 135.407,
            'responsecollectsessions' => '2.0',
            'numberofreturnedmail' => '0.0',
            'importgroup' => null,
            'distributionschedule' => null,
            'email' => 'henrike.junge@syspons.com',
            'digitaldistributionstatus' => 1,
            'digitaldistributionerrormessage' => null,
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
            'social_entrepreneur' => null,
            'inclusion' => null,
            'number_jobs' => null,
            'still_active' => null,
            'best_practice' => null,
            'validation' => 1,
            'lang' => 2,
            'statcompletion_1' => 1,
            'statcompletion_2' => 1,
            'statcompletion_3' => 0,
            'statcreation_1' => 1,
            'statcreation_2' => 0,
            'statcreation_3' => 0,
            'statcreation_4' => 0,
            'statcreation_5' => 0,
            'statcreation_6' => 0,
            'statcreation_7' => 0,
            'statdistribution_1' => 0,
            'statdistribution_2' => 0,
            'statdistribution_3' => 1,
            'statdistribution_4' => 0,
            'statdistribution_5' => 0,
            'statsource_1' => 1,
            'statsource_2' => 1,
            'statsource_3' => 0,
            'statsource_4' => 0,
            'statsource_5' => 0,
            'statsource_6' => 0,
            'statcollect_1' => 0,
            'statcollect_2' => 0,
            'statcollect_3' => 1,
            'statcollect_4' => 0,
            'statoverall_1' => 0,
            'statoverall_2' => 0,
            'statoverall_3' => 0,
            'statoverall_4' => 1,
            'statoverall_5' => 0,
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
            'respondentid' => '825478429.0',
            'organization' => 'GIZ - PMD FMD M&Q Tool',
            'statinternal_1 - E-Mail gesendet' => 'Ausgewählt',
            'statinternal_2 - Fragebogen gedruckt' => 'Nicht ausgewählt',
            'statinternal_3 - Hintergrunddaten für Serienbrief exportiert' => 'Nicht ausgewählt',
            'statinternal_4 - Über Link verteilt' => 'Nicht ausgewählt',
            'statinternal_5 - Importierte Antworten' => 'Nicht ausgewählt',
            'statinternal_6 - Es wurden einige Fragen beantwortet.' => 'Ausgewählt',
            'statinternal_7 - Letzte Seite, die dem Teilnehmer angezeigt wurde' => 'Ausgewählt',
            'statinternal_8 - Vom Administrator erstellt' => 'Ausgewählt',
            'statinternal_9 - Durch Import erstellt' => 'Nicht ausgewählt',
            'statinternal_10 - Erstellt vom Benutzer des Eingabemoduls' => 'Nicht ausgewählt',
            'statinternal_11 - Vom Teilnehmer erstellt' => 'Nicht ausgewählt',
            'statinternal_12 - Über Webdienst erstellt' => 'Nicht ausgewählt',
            'statinternal_13 - Vom Teilnehmer eingegebene Antworten' => 'Ausgewählt',
            'statinternal_14 - Vom Administrator eingegebene Antworten' => 'Ausgewählt',
            'statinternal_15 - Über Interview eingegebene Antworten' => 'Nicht ausgewählt',
            'statinternal_16 - Über Webdienst erfasste Antworten' => 'Nicht ausgewählt',
            'statinternal_17 - Vom Interviewer geschlossener Teilnehmer' => 'Nicht ausgewählt',
            'statinternal_18 - Letzte Seite, die dem Interviewer angezeigt wurde' => 'Nicht ausgewählt',
            'statinternal_19 - Abgelehnt durch zurückgesendete Mail' => 'Nicht ausgewählt',
            'statinternal_20 - Durch Import vom Panel erstellt' => 'Nicht ausgewählt',
            'created' => '2021-09-02 18:49:08',
            'modified' => '2021-10-18 16:42:00',
            'closetime' => '2021-09-02 18:52:40',
            'starttime' => '2021-09-02 18:50:24',
            'difftime' => 135.407,
            'responsecollectsessions' => '2.0',
            'numberofreturnedmail' => '0.0',
            'importgroup' => null,
            'distributionschedule' => null,
            'email' => 'henrike.junge@syspons.com',
            'digitaldistributionstatus' => 1,
            'digitaldistributionerrormessage' => null,
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
            'social_entrepreneur' => null,
            'inclusion' => null,
            'number_jobs' => null,
            'still_active' => null,
            'best_practice' => null,
            'validation' => 'ja',
            'lang' => 'Deutsch',
            'statcompletion_1 - Beendet' => 'Ausgewählt',
            'statcompletion_2 - Abgeschlossen' => 'Ausgewählt',
            'statcompletion_3 - Abgelehnt durch zurückgesendete Mail' => 'Nicht ausgewählt',
            'statcreation_1 - Manuell von Administrator erstellt' => 'Ausgewählt',
            'statcreation_2 - Durch Administrator importiert' => 'Nicht ausgewählt',
            'statcreation_3 - Erstellt vom Benutzer des Eingabemoduls' => 'Nicht ausgewählt',
            'statcreation_4 - Von Teilnehmer über Link erstellt' => 'Nicht ausgewählt',
            'statcreation_5 - Über Webdienst erstellt' => 'Nicht ausgewählt',
            'statcreation_6 - Erstellungstyp unbekannt' => 'Nicht ausgewählt',
            'statcreation_7 - Durch Import vom Panel erstellt' => 'Nicht ausgewählt',
            'statdistribution_1 - Serienbrief' => 'Nicht ausgewählt',
            'statdistribution_2 - Ohne' => 'Nicht ausgewählt',
            'statdistribution_3 - E-Mail' => 'Ausgewählt',
            'statdistribution_4 - Papier' => 'Nicht ausgewählt',
            'statdistribution_5 - Link' => 'Nicht ausgewählt',
            'statsource_1 - Vom Teilnehmer eingegebene Antworten' => 'Ausgewählt',
            'statsource_2 - Von einem Administrator eingegebene Antworten' => 'Ausgewählt',
            'statsource_3 - Über Interview erfasste Antworten' => 'Nicht ausgewählt',
            'statsource_4 - Importierte Antworten' => 'Nicht ausgewählt',
            'statsource_5 - Webdienst' => 'Nicht ausgewählt',
            'statsource_6 - Ursprung der Antwort unbekannt' => 'Nicht ausgewählt',
            'statcollect_1 - Ohne' => 'Nicht ausgewählt',
            'statcollect_2 - Teilweise abgeschlossen' => 'Nicht ausgewählt',
            'statcollect_3 - Abgeschlossen' => 'Ausgewählt',
            'statcollect_4 - Abgelehnt' => 'Nicht ausgewählt',
            'statoverall_1 - Neu' => 'Nicht ausgewählt',
            'statoverall_2 - Versendet' => 'Nicht ausgewählt',
            'statoverall_3 - Teilweise abgeschlossen' => 'Nicht ausgewählt',
            'statoverall_4 - Abgeschlossen' => 'Ausgewählt',
            'statoverall_5 - Abgelehnt' => 'Nicht ausgewählt',
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
                'variableName' => 'statinternal_1',
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
            ['questionName' => 'statinternal', 'variableName' => 'statinternal_19', 'subType' => 'Multiple', 'questionText' => 'Basisstatus', 'choiceValue' => 19, 'choiceText' => 'Abgelehnt durch zurückgesendete Mail'],
            ['questionName' => 'modified', 'variableName' => 'modified', 'subType' => 'Date', 'questionText' => 'Geändert', 'choiceValue' => null, 'choiceText' => null],
            ['questionName' => 's_10', 'variableName' => 's_10', 'subType' => 'String', 'questionText' => 'business_idea_description', 'choiceValue' => null, 'choiceText' => null],
            ['questionName' => 'number_jobs', 'variableName' => 'number_jobs', 'subType' => 'Double', 'questionText' => 'number_jobs', 'choiceValue' => null, 'choiceText' => null],
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
            ['variableName' => 'digitaldistributionstatus', 'value' => 1, 'label' => '1'],
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
