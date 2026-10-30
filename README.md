# Locket

Locket is a demo application to show [Laravel MCP](https://github.com/laravel/mcp) capabilities.

It combines a read-later app with a link sharing social feed.

Locket allows users to share interesting links, manage their 'to read' list of links, and bookmark links shared by others. Locket allows users to do this through the web, API, and MCP.

![Screenshot of Locket's homepage](art/screenshot.png)

## Important Files

To learn the most from Locket and Laravel MCP, take a look at these directories & files:

- `routes/ai.php`
- `app/Mcp/Servers/Locket.php`
- `app/Mcp/Tools/`
- `app/Mcp/Actions/`
- `routes/api.php`

## API

Locket provides a REST API for programmatic access. See the [API Documentation](API.md) for detailed endpoint information and usage examples.

### Getting an API Token

1. Log in to your Locket account
2. Go to Settings → Profile (`/settings/profile`)
3. Create a new personal access token in the API Tokens section
4. Use the token in the `Authorization: Bearer YOUR_TOKEN` header for API requests

## Auth

This app uses [Laravel Passport](https://laravel.com/docs/passport) for both MCP OAuth authentication and API token authentication.

Users can manage their API tokens in the user profile settings area (`/settings/profile`).

## MCP

Locket comes with an MCP server located at http://locket.test/mcp, with tools, a resource, and a prompt.

Laravel MCP 1.x supports protocol revision `2026-07-28` through `server/discover`. Modern requests include the protocol version and client capabilities in `params._meta`, with matching `MCP-Protocol-Version` and `Mcp-Method` HTTP headers. Tool, prompt, and resource calls also require a matching `Mcp-Name` header. Legacy clients using `initialize` remain supported. See the [MCP upgrade guide](https://github.com/laravel/mcp/blob/main/UPGRADE.md) for request examples.

Requests with an `Origin` header must match `APP_URL` exactly, excluding its trailing slash. Set `APP_URL` to the application's public origin, including the scheme and any non-default port. Native MCP clients that omit `Origin` remain supported. GET requests accepting `text/event-stream` return HTTP 405; browser requests still show the MCP information page.

# Setup

The application requires PHP 8.4 or later and Node.js 22.12 or later.

```shell
composer install
cp .env.example .env
php artisan passport:keys

npm install
npm run build
```

TypeScript remains on 6.0 because `typescript-eslint` does not yet support TypeScript 7. ESLint remains on 9 because `eslint-plugin-react` does not yet support ESLint 10.

# HTTP Notes

Many AI agents use Node which comes with its own certificate store, meaning they'll fail to connect to an MCP server on `https://`. We recommend leaving Locket on `http://` locally for testing with AI agents, and using `https://` on production.
