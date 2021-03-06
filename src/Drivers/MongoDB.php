<?php declare(strict_types=1);

namespace Crate\Database\Drivers;

use Crate\Database\Contracts\DriverContract;
use Crate\Database\Migrations\SchemaBuilder;
use Crate\Database\Migrations\SchemaEditor;
use Crate\Database\Properties\BooleanProperty;
use Crate\Database\Properties\IntegerProperty;
use Crate\Database\Properties\NumberProperty;
use Crate\Database\Properties\StringProperty;
use Crate\Database\Query;
use Crate\Database\Schema;

class MongoDB implements DriverContract
{

    /**
     * @inheritDoc
     */
    public function getConnection(): mixed
    {

    }
    
    /**
     * @inheritDoc
     */
    public function getLastQuery(): ?string
    {

    }

    /**
     * @inheritDoc
     */
    public function getLastResult(): mixed
    {

    }

    /**
     * @inheritDoc
     */
    public function getLastError(): ?string
    {

    }

    /**
     * @inheritDoc
     */
    public function create(SchemaBuilder $builder): bool
    {

    }

    /**
     * @inheritDoc
     */
    public function alter(SchemaEditor $editor): bool
    {

    }

    /**
     * @inheritDoc
     */
    public function drop(Schema $schema): bool
    {

    }

    /**
     * @inheritDoc
     */
    public function select(string $schema, Query $query): ?array
    {

    }

    /**
     * @inheritDoc
     */
    public function insert(string $schema, array $values): int
    {

    }

    /**
     * @inheritDoc
     */
    public function update(string $schema, array $values, array $where = []): int
    {

    }

    /**
     * @inheritDoc
     */
    public function delete(string $schema, array $where = []): int
    {

    }

}
