<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\AddLink;
use App\Mcp\Tools\GetRecentLinks;
use App\Mcp\Tools\GetRecentStatuses;
use App\Mcp\Tools\GetTrendingLinks;
use Laravel\Mcp\Server;

class Locket extends Server
{
    public string $name = 'Locket';

    public string $version = '0.0.1';

    public string $instructions = 'Used to interact with Locket, the social link sharing read later app for developers';

    public array $tools = [
        GetRecentLinks::class, // Public
        GetTrendingLinks::class, // Public
        AddLink::class, // Authenticated
        GetRecentStatuses::class, // Public
    ];

    public array $resources = [
        // ExampleResource::class,
    ];

    public array $prompts = [
        // ExamplePrompt::class,
    ];
}
