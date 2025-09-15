# Locket

Locket is a read-later app and a link sharing social feed. Users can share interesting links, manage their 'to read' list of links, and bookmark links shared by others.

![screenshot](art/screenshot.png)

## Purpose

Locket is a great demo of [Laravel's MCP package](https://github.com/laravel/mcp) in use within a real application with web, API, and MCP entry points.

## Auth

This app comes with both Sanctum and Passport configured on the `User` model for authorization.

A basic token management system exists in 'Settings -> Profile' so users can easily setup and revoke Sanctum tokens.

## MCP

It comes with an MCP server located at http://locket.test/mcp, with a few tools, a resource, and a prompt.

Setup:

```shell
composer install
cp .env.example .env
php artisan passport:keys

npm install
npm run build
```
