<?php

use Laravel\Mcp\Facades\Mcp;

Mcp::oauthRoutes();

Mcp::web('/mcp', \App\Mcp\Servers\Locket::class)
    ->name('mcp.locket')
    ->middleware('auth:api');
// ->middleware('auth:sanctum');
