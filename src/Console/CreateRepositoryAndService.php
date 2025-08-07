<?php

namespace Anhht\LaravelServiceRepository\Console;

use Illuminate\Console\Command;

class CreateRepositoryAndService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service-repo:create {model} {--service=} {--repository=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Service and Repository for a model';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $model = ucfirst($this->argument('model'));
        
        // Use model name for service and repository if not specified
        $serviceName = $this->option('service') ? ucfirst($this->option('service')) : $model;
        $repositoryName = $this->option('repository') ? ucfirst($this->option('repository')) : $model;
        
        // Create directories in app/
        $serviceDir = 'app/Services';
        $serviceContractDir = $serviceDir . '/Contracts';
        $repositoryDir = 'app/Repositories';
        $repositoryContractDir = $repositoryDir . '/Contracts';
        
        $folders = [
            $serviceDir,
            $serviceContractDir,
            $repositoryDir,
            $repositoryContractDir,
        ];

        // Create directories if they don't exist
        foreach ($folders as $folder) {
            if (!is_dir($folder)) {
                mkdir($folder, 0755, true);
                $this->info("Created directory: {$folder}");
            }
        }

        // Create service and service contract files
        $fileServiceContractName = $serviceContractDir . '/' . $serviceName . 'ServiceContract.php';
        $fileServiceName = $serviceDir . '/' . $serviceName . 'Service.php';

        if (file_exists($fileServiceContractName) || file_exists($fileServiceName)) {
            if ($this->confirm("Service files already exist. Do you want to overwrite them?")) {
                $this->createServiceFiles($serviceName, $fileServiceContractName, $fileServiceName);
            } else {
                $this->comment('Skipping service creation');
            }
        } else {
            $this->createServiceFiles($serviceName, $fileServiceContractName, $fileServiceName);
        }

        // Create repository and repository contract files
        $fileRepositoryContractName = $repositoryContractDir . '/' . $repositoryName . 'RepositoryContract.php';
        $fileRepositoryName = $repositoryDir . '/' . $repositoryName . 'Repository.php';

        if (file_exists($fileRepositoryContractName) || file_exists($fileRepositoryName)) {
            if ($this->confirm("Repository files already exist. Do you want to overwrite them?")) {
                $this->createRepositoryFiles($repositoryName, $fileRepositoryContractName, $fileRepositoryName);
            } else {
                $this->comment('Skipping repository creation');
            }
        } else {
            $this->createRepositoryFiles($repositoryName, $fileRepositoryContractName, $fileRepositoryName);
        }

        // Check if setup is already completed
        if (!$this->isSetupCompleted()) {
            // Mark setup as completed
            $this->markSetupAsCompleted();

            $this->info('✅ Base Setup package has been successfully installed!');
            $this->comment('📁 Services and Repositories will be automatically registered');
            $this->comment('🔧 Helpers are available in app/helpers/functions.php');
        }

