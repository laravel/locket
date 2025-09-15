<?php

use Laravel\Mcp\Facades\Mcp;

Mcp::oauthRoutes();

Mcp::web('/mcp', \App\Mcp\Servers\Locket::class)
    ->name('mcp.locket')
    ->middleware('auth:api');

// TODO: Currently fails because 'oauthRoutes' is global and the 401 gets converted to OAuth discovery pattern
Mcp::web('/mcp-sanctum', \App\Mcp\Servers\Locket::class)
    ->name('mcp.locket.sanctum')
    ->middleware('auth:sanctum');
