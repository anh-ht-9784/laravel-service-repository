# Laravel Service Repository Package

A Laravel package for generating Services and Repositories with automatic dependency injection.

## Installation

### For Local Development

1. Add the package to your `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "packages/base-setup"
        }
    ],
    "require": {
        "anhht/laravel-service-repository": "*"
    }
}
```

2. Run composer update:

```bash
composer update
```

### For Production (when published to Packagist)

```bash
composer require anhht/laravel-service-repository
```

## Usage

### Commands

The package provides two main commands:

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
- Publish configuration to `config/base-setup.php`
- Update `AppServiceProvider` with repository and service bindings

**Options:**
- `--force`: Overwrite existing files

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
├── helpers/
│   └── functions.php
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