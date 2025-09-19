<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\FetchLinkTitle as FetchLinkTitleJob;
use App\Models\Link;
use Illuminate\Console\Command;

class FetchLinkTitle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-link-title {url : The URL to fetch the title for} {--debug : Show raw HTML and extracted title}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manually test the FetchLinkTitle job with a given URL';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $url = $this->argument('url');
        $debug = $this->option('debug');

        $this->info("Testing FetchLinkTitle job for URL: {$url}");

        if ($debug) {
            // Fetch the HTML directly to show what we're getting
            $this->info('Fetching HTML with debug mode...');

            try {
                $response = \Illuminate\Support\Facades\Http::timeout(10)
                    ->withUserAgent('Mozilla/5.0 (compatible; LocketBot/1.0)')
                    ->get($url);

                $html = $response->body();

                $this->info('Response Status: '.$response->status());
                $this->info('Content Type: '.$response->header('Content-Type'));
                $this->info('HTML Length: '.strlen($html).' bytes');

                // Look for title tag
                if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $matches)) {
                    $this->info('Raw title tag content: '.$matches[1]);
                    $this->info('Cleaned title: '.html_entity_decode(trim($matches[1])));
                } else {
                    $this->warn('No title tag found!');
                    $this->info('First 1000 chars of HTML:');
                    $this->line(substr($html, 0, 1000));
                }

                $this->info("\n---Now running the actual job---\n");
            } catch (\Exception $e) {
                $this->error('Debug fetch failed: '.$e->getMessage());
            }
        }

        // Create a temporary Link model (not saved to database)
        $link = new Link;
        $link->id = 0;
        $link->url = $url;
        $link->title = null;

        // Make the model "exist" so update() will work
        $link->exists = true;

        // Override the update method to just display the result
        $link->fillable(['title']);
        $originalUpdate = \Closure::bind(function ($attributes) {
            $this->fill($attributes);

            return true;
        }, $link, Link::class);

        // Run the job synchronously
        try {
            $job = new FetchLinkTitleJob($link);
            $job->handle();

            if ($link->title) {
                $this->info("✓ Successfully fetched title: {$link->title}");
            } else {
                $this->warn('No title was fetched for this URL');
            }
        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
