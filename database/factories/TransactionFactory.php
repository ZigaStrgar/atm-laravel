<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'country' => $this->faker->countryCode
        ];
    }

    public function deposit()
    {
        return $this->state(function (array $attributes) {
            return [
                'amount' => random_int(1, 500),
                'type' => 'deposit'
            ];
        });
    }

    public function withdraw()
    {
        return $this->state(function (array $attributes) {
            return [
                'amount' => random_int(-500, -1),
                'type' => 'deposit'
            ];
        });
    }
}
