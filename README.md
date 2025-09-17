# Locket

Locket is a demo app to show [Laravel MCP](https://github.com/laravel/mcp) capabilities.

It combines a read-later app with a link sharing social feed.

Locket allows users to share interesting links, manage their 'to read' list of links, and bookmark links shared by others. And allows users to do this through the web, API, and MCP.

![Screenshot of Locket's homepage](art/screenshot.png)

## Important files

To learn the most from Locket about Laravel MCP take a look at these directories & files:

- `routes/ai.php`
- `app/Mcp/Servers/Locket.php`
- `app/Mcp/Tools/`
- `app/Mcp/Actions/`

## Auth

This app uses [Laravel Passport](https://laravel.com/docs/passport) for MCP authentication and API authentication.

## MCP

Locket comes with an MCP server located at http://locket.test/mcp, with tools, a resource, and a prompt.

# Setup

```shell
composer install
cp .env.example .env
php artisan passport:keys

npm install
npm run build
```

# HTTP Notes

Many AI agents use Node which comes with its own certificate store, meaning they'll fail to connect to an MCP server on https://.

We recommend leaving Locket on http:// locally for testing with AI agents, and using https:// on production.
