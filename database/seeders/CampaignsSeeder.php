<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CampaignsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fh = fopen(storage_path('campaigns.csv'), 'r');

        if ($fh === false) {
            throw new \RuntimeException('campaigns.csv not found');
        }

        $isHeader = true;
        while ($row = fgetcsv($fh)) {
            if ($isHeader) {
                $isHeader = false;

                continue;
            }

            DB::table('campaigns')->insert([
                'utm_campaign' => $row[0],
                'name' => fake()->words(4, true),
            ]);
        }

        fclose($fh);
    }
}
