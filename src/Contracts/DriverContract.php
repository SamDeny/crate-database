<?php declare(strict_types=1);

namespace Crate\Database\Contracts;

use Crate\Database\Migrations\SchemaBuilder;
use Crate\Database\Migrations\SchemaEditor;
use Crate\Database\Query;
use Crate\Database\Schema;

interface DriverContract
{

    /**
     * Get Driver connection.
     *
     * @return mixed
     */
    public function getConnection(): mixed;
    
    /**
     * Return last executed Query / Statement.
     *
     * @return string|null
     */
    public function getLastQuery(): ?string;

    /**
     * Return last received Result.
     *
     * @return mixed
     */
    public function getLastResult(): mixed;

    /**
     * Return last error message
     *
     * @return string
     */
    public function getLastError(): ?string;

    /**
     * Create Database Schema.
     *
     * @param SchemaBuilder $builder
     * @return boolean
     */
    public function create(SchemaBuilder $builder): bool;

    /**
     * Alter Database Schema.
     *
     * @param SchemaEditor $editor
     * @return boolean
     */
    public function alter(SchemaEditor $editor): bool;

    /**
     * Drop Database Schema.
     *
     * @param Schema $schema
     * @return boolean
     */
    public function drop(Schema $schema): bool;

    /**
     * Select one or more records.
     *
     * @param string $schema
     * @param Query $query
     * @return ?array
     */
    public function select(string $schema, Query $query): ?array;

    /**
     * Insert one or more records.
     *
     * @param string $schema
     * @param array $values
     * @return integer
     */
    public function insert(string $schema, array $values): int;

    /**
     * Update one or more records.
     *
     * @param string $schema
     * @param array $values
     * @param array $where
     * @return integer
     */
    public function update(string $schema, array $values, array $where = []): int;

    /**
     * Delete one or more records.
     *
     * @param string $schema
     * @param array $where
     * @return integer
     */
    public function delete(string $schema, array $where = []): int;
    
}
