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
}
