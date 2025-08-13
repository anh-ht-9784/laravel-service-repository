# Laravel Service Repository Generator

A Laravel package for generating Services and Repositories with automatic dependency injection and API response standardization.

## Installation

### For Local Development

1. Add the package to your `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "packages/laravel-service-repository"
        }
    ],
    "require": {
        "anhht/laravel-service-repository-generator": "*"
    }
}
```

2. Run composer update:

```bash
composer update
```

### For Production (when published to Packagist)

```bash
composer require anhht/laravel-service-repository-generator
```

## Usage

### Commands

The package provides three main commands:

#### 1. Create Service and Repository
```bash
php artisan service-repo:create {model}
```

Example:
```bash
php artisan service-repo:create User
php artisan service-repo:create Product
php artisan service-repo:create Order
```

This will create:
- `app/Services/Contracts/{Model}ServiceContract.php`
- `app/Services/{Model}Service.php`
- `app/Repositories/Contracts/{Model}RepositoryContract.php`
- `app/Repositories/{Model}Repository.php`

**Features:**
- Automatically creates directories if they don't exist
- Asks for confirmation before overwriting existing files
- Automatically registers bindings in `AppServiceProvider` on first run

#### 2. Publish Package Files
```bash
php artisan service-repo:publish [--force]
```

This command will:
- Publish helper functions to `app/helpers/functions.php`
- Publish configuration to `config/laravel-service-repository.php`
- Update `AppServiceProvider` with repository and service bindings

#### 3. Publish API Files
```bash
php artisan service-repo:publish-api-routes [--force]
```

This command will:
- Publish API routes template to `routes/api.php`
- Publish BaseApiController to `app/Http/Controllers/Api/BaseApiController.php`
- Publish ApiCodes constants to `app/Constants/ApiCodes.php`

**Options:**
- `--force`: Overwrite existing files

### API Response Standardization

The package provides a `BaseApiController` that standardizes all API responses:

```php
<?php

namespace App\Http\Controllers\Api;

use Anhht\LaravelServiceRepository\Controllers\BaseApiController;

class UserController extends BaseApiController
{
    public function index()
    {
        return $this->safeExecute(function() {
            return User::paginate(10);
        }, 'Users retrieved successfully');
    }
}
```

**Features:**
- **Smart Response Handling**: Automatically detects success/error responses
- **Safe Execution**: Catches exceptions and formats them properly
- **Data Formatting**: Automatically formats Models, Collections, and Paginators
- **Flexible Return**: Support array returns `[data, message, code]` for customization

### Automatic Dependency Injection

After running the commands, your Services and Repositories will be automatically registered in the Laravel service container. You can inject them directly:

```php
// In your controllers or other classes
public function __construct(
    private UserServiceContract $userService,
    private UserRepositoryContract $userRepository
) {}
```

### Helpers

The package provides several helper functions (available in `app/helpers/functions.php`):

- `cxl_asset($path)` - Asset helper with versioning
- `base_setup_version()` - Get package version from config
- `base_setup_name()` - Get package name
- `base_setup_is_enabled()` - Check if package is enabled

### API Constants

The package provides standardized API response codes (available in `app/Constants/ApiCodes.php`):

- **Success Codes**: `SUCCESS` (200), `CREATED` (201)
- **Client Error Codes**: `BAD_REQUEST` (400), `UNAUTHORIZED` (401), `FORBIDDEN` (403), `NOT_FOUND` (404), `UNPROCESSABLE_ENTITY` (422)
- **Server Error Codes**: `INTERNAL_SERVER_ERROR` (500)
- **Custom Codes**: `VALIDATION_ERROR` (1000)

### Configuration

The package creates `config/laravel-service-repository.php` with:

```php
return [
    'version' => env('BASE_SETUP_VERSION', '1.0.0'),
];
```

## File Structure

After installation, your Laravel app will have:

```
app/
├── Services/
│   ├── Contracts/
│   │   └── {Model}ServiceContract.php
│   └── {Model}Service.php
├── Repositories/
│   ├── Contracts/
│   │   └── {Model}RepositoryContract.php
│   └── {Model}Repository.php
├── Http/Controllers/Api/
│   └── BaseApiController.php
├── Constants/
│   └── ApiCodes.php
├── helpers/
│   └── functions.php
├── routes/
│   └── api.php
└── Providers/
    └── AppServiceProvider.php (updated with bindings)

config/
└── laravel-service-repository.php
```

## Requirements

- PHP >= 8.0
- Laravel >= 8.0

## License

MIT License 