        return Command::SUCCESS;
    }

    public function makeMultiFolder(array $directories, $mode = null, $recursive = null): bool
    {
        foreach ($directories as $directory) {
            mkdir($directory, $mode == null ? 0777 : $mode, $recursive == null ? true : $mode);
        }

        return true;
    }

    /**
     * Create service files
     */
    protected function createServiceFiles(string $serviceName, string $contractPath, string $servicePath): void
    {
        // Create service contract file
        $serviceContractFile = fopen($contractPath, "w") or die("Unable to open file!");
        fwrite($serviceContractFile, $this->templateServiceContract($serviceName));
        fclose($serviceContractFile);

        // Create service file
        $serviceFile = fopen($servicePath, "w") or die("Unable to open file!");
        fwrite($serviceFile, $this->templateService($serviceName));
        fclose($serviceFile);

        $this->info($serviceName . 'Service and ' . $serviceName . 'ServiceContract have been created');
        $this->comment('Service binding will be automatically registered in AppServiceProvider');
    }

    /**
     * Create repository files
     */
    protected function createRepositoryFiles(string $repositoryName, string $contractPath, string $repositoryPath): void
    {
        // Create repository contract file
        $repositoryContractFile = fopen($contractPath, "w") or die("Unable to open file!");
        fwrite($repositoryContractFile, $this->templateRepositoryContract($repositoryName));
        fclose($repositoryContractFile);

        // Create repository file
        $repositoryFile = fopen($repositoryPath, "w") or die("Unable to open file!");
        fwrite($repositoryFile, $this->templateRepository($repositoryName));
        fclose($repositoryFile);

        $this->info($repositoryName . 'Repository and ' . $repositoryName . 'RepositoryContract have been created');
        $this->comment('Repository binding will be automatically registered in AppServiceProvider');
    }

    public function templateServiceContract($service): string
    {
        return '<?php

namespace App\Services\Contracts;

interface ' . $service . 'ServiceContract
{
    /**
     * Get all records
     */
    public function all();

    /**
     * Find record by ID
     */
    public function find($id);

    /**
     * Create new record
     */
    public function create(array $data);

    /**
     * Update record
     */
    public function update($id, array $data);

    /**
     * Delete record
     */
    public function delete($id);
}';
    }

    public function templateService($service): string
    {
        return '<?php

namespace App\Services;

use App\Services\Contracts\\' . $service . 'ServiceContract;
use App\Repositories\Contracts\\' . $service . 'RepositoryContract;

class ' . $service . 'Service implements ' . $service . 'ServiceContract
{
    protected $repository;

    public function __construct(' . $service . 'RepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all records
     */
    public function all()
    {
        return $this->repository->all();
    }

    /**
     * Find record by ID
     */
    public function find($id)
    {
        return $this->repository->find($id);
    }

    /**
     * Create new record
     */
    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    /**
     * Update record
     */
    public function update($id, array $data)
    {
        return $this->repository->update($id, $data);
    }

    /**
     * Delete record
     */
    public function delete($id)
    {
        return $this->repository->delete($id);
    }
}
';
    }

    public function templateRepositoryContract($repository): string
    {
        return '<?php

namespace App\Repositories\Contracts;

interface ' . $repository . 'RepositoryContract
{
    /**
     * Get all records
     */
    public function all();

    /**
     * Find record by ID
     */
    public function find($id);

    /**
     * Create new record
     */
    public function create(array $data);

    /**
     * Update record
     */
    public function update($id, array $data);

    /**
     * Delete record
     */
    public function delete($id);
}';
    }

    public function templateRepository($repository): string
    {
        return '<?php

namespace App\Repositories;

use App\Repositories\Contracts\\' . $repository . 'RepositoryContract;
use App\Models\\' . $repository . ';

class ' . $repository . 'Repository implements ' . $repository . 'RepositoryContract
{
    protected $model;

    public function __construct(' . $repository . ' $model)
    {
        $this->model = $model;
    }

    /**
     * Get all records
     */
    public function all()
    {
        return $this->model->all();
    }

    /**
     * Find record by ID
     */
    public function find($id)
    {
        return $this->model->find($id);
    }

    /**
     * Create new record
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update record
     */
    public function update($id, array $data)
    {
        $record = $this->model->find($id);
        if ($record) {
            $record->update($data);
            return $record;
        }
        return null;
    }

    /**
     * Delete record
     */
    public function delete($id)
    {
        $record = $this->model->find($id);
        if ($record) {
            return $record->delete();
        }
        return false;
    }
}
';
    }

    /**
     * Publish helpers to app directory
     */
    protected function publishHelpers(): void
    {
        $helperDir = app_path('helpers');
        $helperFile = $helperDir . '/functions.php';
        $sourceFile = __DIR__ . '/../helpers/functions.php';

        // Create helpers directory if not exists
        if (!is_dir($helperDir)) {
            mkdir($helperDir, 0755, true);
            $this->info('Created directory: app/helpers');
        }

        // Copy helper file if not exists
        if (!file_exists($helperFile) && file_exists($sourceFile)) {
            copy($sourceFile, $helperFile);
        }
    }

    /**
     * Check if setup is already completed
     */
    protected function isSetupCompleted(): bool
    {
        $setupFile = storage_path('app/base-setup-completed.txt');
        return file_exists($setupFile);
    }

    /**
     * Mark setup as completed
     */
    protected function markSetupAsCompleted(): void
    {
        $setupFile = storage_path('app/base-setup-completed.txt');
        file_put_contents($setupFile, date('Y-m-d H:i:s') . ' - Base Setup package installed successfully');
    }
}
