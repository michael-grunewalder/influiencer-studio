<?php

namespace Database\Seeders;

use App\Models\Influencer;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create users
        $userAlpha = User::factory()->create([
            'first_name' => 'Alpha',
            'last_name' => 'User',
            'email' => 'alpha@example.com',
            'password' => Hash::make('password123'),
        ]);

        $userBeta = User::factory()->create([
            'first_name' => 'Beta',
            'last_name' => 'User',
            'email' => 'beta@example.com',
            'password' => Hash::make('password123'),
        ]);

        // 2. Create 5 teams
        $teams = collect();
        for ($i = 1; $i <= 5; $i++) {
            $teams->push(Team::create([
                'name' => "Team #{$i}",
                'description' => "This is test team #{$i} for vividpersona.",
            ]));
        }

        // 3. Attach teams
        // Alpha: Team 1, Team 2, Team 5
        $userAlpha->teams()->attach([
            $teams[0]->id,
            $teams[1]->id,
            $teams[4]->id,
        ]);

        // Beta: Team 3, Team 4, Team 5
        $userBeta->teams()->attach([
            $teams[2]->id,
            $teams[3]->id,
            $teams[4]->id,
        ]);

        // 4. Create influencers for each team (up to 12)
        foreach ($teams as $team) {
            $count = rand(1, 12);
            Influencer::factory()->count($count)->create([
                'team_id' => $team->id,
            ]);
        }
    }
}
