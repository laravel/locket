# Locket

This app has Sanctum and Passport configured, with commented out lines in the User model to enable/disable each one.

It comes with an MCP server at http://mcp-demo.test/mcp, with one public tool and one authenticated tool.

There's also a basic token management system in 'Settings -> Profile' so you can easily setup and revoke Sanctum tokens.

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
