
<div align="center">
    <h1>Appsolutely AIO</h1>
    <p>AI-powered application framework for Laravel</p>
</div>

<p align="center">
    <a><img src="https://img.shields.io/badge/php-8.2+-59a9f8.svg?style=flat" /></a>
    <a><img src="https://img.shields.io/badge/laravel-12+-59a9f8.svg?style=flat" ></a>
</p>

`Appsolutely AIO` is an AI-powered application framework for Laravel that goes beyond a traditional admin panel. It provides a complete foundation for building modern web applications — install it once and start shipping.

### What's Included

- **Admin Dashboard** — A fully featured admin panel with configurable layouts, themes, and role-based access control.
- **CMS** — Built-in content management with article publishing, page builder, categories, forms, and media handling.
- **E-Commerce** — Product catalog, orders, carts, coupons, shipping zones, tax rates, inventory management, wishlists, and product reviews.
- **Notification System** — Templated notifications with queue processing, configurable senders and rules.
- **Production-Ready APIs** — Authentication, user management, delivery, file management, and other common endpoints — available immediately after installation.

---

## Requirements

- PHP 8.2+
- Laravel 12+
- A supported database (MySQL, PostgreSQL, or SQLite)

## Installation

### 1. Create a Laravel Application

Skip this step if you already have one.

```bash
composer create-project laravel/laravel my-app
cd my-app
```

### 2. Require the Package

```bash
composer require appsolutely/aio
```

If you are developing locally with a path repository:

```bash
composer config repositories.aio path ../aio
composer require appsolutely/aio:@dev
```

### 3. Publish Assets and Configuration

```bash
php artisan admin:publish
```

This publishes:

| Tag | Destination | Description |
|-----|-------------|-------------|
| `aio-config` | `config/aio.php`, `config/admin.php` | Package and admin panel configuration |
| `aio-migrations` | `database/migrations/` | All database migrations |
| `aio-assets` | `public/vendor/aio/` | Admin panel frontend assets (CSS, JS, fonts) |
| `aio-lang` | `lang/` | Translation files |

You can publish individual tags with flags: `--config`, `--migrations`, `--assets`, `--lang`.

### 4. Configure Environment

Add the following to your `.env` file:

```dotenv
# Admin panel route prefix (default: admin)
ADMIN_ROUTE_PREFIX=admin

# Optional: restrict admin to a specific domain
# ADMIN_ROUTE_DOMAIN=admin.example.com

# Disable HTTPS for local development
ADMIN_HTTPS=false
```

> **Important:** If you copied `.env` from another project, make sure `APP_URL` matches your local address (e.g. `http://127.0.0.1:8000`) and remove or clear any production-specific values like `ADMIN_ASSETS_SERVER`.

### 5. Run Installation

```bash
php artisan admin:install
```

This command will:

1. Run all database migrations
2. Seed the default admin user, roles, permissions, and menu
3. Scaffold the `app/Admin/` directory with starter controllers, routes, and bootstrap file

### 6. Access the Admin Panel

Start the development server:

```bash
php artisan serve
```

Open **http://localhost:8000/admin** in your browser.

Default credentials:

| Username | Password |
|----------|----------|
| `admin`  | `admin`  |

> Change the default password immediately after first login.

---

## Configuration

### User Model

AIO does not ship its own User model — it uses your application's `App\Models\User` by default. This is configurable in `config/aio.php`:

```php
'models' => [
    'user' => \App\Models\User::class,
    'team' => \App\Models\Team::class,
    // ...
],
```

For type safety, your User model can implement the `Appsolutely\AIO\Contracts\Authenticatable` interface:

```php
use Appsolutely\AIO\Contracts\Authenticatable;

class User extends \Illuminate\Foundation\Auth\User implements Authenticatable
{
    // ...
}
```

### Admin Routes

Route configuration is in `config/admin.php`:

```php
'route' => [
    'domain'     => env('ADMIN_ROUTE_DOMAIN'),
    'prefix'     => env('ADMIN_ROUTE_PREFIX', 'admin'),
    'namespace'  => 'App\\Admin\\Controllers',
    'middleware'  => ['web', 'admin'],
],
```

### Service Overrides

All services are bound via interfaces. Override any service by rebinding in your `AppServiceProvider`:

```php
$this->app->bind(
    \Appsolutely\AIO\Services\Contracts\OrderServiceInterface::class,
    \App\Services\CustomOrderService::class,
);
```

---

## Project Structure

After installation, your project will contain:

