<?php declare(strict_types=1);

namespace Crate\Database\Migrations;

use Crate\Database\Schema;

class Migrator
{

    /**
     * Migration Actions
     * 
     * @var array
     */
    protected array $migrations;

    /**
     * Create a new Migrator instance.
     */
    public function __construct()
    {
        $this->migrations = [ ];
    }

    /**
     * Get Migration Actions
     *
     * @return array
     */
    public function getMigrations(): array
    {
        return $this->migrations;
    }

    /**
     * Create a new Schema
     *
     * @param string $name
     * @param \Closure $callback
     * @return void
     */
    public function create(string $name, \Closure $callback): void
    {
        $this->migrations[] = ['create', $name, $callback];
    }

    /**
     * Update an existing Schema
     *
     * @param string $name
     * @param \Closure $callback
     * @return void
     */
    public function update(string $name, \Closure $callback): void
    {
        $this->migrations[] = ['update', $name, $callback];
    }

    /**
     * Delete an existing Schema
     *
     * @param string $name
     * @return void
     */
    public function delete(string $name): void
    {
        $this->migrations[] = ['delete', $name];
    }

    /**
     * Select an existing Schema
     *
     * @param string $name
     * @param \Closure $callback
     * @return void
     */
    public function select(string $name, \Closure $callback): void
    {
        $this->migrations[] = ['select', $name, $callback];
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function commit(): void
    {
        $this->migrations[] = ['commit'];
    }

}
