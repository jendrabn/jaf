<?php

namespace Database\Factories;

use App\Models\Banner;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Banner>
 */
class BannerFactory extends Factory
{
  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    return [
      'image_alt' => fake()->sentence(),
      'url' => fake()->url(),
    ];
  }

  public function hasImage()
  {
    return $this->afterCreating(
      function (Banner $banner) {
        $file = UploadedFile::fake()->image('banner.jpg');
        $banner->addMedia($file)
          ->toMediaCollection('banner_images');
      }
    );
  }
}