```
app/Admin/
├── Controllers/
│   ├── AuthController.php      # Admin authentication
│   └── HomeController.php      # Dashboard
├── Metrics/Examples/            # Example dashboard metric cards
├── bootstrap.php                # Admin panel bootstrapping
└── routes.php                   # Admin route definitions
```

AIO registers the following route groups automatically:

| Group | Prefix | Description |
|-------|--------|-------------|
| Admin | `/admin` | Admin panel (configurable) |
| Web | `/` | Frontend routes (member auth, pages, sitemap) |
| API | `/` | API endpoints (deliveries, forms, releases) |

---

## Admin Menu Profiles

AIO ships with three admin menu seeders for different use cases. The default installation uses the **Full** profile. You can switch profiles at any time — each run truncates the menu table and re-seeds from scratch.

| Profile | Seeder Class | Use Case |
|---------|-------------|----------|
| **Full** | `AdminMenuFullSeeder` | All features: CMS + E-Commerce + Releases. For projects that need everything. |
| **CMS** | `AdminMenuCmsSeeder` | Content management only: Articles, Pages, Page Blocks, Categories, Menus, Forms, Notifications, Media. No e-commerce. |
| **E-Commerce** | `AdminMenuEcomSeeder` | Online store with CMS: Orders and Products first, then content management features. |

### Switching Menu Profile

```bash
# Full (default)
php artisan db:seed --class="Appsolutely\AIO\Database\Seeders\Admin\AdminMenuFullSeeder"

# CMS only
php artisan db:seed --class="Appsolutely\AIO\Database\Seeders\Admin\AdminMenuCmsSeeder"

# E-Commerce + CMS
php artisan db:seed --class="Appsolutely\AIO\Database\Seeders\Admin\AdminMenuEcomSeeder"
```

Safe to run multiple times — each run clears the existing menu and rebuilds it.

### Menu Structure

**Full** — 14 top-level items, Articles and Pages first:

> Dashboard, Articles, Pages, Page Blocks, Categories, Menus, Orders ▸ (All Orders, Shipments, Refunds, Coupons), Products ▸ (All Products, Categories, Attributes, Reviews), Forms, Notifications, Media, Releases, Site Settings, System ▸ (Admin Users, Roles, Permissions, Menu, Extensions)

**CMS** — 11 top-level items, flat layout:

> Dashboard, Articles, Pages, Page Blocks, Categories, Menus, Forms, Notifications, Media, Site Settings, System ▸ …

**E-Commerce** — 14 top-level items, orders-first layout:

> Dashboard, Orders ▸ (…), Products ▸ (…), Articles, Pages, Page Blocks, Categories, Menus, Forms, Notifications, Media, Site Settings, System ▸ …

---

## Extras

AIO bundles several packages that accelerate development. They are already included as dependencies — just use their artisan commands directly.

### AI (Laravel AI + OpenAI)

Build AI-powered features with [Laravel AI](https://github.com/laravel/ai) and [OpenAI for Laravel](https://github.com/openai-php/laravel). Scaffold agents, tools, and middleware instantly:

```bash
# Create an AI agent
php artisan make:agent MyAgent

# Create an agent with structured output
php artisan make:agent MyAgent --structured

# Create an AI tool
php artisan make:tool SearchTool

# Create agent middleware
php artisan make:agent-middleware RateLimitMiddleware
```

> Publish stubs to customize generated code: `php artisan stub:publish`

### AI Agent Config

`admin:install` also publishes shared agent configuration for AI coding assistants (Claude Code, Cursor, Codex):

- `.agents/skills/` — Reusable skill definitions
- `.claude/settings.json` — Claude Code permissions
- `.cursor/mcp.json` — Cursor MCP server config
- `.codex/config.toml.example` — Codex configuration template
- Symlinks: `.agent/skills`, `.claude/skills`, `.cursor/skills` → `.agents/skills`

---

## Available Commands

| Command | Description |
|---------|-------------|
| `admin:install` | Install the admin package (migrate, seed, scaffold) |
| `admin:publish` | Publish assets, config, migrations, and language files |
| `admin:create-user` | Create a new admin user |
| `admin:reset-password` | Reset password for an admin user |
| `admin:menu-cache` | Flush and rebuild the menu cache |
| `admin:ext-make` | Create a new AIO extension |
| `admin:ext-install` | Install an extension |
| `admin:ide-helper` | Generate IDE helper file for autocompletion |

---

## Credits

Based on [Dcat Admin](https://github.com/jqhph/dcat-admin) by [jqhph](https://github.com/jqhph).

## License

`Appsolutely AIO` is licensed under [The MIT License (MIT)](LICENSE).
