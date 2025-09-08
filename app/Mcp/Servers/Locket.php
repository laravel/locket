<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\AddLink;
use App\Mcp\Tools\GetRecentLinks;
use App\Mcp\Tools\GetRecentStatuses;
use App\Mcp\Tools\UpdateStatus;
use Laravel\Mcp\Server;

class Locket extends Server
{
    public string $name = 'Locket';

    public string $version = '0.0.1';

    public string $instructions = 'Instructions describing how to use the server and its features. This may be used by clients to improve the LLM\'s understanding of the server\'s capabilities. It can be thought of as a "hint" to the LLM.';

    public array $tools = [
        // GetRecentLinks::class, // Public
        // AddLink::class, // Authenticated
        GetRecentStatuses::class, // Public
        UpdateStatus::class, // Authenticated
    ];

    public array $resources = [
        // ExampleResource::class,
    ];

    public array $prompts = [
        // ExamplePrompt::class,
    ];
}
