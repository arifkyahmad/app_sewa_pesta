<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nama' => fake()->name(),
            'nomor_telepon' => '08' . fake()->numerify('##########'),
            'alamat' => fake()->streetAddress() . ', ' . fake()->city(),
        ];
    }
}