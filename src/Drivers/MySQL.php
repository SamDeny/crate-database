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

class MySQL implements DriverContract
{

    /**
     * MySQLi Connection
     *
     * @var \MySQLi|null
     */
    protected ?\MySQLi $connection;

    /**
     * Create a new MySQLi driver instance.
     *
     * @param string $hostname
     * @param string $username
     * @param string|null $password
     * @param string $database
     * @param integer $port
     * @param string|null $socket
     */
    public function __construct(
        string $hostname, 
        string $username,
        ?string $password, 
        string $database,
        int $port = 3306, 
        ?string $socket = null
    ) {
        $this->connection = new \MySQLi(
            $this->host = $hostname,
            $this->user = $username,
            $this->pass = ($password ?? ''),
            $this->name = $database,
            $this->port = $port,
            $this->socket = $socket
        );
    }

    /**
     * Clear current MySQLi driver instance.
     */
    public function __destruct()
    {
        if ($this->connection) {
            $this->connection->close();
            $this->connection = null;
        }
    }

    /**
     * Get Driver connection.
     *
     * @return \MySQLi|null
     */
    public function getConnection(): ?\MySQLi
    {
        return $this->connection;
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
