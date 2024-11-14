<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use App\Models\Stat;
use App\Models\Term;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImportStatsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-stats {filename}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import stats from CSV files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filename = $this->argument('filename');
        $filePath = storage_path($filename);

        if (!file_exists($filePath) || !is_readable($filePath)) {
            $this->error("File is not readable or does not exist.");
            return 1;
        }
        $this->info("Please wait $filename is processing..!");
        $file = fopen($filePath, 'r');
        fgetcsv($file); // Skip header

        $batchSize = 1000;
        $batchData = [];
        $campaignCache = [];
        $termCache = [];
        $recordCount = 0;

        // Start timer
        $startTime = microtime(true);

        DB::beginTransaction();

        try {
            while ($row = fgetcsv($file)) {
                list($utm_campaign, $utm_term, $monetization_timestamp, $revenue) = $row;

                if (empty($utm_campaign) || empty($utm_term)) {
                    continue;
                }

                // Find or cache campaign
                if (!isset($campaignCache[$utm_campaign])) {
                    $campaign = Campaign::firstOrCreate(
                        ['utm_campaign' => $utm_campaign],
                        ['name' => fake()->words(4, true)]
                    );
                    $campaignCache[$utm_campaign] = $campaign->id;
                }

                // Find or cache term
                if (!isset($termCache[$utm_term])) {
                    $term = Term::firstOrCreate(['name' => $utm_term]);
                    $termCache[$utm_term] = $term->id;
                }

                $timestamp = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $monetization_timestamp);
                $event_date = $timestamp->format('Y-m-d');
                $event_hour = $timestamp->format('H');

                $batchData[] = [
                    'campaign_id' => $campaignCache[$utm_campaign],
                    'term_id' => $termCache[$utm_term],
                    'event_date' => $event_date,
                    'event_hour' => $event_hour,
                    'revenue' => $revenue,
                    'monetization_timestamp' => $timestamp,
                ];

                $recordCount++;

                // Insert batch if size reached
                if (count($batchData) >= $batchSize) {
                    Stat::insert($batchData);
                    $batchData = []; // Clear batch
                }
            }

            // Insert any remaining data
            if (count($batchData) > 0) {
                Stat::insert($batchData);
            }

            DB::commit();
            fclose($file);

            // End timer
            $endTime = microtime(true);
            $timeTaken = $endTime - $startTime;

            $this->info("Records uploaded: $recordCount");
            $this->info("Time taken: " . round($timeTaken, 2) . " seconds");
            $this->info("Import completed successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            fclose($file);
            $this->error('Import failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
