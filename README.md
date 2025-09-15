# Locket

Locket is a read-later app and a link sharing social feed. Users can share interesting links, manage their 'to read' list of links, and bookmark links shared by others.

![screenshot](art/screenshot.png)

## Purpose

Locket is a great demo of [Laravel's MCP package](https://github.com/laravel/mcp) in use within a real application with web, API, and MCP entry points.

## Auth

This app comes with both Sanctum and Passport configured on the `User` model for authorization.

A basic token management system exists in 'Settings -> Profile' so users can easily setup and revoke Sanctum tokens.

## MCP

It comes with an MCP server at http://locket.test/mcp, with public tool and authenticated tools.

Setup:

```shell
composer install
cp .env.example .env
php artisan passport:keys
```

Put the contents from `/storage/*.key` into your `.env`.

```shell
npm install
```

```shell
npm run build
```
