<?php

namespace Database\Factories;

use App\Models\Loan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Loan>
 */
class LoanFactory extends Factory
{

    protected $model = Loan::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'amount' => $this->faker->numberBetween($min = 10, $max = 1000),
            'loan_term' => $this->faker->numberBetween($min = 1, $max = 12),
            'user_id' => function() {
                return User::factory()->create()->id;
            }
        ];
    }
}
