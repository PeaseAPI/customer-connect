<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\StickyNote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StickyNoteFactory extends Factory
{
    protected $model = StickyNote::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'user_id' => User::factory(),
            'note_text' => fake()->sentence(),
            'color' => fake()->randomElement(['#FFC107', '#4CAF50', '#2196F3', '#FF5722', '#9C27B0', '#FFFFFF']),
            'added_by' => User::factory(),
        ];
    }
}
