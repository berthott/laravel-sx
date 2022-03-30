<?php

namespace berthott\SX\Tests\Feature\UniqueFields;

use Illuminate\Support\Facades\Schema;

class UniqueFieldsTest extends UniqueFieldsTestCase
{
    public function test_generated_unique_fields(): void
    {
        $val = GeneratedUniqueEntity::generateUniqueValue('generated_id');
        $this->assertEquals('GEN005', $val);
    }

    public function test_generated_unique_field_params(): void
    {
        $params = GeneratedUniqueEntity::generatedUniqueFieldsParams();
        $this->assertArrayHasKey('form_params', $params);
        $this->assertArrayHasKey('generated_id', $params['form_params']);
        $this->assertEquals('GEN005', $params['form_params']['generated_id']);
    }
    public function test_generated_unique_fields_empty(): void
    {
        $val = EmptyEntity::generateUniqueValue('generated_id');
        $this->assertEquals('', $val);
    }

    public function test_generated_unique_field_params_empty(): void
    {
        $params = EmptyEntity::generatedUniqueFieldsParams();
        $this->assertArrayHasKey('form_params', $params);
        $this->assertEquals([], $params['form_params']);
    }

    public function test_unique_fields_fails(): void
    {
        $this->post(route('unique_entities.create_respondent'), [
            'form_params' => [
                    'email' => 'test@syspons.com', // string
                    'unique_id' => 'UNIQUE004', // string
                ]
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('form_params.unique_id');
        
        $this->put(route('unique_entities.update_respondent', ['unique_entity' => 834262051]), [
                'form_params' => [
                    'email' => 'test@syspons.com', // string
                    'unique_id' => 'UNIQUE004', // string
                ]
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrorFor('form_params.unique_id');
    }

    public function test_unique_fields_succeeds(): void
    {
        $this->post(route('unique_entities.create_respondent'), [
            'form_params' => [
                    'email' => 'test@syspons.com', // string
                    'unique_id' => 'UNIQUE005', // string
                ]
            ])
            ->assertStatus(200);
        
        $this->assertDatabaseHas('unique_entities', ['unique_id' => 'UNIQUE005']);
    }
}
