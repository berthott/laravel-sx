<?php

namespace berthott\SX\Tests\Unit\Sxable;

use Illuminate\Database\Eloquent\Factories\Factory;

class EntityFactory extends Factory
{
    protected $model = Entity::class;

    public function definition()
    {
        return [
            'name' => $this->faker->firstName(),
        ];
    }
}
