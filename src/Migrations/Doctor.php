<?php declare(strict_types=1);

namespace Crate\Database\Migrations;

use Crate\Database\Contracts\DriverContract;
use Crate\Database\Contracts\MigrationContract;
use Crate\Database\Repository;
use Crate\Database\Schema;

class Doctor
{

    /**
     * Migration Doctor database provider.
     *
     * @var array
     */
    protected string $provider;

    /**
     * Migration Doctor database driver instance.
     *
     * @var DriverContract
     */
    protected DriverContract $driver;

    /**
     * Create a new Migration Doctor instance.
     */
    public function __construct()
    {
        $dbconfig = config('database.drivers.' . config('database.crate', 'sqlite'));

        $provider = $dbconfig['provider'];
        unset($dbconfig['provider']);

        $this->provider = $provider;
        $this->driver = new $this->provider(...$dbconfig);
    }

    /**
     * Return last error message.
     *
     * @return string|null
     */
    public function lastError(): ?string
    {
        return $this->driver->getLastError();
    }

    /**
     * Scan Migration Path.
     *
     * @param string $module The module name.
     * @param string $path The desired migration path.
     * @return void
     */
    public function scan(string $module, string $path)
    {

    }

    /**
     * Install a single Migration File.
     *
     * @param string $module
     * @param string $filepath
     * @return boolean
     */
    public function execute(string $module, string $filepath): bool
    {
        if (!file_exists($filepath) || !is_file($filepath)) {
            //@todo
            throw new \Exception('');
        }

        // Create Migrator instance
        $migrator = new Migrator();

        /** @var MigrationContract */
        $migration = include $filepath;
        $migration->install($migrator);

        // Execute Actions
        $error = false;
        $transaction = false;
        
        // Start Transaction
        if (method_exists($this->driver, 'begin') && method_exists($this->driver, 'commit') && method_exists($this->driver, 'rollback')) {
            call_user_func([$this->driver, 'begin']);
            $transaction = true;
        }

        // Handle Migrations
        foreach ($migrator->getMigrations() AS $action) {
            $type = array_shift($action);

            if ($type === 'create') {
                $builder = new SchemaBuilder(array_shift($action));
                call_user_func(array_shift($action), $builder);

                if (($status = $this->driver->create($builder)) && $builder->storage) {
                    file_put_contents(__DIR__ . '/../../storage/schemes/' . $builder->name . '.schema.json', $builder->toJSON(true));     //@todo
                }
            } else if ($type === 'update') {
                $editor = new SchemaEditor(Schema::get(array_shift($action)));
                call_user_func(array_shift($action), $editor);
                
                if (($status = $this->driver->alter($editor)) && $builder->storage) {
                    //@todo
                }
            } else if ($type === 'delete') {
                if ($status = $this->driver->drop(Schema::get(array_shift($action)))) {
                    //@todo
                }
            } else if ($type === 'select') {
                $schema = new Repository(array_shift($action));
                call_user_func(array_shift($action), $schema);
            } else if ($type === 'commit') {
                if ($transaction) {
                    call_user_func([$this->driver, 'commit']);
                    call_user_func([$this->driver, 'begin']);
                }
                $status = true;
            }

            if (!$status) {
                $error = true;
                break;
            }
        }

        // Final Commit 
        if ($transaction) {
            if ($error) {
                call_user_func([$this->driver, 'rollback']);
            } else {
                call_user_func([$this->driver, 'commit']);
            }
        }

        // Store Migration
        if (!$error) {
            $this->driver->insert('migrations', [
                'module' => $module,
                'migration' => $filepath
            ]);
        }

        // Return Result
        return !$error;
    }

    /**
     * Uninstall installed Migration(s).
     *
     * @param integer $amount
     * @return boolean
     */
    public function rollback(int $amount = 1): bool
    {

        return false;
    }

}
