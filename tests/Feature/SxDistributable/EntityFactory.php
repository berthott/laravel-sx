<?php

namespace berthott\SX\Tests\Feature\SxDistributable;

use Illuminate\Database\Eloquent\Factories\Factory;

class EntityFactory extends Factory
{
    protected $model = Entity::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'year' => $this->faker->year,
        ];
    }
}
