
<div align="center">
    <h1>Appsolutely AIO</h1>
    <p>AI-powered application framework for Laravel</p>
</div>

<p align="center">
    <a><img src="https://img.shields.io/badge/php-8.2+-59a9f8.svg?style=flat" /></a>
    <a><img src="https://img.shields.io/badge/laravel-10+-59a9f8.svg?style=flat" ></a>
</p>

`Appsolutely AIO` is an AI-powered application framework for Laravel that goes beyond a traditional admin panel. It provides a complete foundation for building modern web applications — install it once and start shipping.

### What's Included

- **Admin Dashboard** — A fully featured admin panel with configurable layouts, themes, and role-based access control.
- **CMS** — Built-in content management with article publishing, categories, and media handling.
- **E-Commerce Essentials** — Base configuration for product catalogs, orders, and payment integration.
- **AI Modules** — Integrated AI capabilities ready to plug into your application workflows.
- **Production-Ready APIs** — Authentication (login, registration, password reset), user management, and other common endpoints — available immediately after installation.

### Installation

```bash
composer require appsolutely/aio
```

Publish resources:

```bash
php artisan admin:publish
```

Run installation:

```bash
php artisan admin:install
```

Open `http://localhost/admin` in your browser, login with `admin` / `admin`.

### Usage as a Package

AIO is designed to be required into your own Laravel application. Once installed, all admin routes, API endpoints, and UI components are available out of the box. You can extend, override, or compose them to fit your application's needs.

### Credits

Based on [Dcat Admin](https://github.com/jqhph/dcat-admin) by [jqhph](https://github.com/jqhph).

### License

`Appsolutely AIO` is licensed under [The MIT License (MIT)](LICENSE).
