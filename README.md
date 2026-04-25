# Portfolio Microservices

A collection of PHP-based backend services supporting a static Next.js portfolio. This repository handles dynamic functionality, including SMTP email delivery and Spotify API integration, while ensuring credentials remain secure.

## Core Features

- Centralized Authentication: A single .env file at the root manages all API keys and secrets.
- Unified CORS: Global .htaccess handles internal routing and manages cross-origin security for specified domains.
- Spotify Bridge: Fetches live playback data using server-side OAuth refresh tokens.
- SMTP Bridge: Routes contact form submissions via an authenticated SMTP provider.

## Development Commands

All commands are executed from the project root:

| Command | Action |
| :--- | :--- |
| composer install | Installs project dependencies |
| composer update | Updates project dependencies |
| composer dump-autoload | Rebuilds the class map and autoloader |

## Setup and Configuration

### 1. Requirements
- PHP 8.0+
- Composer

### 2. Environment Configuration
Create a .env file in the root directory and populate it with your credentials:

## API Endpoints

### Spotify Now Playing
- Endpoint: GET /spotify
- Description: Returns current track details or a null playing state.

### Send Email
- Endpoint: POST /send-email
- Description: Accepts a JSON payload containing name, email, subject, and message.