# CerneCMS Installation Guide

## Quick Start

1. Extract the zip file to your web server directory
2. Copy `.env.example` to `.env` and configure your settings
3. Set up the database (SQLite is included in `content/database/`)
4. Access the admin panel at `/admin`

## Requirements

- PHP 8.2 or higher
- Web server (Apache, Nginx, or PHP built-in server)
- SQLite extension enabled

## Running the Development Server

```bash
php -S 127.0.0.1:8080 -t public
```

Access the application at `http://localhost:8080`

## Admin Panel

Default admin credentials need to be created. Use the CLI command:

```bash
php bin/cerne user:create
```

## Frontend Development

If you want to modify the frontend:

```bash
pnpm install
pnpm run dev
```

Then build the assets:

```bash
pnpm run build
```

## Support

For documentation and support, visit the project repository.
