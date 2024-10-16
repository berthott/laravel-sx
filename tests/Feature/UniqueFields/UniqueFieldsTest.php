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

    public function test_database_has_unique_column(): void
    {
        $sm = Schema::getConnection()->getDoctrineSchemaManager();
        $indexes = $sm->listTableIndexes('unique_entities');
        $this->assertArrayHasKey('unique_entities_unique_id_unique', $indexes);
    }

    public function test_database_has_indexes(): void
    {
        $sm = Schema::getConnection()->getDoctrineSchemaManager();
        $indexes = $sm->listTableIndexes('unique_entities');
        $this->assertArrayHasKey('unique_entities_statinternal_3_statinternal_4_index', $indexes);
        $this->assertArrayHasKey('unique_entities_statinternal_2_index', $indexes);
    }

    public function test_database_has_foreign_keys(): void
    {
        $sm = Schema::getConnection()->getDoctrineSchemaManager();
        $foreign_keys = $sm->listTableForeignKeys('unique_entities');
        $this->assertCount(1, $foreign_keys);
    }

    public function test_database_casts(): void
    {
        $type = Schema::getColumnType('unique_entities', 'fake');
        $this->assertSame('string', $type);
    }
}
