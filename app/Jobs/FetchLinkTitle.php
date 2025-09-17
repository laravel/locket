<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Link;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchLinkTitle implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Link $link
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Making HTTP request to fetch title', [
                'link_id' => $this->link->id,
                'url' => $this->link->url,
            ]);

            $response = Http::timeout(10)
                ->withUserAgent('Mozilla/5.0 (compatible; LocketBot/1.0)')
                ->get($this->link->url);

            Log::info('HTTP response received', [
                'link_id' => $this->link->id,
                'url' => $this->link->url,
                'status' => $response->status(),
                'successful' => $response->successful(),
                'content_type' => $response->header('Content-Type'),
                'content_length' => strlen($response->body()),
            ]);

            if ($response->successful()) {
                $html = $response->body();

                Log::info('Attempting to extract title from HTML', [
                    'link_id' => $this->link->id,
                    'url' => $this->link->url,
                    'html_length' => strlen($html),
                    'html_preview' => substr($html, 0, 200),
                ]);

                // Extract title using regex...
                if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $matches)) {
                    $rawTitle = $matches[1];

                    $title = html_entity_decode(trim($rawTitle));

                    Log::info('Title found in HTML', [
                        'link_id' => $this->link->id,
                        'url' => $this->link->url,
                        'raw_title' => $rawTitle,
                        'decoded_title' => $title,
                    ]);

                    // Clean up the title (remove extra whitespace, line breaks)...
                    $title = preg_replace('/\s+/', ' ', $title);

                    // Limit title length to prevent database issues...
                    $title = substr($title, 0, 255);

                    Log::info('Title cleaned and processed', [
                        'link_id' => $this->link->id,
                        'url' => $this->link->url,
                        'final_title' => $title,
                        'title_length' => strlen($title),
                    ]);

                    if (! empty($title)) {
                        $this->link->update(['title' => $title]);
                        Log::info('Successfully updated link title', [
                            'link_id' => $this->link->id,
                            'url' => $this->link->url,
                            'title' => $title,
                        ]);
                    } else {
                        Log::warning('Title was empty after processing', [
                            'link_id' => $this->link->id,
                            'url' => $this->link->url,
                        ]);
                    }
                } else {
                    Log::warning('No title tag found in HTML', [
                        'link_id' => $this->link->id,
                        'url' => $this->link->url,
                        'html_preview' => substr($html, 0, 500),
                    ]);
                }
            } else {
                Log::warning('HTTP request was not successful', [
                    'link_id' => $this->link->id,
                    'url' => $this->link->url,
                    'status' => $response->status(),
                    'response_body' => substr($response->body(), 0, 200),
                ]);
            }
        } catch (Exception $e) {
            Log::error('Failed to fetch title for link', [
                'link_id' => $this->link->id,
                'url' => $this->link->url,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        Log::info('FetchLinkTitle job completed', [
            'link_id' => $this->link->id,
            'url' => $this->link->url,
        ]);
    }
}
