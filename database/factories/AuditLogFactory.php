<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'file_path' => 'audit-files/'.$this->faker->uuid().'.enc',
            'file_hash' => $this->faker->regexify('[a-f0-9]{64}'),
            'tx_hash' => null,
            'block_number' => null,
            'integrity_status' => 'pending',
            'metadata' => [],
        ];
    }
}
