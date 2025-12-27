# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

## [0.1.0] - 2025-12-27
### Added
- Initial project structure for FlightCMS (FormigaCMS).
- **Backend Framework**: FlightPHP v3.
- **Database**: SQLite with `pages` and `blocks` tables.
- **Models**: ActiveRecord models for `Page` and `Block`.
- **Controllers**:
    - `AdminController` (Dashboard stub).
    - `ApiController` (Pages CRUD endpoints).
    - `FrontendController` (Theme rendering stub).
- **Middleware**: `AuthMiddleware` for API protection.
- **Docker**: `Dockerfile` and `docker-compose.yml` for PHP 8.2 + Apache environment.
- **Configuration**: `app/config/routes.php` and `flight.php` bootstrapper.
- **Verification**: Basic browsing verification for Admin, API, and Home routes.
