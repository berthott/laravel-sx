<?php

namespace berthott\SX\Tests\Feature\MultiLanguage;

class MultiLanguageMockedTest extends MultiLanguageMockedTestCase
{
    public function test_labels_route_validation(): void
    {
        $this->get(route('entities.labels', [ 'lang' => 'de' ]))
            ->assertSuccessful();
        $this->get(route('entities.labels', [ 'lang' => 'en' ]))
            ->assertSuccessful();
        $this->get(route('entities.labels', [ 'lang' => 'fr' ]))
            ->assertJsonValidationErrorFor('lang');
    }

    public function test_labels_route(): void
    {
        $this->get(route('entities.labels'))
            ->assertSuccessful()
            ->assertJsonFragment([
                'variableName' => 's_5',
                'value' => 1,
                'label' => 'laufende Bewerbung',
            ]);
        $this->get(route('entities.labels', ['lang' => 'de']))
            ->assertSuccessful()
            ->assertJsonFragment([
                'variableName' => 's_5',
                'value' => 1,
                'label' => 'laufende Bewerbung',
            ]);
        $this->get(route('entities.labels', ['lang' => 'en']))
            ->assertSuccessful()
            ->assertJsonFragment([
                'variableName' => 's_5',
                'value' => 1,
                'label' => 'running application',
            ]);
    }

    public function test_labels_route_labeled(): void
    {
        $this->get(route('entities.labels', [ 'labeled' => true ]))
            ->assertSuccessful()
            ->assertJsonFragment([
                'variableName' => 'statinternal_1 - E-Mail gesendet',
                'value' => 0,
                'label' => 'Nicht ausgewählt',
            ]);
        $this->get(route('entities.labels', [ 'labeled' => true, 'lang' => 'de' ]))
            ->assertSuccessful()
            ->assertJsonFragment([
                'variableName' => 'statinternal_1 - E-Mail gesendet',
                'value' => 0,
                'label' => 'Nicht ausgewählt',
            ]);
        $this->get(route('entities.labels', [ 'labeled' => true, 'lang' => 'en' ]))
            ->assertSuccessful()
            ->assertJsonFragment([
                'variableName' => 'statinternal_1 - email sent',
                'value' => 0,
                'label' => 'Not selected',
            ]);
    }

    public function test_structure_route_validation(): void
    {
        $this->get(route('entities.structure', [ 'lang' => 'de' ]))
            ->assertSuccessful();
        $this->get(route('entities.structure', [ 'lang' => 'en' ]))
            ->assertSuccessful();
        $this->get(route('entities.structure', [ 'lang' => 'fr' ]))
            ->assertJsonValidationErrorFor('lang');
    }
    
    public function test_structure_route_labeled(): void
    {
        $this->get(route('entities.structure', [
            'labeled' => true
        ]))
            ->assertSuccessful()
            ->assertJsonFragment(['variableName' => 'survey', 'subType' => 'Single'])
            ->assertJsonFragment(['variableName' => 'respondentid', 'subType' => 'Double'])
            ->assertJsonFragment(['variableName' => 'statinternal_1 - E-Mail gesendet', 'subType' => 'Multiple'])
            ->assertJsonFragment(['variableName' => 'created', 'subType' => 'Date']);
        $this->get(route('entities.structure', [
            'labeled' => true,
            'lang' => 'de',
        ]))
            ->assertSuccessful()
            ->assertJsonFragment(['variableName' => 'survey', 'subType' => 'Single'])
            ->assertJsonFragment(['variableName' => 'respondentid', 'subType' => 'Double'])
            ->assertJsonFragment(['variableName' => 'statinternal_1 - E-Mail gesendet', 'subType' => 'Multiple'])
            ->assertJsonFragment(['variableName' => 'created', 'subType' => 'Date']);
        $this->get(route('entities.structure', [
            'labeled' => true,
            'lang' => 'en',
        ]))
            ->assertSuccessful()
            ->assertJsonFragment(['variableName' => 'survey', 'subType' => 'Single'])
            ->assertJsonFragment(['variableName' => 'respondentid', 'subType' => 'Double'])
            ->assertJsonFragment(['variableName' => 'statinternal_1 - email sent', 'subType' => 'Multiple'])
            ->assertJsonFragment(['variableName' => 'created', 'subType' => 'Date']);
    }

    public function test_report_route_validation(): void
    {
        $this->get(route('entities.report', [ 'lang' => 'de' ]))
            ->assertSuccessful();
        $this->get(route('entities.report', [ 'lang' => 'en' ]))
            ->assertSuccessful();
        $this->get(route('entities.report', [ 'lang' => 'fr' ]))
            ->assertJsonValidationErrorFor('lang');
    }

    public function test_report_route(): void
    {
        $this->get(route('entities.report'))
            ->assertSuccessful()
            ->assertJsonFragment([
                's_2' => [
                    'type' => 'Double',
                    'question' => 'Jahr',
                    'answers' => [2021, 2020],
                    'average' => 2020.5,
                    'num' => 4,
                    'numValid' => 2,
                    'numInvalid' => 2,
                ],
            ]);
        $this->get(route('entities.report', [ 'lang' => 'de' ]))
            ->assertSuccessful()
            ->assertJsonFragment([
                's_2' => [
                    'type' => 'Double',
                    'question' => 'Jahr',
                    'answers' => [2021, 2020],
                    'average' => 2020.5,
                    'num' => 4,
                    'numValid' => 2,
                    'numInvalid' => 2,
                ],
            ]);
        $this->get(route('entities.report', [ 'lang' => 'en' ]))
            ->assertSuccessful()
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
            ]);
    }
}
