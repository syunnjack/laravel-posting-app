<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Post::class;


    public function definition(): array
    {
        return [
            'title' => $this->faker->realText(20), // 最大20文字くらい
            'content' => $this->faker->realText(200), // 最大200文字ほど
            'user_id' => 2, // 実在するID or 新規作成

            //
        ];
    }
}
