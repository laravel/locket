<?php

use Laravel\Mcp\Facades\Mcp;

// Enable OAuth routes for MCP authentication
Mcp::oauthRoutes();

Mcp::web('/mcp', \App\Mcp\Servers\Locket::class)
    ->name('mcp.locket')
    ->middleware('auth:api');
