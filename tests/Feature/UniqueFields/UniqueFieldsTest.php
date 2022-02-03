<?php

namespace berthott\SX\Tests\Feature\UniqueFields;

use Illuminate\Support\Facades\Schema;

class UniqueFieldsTest extends UniqueFieldsTestCase
{
    public function test_generated_unique_fields(): void
    {
        $val = Entity::generateUniqueValue('generated_id');
        $this->assertEquals('GEN005', $val);
    }
    public function test_generated_unique_fields_empty(): void
    {
        $val = EmptyEntity::generateUniqueValue('generated_id');
        $this->assertEquals('', $val);
    }

    public function test_generated_unique_field_params(): void
    {
        $params = Entity::generatedUniqueFieldsParams();
        $this->assertArrayHasKey('form_params', $params);
        $this->assertArrayHasKey('generated_id', $params['form_params']);
        $this->assertEquals('GEN005', $params['form_params']['generated_id']);
    }

    public function test_generated_unique_field_params_empty(): void
    {
        $params = EmptyEntity::generatedUniqueFieldsParams();
        $this->assertArrayHasKey('form_params', $params);
        $this->assertEquals([], $params['form_params']);
    }
}
