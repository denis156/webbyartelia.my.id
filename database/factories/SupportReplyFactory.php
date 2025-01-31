<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Support;
use App\Models\SupportReply;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupportReplyFactory extends Factory
{
    protected $model = SupportReply::class;

    public function definition()
    {
        return [
            'support_id' => Support::factory(),
            'user_id' => User::factory(),
            'message' => fake()->paragraph(),
            'attachment_path' => null,
            'is_internal' => fake()->boolean(20), // 20% chance of being internal
        ];
    }
}
