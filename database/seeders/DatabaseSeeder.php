<?php

namespace Database\Seeders;

use App\Actions\CreateArgument;
use App\Actions\SendUserMessage;
use App\Models\ArgumentComment;
use App\Models\Rfc;
use App\Models\User;
use App\Models\VerificationRequest;
use App\Models\VerificationRequestStatus;
use App\Models\VoteType;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $mainUser = User::factory()->create([
            'name' => 'Brent',
            'email' => 'brendt@stitcher.io',
            'is_admin' => true,
            'reputation' => 10_000,
        ]);

        foreach (range(1, 10) as $i) {
            (new SendUserMessage)(
                to: $mainUser,
                sender: User::factory()->create(),
                url: '/',
                body: $i.fake()->paragraphs(random_int(1, 3), true),
            );
        }

        $users = User::factory()->count(50)->create();

        foreach ($users as $user) {
            VerificationRequest::create([
                'user_id' => $user->id,
                'status' => VerificationRequestStatus::PENDING,
                'motivation' => 'hello',
            ]);
        }

        $rfcs = Rfc::factory()->count(10)->create([
            'title' => 'Interface Default Methods',
            'published_at' => now()->subDay(),
        ]);

        foreach ($rfcs as $rfc) {
            $majority = fake()->boolean() ? VoteType::YES : VoteType::NO;

            $minority = VoteType::opposite($majority);

            foreach ($users as $user) {
                if (fake()->boolean(80)) {
                    $argument = (new CreateArgument())(
                        rfc: $rfc,
                        user: $user,
                        voteType: fake()->boolean(70) ? $majority : $minority,
                        body: fake()->paragraphs(fake()->numberBetween(1, 4), true),
                    );

                    ArgumentComment::factory()->count(fake()->numberBetween(0, 5))
                        ->sequence(
                            ['argument_id' => $argument->id, 'user_id' => $users->random()->id],
                            ['argument_id' => $argument->id, 'user_id' => $users->random()->id],
                            ['argument_id' => $argument->id, 'user_id' => $users->random()->id],
                            ['argument_id' => $argument->id, 'user_id' => $users->random()->id],
                            ['argument_id' => $argument->id, 'user_id' => $users->random()->id],
                        )
                        ->create();
                }
            }
        }
    }
}
