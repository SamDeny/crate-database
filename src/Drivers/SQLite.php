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

class SQLite implements DriverContract
{

    /**
     * SQLite database path.
     *
     * @var string
     */
    protected string $path;

    /**
     * SQLite3 encryption key.
     *
     * @var string|null
     */
    protected ?string $encryptionKey = null;

    /**
     * SQLite3 PRAGMAs.
     *
     * @var array
     */
    protected array $pragmas = [];

    /**
     * SQLite3 connection.
     *
     * @var ?\SQLite3
     */
    protected ?\SQLite3 $connection = null;

    /**
     * SQLite3 Version
     * 
     * @var ?string
     */
    protected ?string $sqliteVersion = null;

    /**
     * Switch if in transaction.
     *
     * @var boolean
     */
    protected bool $transaction = false;

    /**
     * Last SQL statement
     *
     * @var string|null
     */
    protected string|null $lastQuery = null;
    
    /**
     * Last received Result
     *
     * @var mixed
     */
    protected mixed $lastResult = null;
    
    /**
     * Last Error Message
     *
     * @var string|null
     */
    protected string|null $lastError = null;

    /**
     * Create a new SQLite driver instance.
     *
     * @param string $path
     * @param ?string $encryptionKey
     * @param array $pragmas
     */
    public function __construct(string $path, ?string $encryptionKey = null, array $pragmas = [])
    {
        $this->path = $path;
        $this->encryptionKey = $encryptionKey;
        $this->pragmas = $pragmas;

        // Create Connection
        $flags = \SQLITE3_OPEN_CREATE | \SQLITE3_OPEN_READWRITE;
        if ($this->encryptionKey) {
            $connection = new \SQLite3($path, $flags, $encryptionKey);
        } else {
            $connection = new \SQLite3($path, $flags);
        }
        
        // Apply PRAGMAs
        foreach ($pragmas AS $pragma => $value) {
            if (is_bool($value)) {
                $value = $value? 'yes': 'no';
            }
            $connection->exec("PRAGMA $pragma = $value;");
        }

        // Set Connection
        $connection->enableExceptions(false);       //@todo
        $this->connection = $connection;
    }

    /**
     * Close SQLite database connection.
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
     * @return \SQLite3|null
     */
    public function getConnection(): ?\SQLite3
    {
        return $this->connection;
    }

    /**
     * Get SQLite version
     *
     * @return string
     */
    public function getVersion(): string
    {
        if ($this->sqliteVersion === null) {
            $this->sqliteVersion = $this->connection->query('SELECT sqlite_version() as ver;')->fetchArray()['ver'];
        }
        return $this->sqliteVersion;
    }

    /**
     * @inheritDoc
     */
    public function getLastQuery(): ?string
    {
        return $this->lastQuery;
    }

    /**
     * @inheritDoc
     */
    public function getLastResult(): mixed
    {
        return $this->lastResult;
    }

    /**
     * @inheritDoc
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Execute and Log an SQL statement.
     *
     * @param String $query
     * @return boolean
     */
    public function execute(String $query): bool
    {
        $this->lastQuery = $query;
        $this->lastResult = @$this->connection->exec($query);
        $this->lastInserts = [];

        if ($this->lastResult) {
            $this->lastError = null;
        } else {
            $this->lastError = $this->connection->lastErrorMsg();
        }

        return $this->lastResult;
    }

    /**
     * Build Column
     *
     * @param string $name
     * @param [type] $property
     * @return string
     */
    protected function buildColumn(string $name, $property): string
    {
        $constraints = [];

        // Evaluate Type
        if ($property instanceof NumberProperty) {
            $type = 'REAL';
        } else if ($property instanceof IntegerProperty || $property instanceof BooleanProperty) {
            $type = 'INTEGER';
        } else {
            $type = 'TEXT';
        }

        // Required Constraint
        if (isset($property->required) && $property->required === true) {
            $constraints[] = 'NOT NULL';
        } else {
            $constraints[] = 'NULL';
        }

        // Unique Constraint
        if (isset($property->unique)) {
            $constraints[] = 'UNIQUE';
        }

        // Default Constraint
        if (isset($property->default)) {
            if ($property->default === NULL) {
                $constraints[] = "DEFAULT NULL";
            } else {
                if ($property instanceof StringProperty) {
                    $constraints[] = "DEFAULT '{$property->default}'";
                } else if ($property instanceof BooleanProperty) {
                    $constraints[] = "DEFAULT " . ($property->default? '1': '0');
                } else {
                    if (is_array($property->default) || is_object($property->value)) {
                        $default = "'". json_encode($property->default) ."'";
                    } else {
                        $default = $property->default;
                    }
                    $constraints[] = "DEFAULT {$default}";
                }
            }
        }

        // Check Constraint
        if ($property instanceof IntegerProperty || $property instanceof NumberProperty) {
        }

        // Return Column
        return trim("\"{$name}\" $type " . implode(' ', $constraints));
    }

    /**
     * @inheritDoc
     */
    public function create(SchemaBuilder $schema): bool
    {
        $fields = [];

        // Primary Key
        if ($schema->primaryKeyFormat === 'id') {
            $fields[$schema->primaryKey] = "'$schema->primaryKey' INTEGER NOT NULL PRIMARY KEY";
        } else if ($schema->primaryKeyFormat === 'uid') {
            $fields[$schema->primaryKey] = "'$schema->primaryKey' TEXT NOT NULL PRIMARY KEY CHECK(length(\"$schema->primaryKey\") == 26)";
        } else if ($schema->primaryKeyFormat === 'uuid' || $schema->primaryKeyFormat === 'uuidv4') {
            $fields[$schema->primaryKey] = "'$schema->primaryKey' TEXT NOT NULL PRIMARY KEY CHECK(length(\"$schema->primaryKey\") == 36)";
        }

        // Fields
        foreach ($schema->properties AS $name => $property) {
            $fields[$name] = $this->buildColumn($name, $property);
        }
        if ($schema->created) {
            $fields[$schema->created] = "'$schema->created' TEXT DEFAULT (DATETIME('NOW'))";
        }
        if ($schema->updated) {
            $fields[$schema->updated] = "'$schema->updated' TEXT NULL";
        }

        // Add Unique Indexes
        foreach ($schema->uniques AS $unique) {
            $fields[$schema->name . '_' . implode('_', $unique) . 'unique_index'] = "UNIQUE (". implode(',', $unique) .")";
        }

        // Build Query
        $query = sprintf(
            "CREATE TABLE %s (\n  %s\n);\n",
            $schema->name, 
            implode(",\n  ", $fields)
        );
        if ($schema->updated) {
            $query .= sprintf(
                "CREATE TRIGGER %1\$s_%3\$s AFTER UPDATE ON %1\$s\n" .
                "  BEGIN\n" .
                "    UPDATE %1\$s SET %3\$s = DATETIME('NOW') WHERE %2\$s = NEW.%2\$s;\n" .
                "  END;",
                $schema->name, 
                $schema->primaryKey,
                $schema->updated
            );
        }

        // Execute Query
        return $this->execute($query);
    }

    /**
     * @inheritDoc
     */
    public function alter(SchemaEditor $schema): bool
    {
        $dropSupport = version_compare($this->getVersion(), '3.35', '>=');

        // Tasks
        $create = $schema->properties;  // Create new Properties
        $change = [];                   // Change Property definitions
        $rename = [];                   // Rename Properties
        $remove = [];                   // Remove Properties

        $time = '_' . time();
        foreach ($schema->changedProperties AS $changes) {
            if ($changes[1] === null) {
                $remove[] = $changes[0];
            } else {
                if (count($changes) === 3) {
                    // SQLite does not support altering columns, thus we create changed columns 
                    // under a temporary name, replace them and rename them afterwards if necessary.
                    $propname = $changes[1]->name;
                    if (!array_key_exists($propname, $create)) {
                        $changes[1]->name = $propname . $time;

                        $create[$changes[1]->name] = $changes[1];
                        $change[] = [$changes[1]->name, $changes[1], $changes[2]];
                        $rename[] = [$changes[1]->name, $propname];
                    } else {
                        $change[] = $changes;
                    }
                } else {
                    $rename[] = [$changes[0], $changes[1]->name];
                }
            }
        }

        // Create Columns
        foreach ($create AS $name => $property) {
            $field = $this->buildColumn($name, $property);
            $query = "ALTER TABLE {$schema->name} ADD COLUMN $field";
            if ($this->execute($query) === false) {
                return false;
            }
        }

        // Handle internal Date/Time columns
        foreach (['created', 'updated'] AS $column) {
            $old = $schema->originalSchema->{$column};
            $new = $schema->{$column};
            if ($old === $new) {
                continue;   // No changes made
            }

            // Column has been added
            if ($old === null) {
                if ($column === 'created') {
                    $query = "ALTER TABLE {$schema->name} ADD COLUMN '$new' TEXT DEFAULT (DATETIME('NOW'));";
                } else {
                    $query = "
                        ALTER TABLE {$schema->name} ADD COLUMN '$new' TEXT NULL;
                        CREATE TRIGGER {$schema->name}_{$new} AFTER UPDATE ON {$schema->name}
                          BEGIN
                            UPDATE {$schema->name} SET {$new} = DATETIME('NOW') WHERE {$schema->primaryKey} = NEW.{$schema->primaryKey};
                          END;
                    ";
                }
                if ($this->connection->exec($query) === false) {
                    return false;
                } else {
                    continue;
                }
            }

            // Column has been removed
            if ($new === null) {
                $remove[] = $old;
                if ($column === 'updated') {
                    if ($this->connection->exec("DROP TRIGGER {$schema->name}_{$old}") === false) {
                        return false;
                    }
                }
                continue;
            }

            // Column name has been changed
            if ($column === 'updated') {
                if ($this->connection->exec("DROP TRIGGER {$schema->name}_{$old}") === false) {
                    return false;
                }
            }
            if ($this->connection->exec("ALTER TABLE {$schema->name} RENAME COLUMN {$old} TO {$new};") === false) {
                return false;
            }
            if ($column === 'updated') {
                $query .= "
                    CREATE TRIGGER {$schema->name}_{$new} AFTER UPDATE ON {$schema->name}
                      BEGIN
                        UPDATE {$schema->name} SET {$new} = DATETIME('NOW') WHERE {$schema->primaryKey} = NEW.{$schema->primaryKey};
                      END;
                ";
                $this->connection->exec($query);
            }
        }

        // Change Columns
        foreach ($change AS $details) {
            $column = $details[0];
            $property = $details[1];
            $converter = $details[2];

            if ($converter === null) {
                $query = sprintf("
                    UPDATE %1\$s SET \"%3\$s\" = old.__column
                      FROM (
                        SELECT \"%2\$s\" AS __column, \"%4\$s\" as __primary
                          FROM %1\$s
                      ) AS old
                     WHERE %1\$s.%4\$s = old.__primary;
                ", $schema->name, $column, $property->name, $schema->primaryKey);

                if ($this->execute($query) === false) {
                    return false;
                } else {
                    $remove[] = $column;
                    continue;
                }
            } else {
                $query = $this->connection->query("SELECT * FROM {$schema->name};");
                if ($query === false) {
                    return false;
                }

                while (($row = $query->fetchArray(\SQLITE3_ASSOC)) !== false) {
                    $newValue = $converter($row[$column], $row);

                    $stmt = $this->connection->prepare(
                        "UPDATE {$schema->name} SET {$property->name} = ?
                          WHERE {$schema->primaryKey} = ?;"
                    );
                    if (!$stmt) {
                        return false;
                    }
                    
                    $stmt->bindValue(0, $newValue);
                    $stmt->bindValue(1, $row[$schema->primaryKey]);
                    if ($stmt->execute() === false) {
                        return false;
                    }
                }
                $remove[] = $column;
            }
        }

        // Rename Columns
        foreach ($rename AS $details) {
            if ($this->execute("ALTER TABLE {$schema->name} RENAME COLUMN \"{$details[0]}\" TO \"{$details[1]}\";") === false) {
                return false;
            } 
        }

        // Remove Columns
        if ($dropSupport) {
            foreach ($remove AS &$column) {
                if (@$this->connection->exec("ALTER TABLE {$schema->name} DROP COLUMN \"{$column}\";") !== false) {
                    $column = null;
                }
            }
            $remove = array_filter($remove);
        }

        // Remove Columns [Fallback Method]
        // @info DROP COLUMN support has been added in SQLite 3.35.0, but is 
        //       highly limited. The following method works in the most cases.
        if (count($remove) > 0) {
            $foreignKeys = $this->connection->querySingle("PRAGMA foreign_keys;") === 1;

            // Step 1 - Disable Foreign Keys
            if ($foreignKeys) {
                $this->connection->exec('PRAGMA foreign_keys=off;');
            }

            // Step 2 - Create new Table
            $temptable = $schema->name . '_temp';
            $schemaBuilder = $schema->convertToBuilder($schema->name . '_temp');
            $schemaBuilder->name = $temptable;
            if ($this->create($schemaBuilder) === false) {
                return false;
            }

            // Step 3 - Transfer records
            $columns = [$schema->primaryKey, ...array_keys($schemaBuilder->properties)];
            if ($schema->created) {
                $columns[] = $schema->created;
            }
            if ($schema->updated) {
                $columns[] = $schema->updated;
            }

            $query = "INSERT INTO $temptable SELECT ". implode(', ', $columns) ." FROM $schema->name;";
            if ($this->connection->exec($query) === false) {
                return false;
            }

            // Step 4 - Drop old Table
            if ($this->connection->exec("DROP TABLE $schema->name;") === false) {
                return false;
            }

            // Step 5 - Rename new table
            if ($this->connection->exec("ALTER TABLE $temptable RENAME TO $schema->name;") === false) {
                return false;
            }

            // Step 6 - Validate foreign keys
            if ($foreignKeys) {
                if ($this->connection->exec('PRAGMA foreign_key_check;') === false) {
                    return false;
                }
            }

            // Step 7 - Re-Enable foreign keys
            if ($foreignKeys) {
                $this->connection->exec('PRAGMA foreign_keys=on;');
            }
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function drop(Schema $schema): bool
    {

        // Delete Branches, if enabled
        if ($schema->supports('branches')) {
            $query = $this->connection->query("
                SELECT name FROM sqlite_schema 
                 WHERE type='table' AND name LIKE '{$schema->name}_branch_%';
            ");
            if ($query === false) {
                return false;
            }

            while (($row = $query->fetchArray(\SQLITE3_ASSOC)) !== false) {
                if ($this->execute("DROP TABLE {$row['name']};") === false) {
                    return false;
                }
            }
        }
        
        // Delete Revisions, if enabled
        if ($schema->supports('revisions')) {
            $query = $this->connection->query("
                SELECT name FROM sqlite_schema 
                 WHERE type='table' AND name='{$schema->name}_revisions';
            ");
            if ($query === false) {
                return false;
            }

            while (($row = $query->fetchArray(\SQLITE3_ASSOC)) !== false) {
                if ($this->execute("DROP TABLE {$row['name']};") === false) {
                    return false;
                }
            }
        }

        // Drop Main Table
        if ($this->execute("DROP TABLE {$schema->name};") === false) {
            return false;
        } else {
            return true;
        }

    }

    /**
     * @inheritDoc
     */
    public function select(string $schema, Query $query): ?array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function insert(string $scheme, array $values): int
    {
        if (!array_is_list($values)) {
            $values = [$values];
        }

        // Prepare Query
        $columns = array_combine(
            array_keys($values[0]), array_map(fn($key) => ":param_$key", array_keys($values[0]))
        );

        $query  = "INSERT INTO $scheme ";
        $query .= "(". implode(', ', array_keys($columns)) .") VALUES ";
        $query .= "(". implode(', ', array_values($columns)) .");";

        // Execute Statement
        $ids = [];
        $error = false;
        $this->begin();

        if (($stmt = $this->connection->prepare($query)) !== false) {
            foreach ($columns AS $key => $prep) {
                $ref = 'param_' . $key;
                $stmt->bindParam($prep, $$ref);
            }

            // Loop values and execute each single one.
            foreach ($values AS $row) {
                foreach ($row AS $key => $value) {
                    $ref = 'param_' . $key;
                    $$ref = $value;
                }
                
                if (($result = $stmt->execute()) === false) {
                    $error = true;
                    break;
                } else {
                    $ids[] = $this->connection->lastInsertRowID();
                }
            }
        } else {
            $error = true;
        }

        // Finish
        $this->lastQuery = $stmt? $stmt->getSQL(): $query;
        if ($error) {
            $this->lastError = $this->connection->lastErrorMsg();
            $this->lastResult = false;
            $this->lastInserts = [];
            $this->rollback();
        } else {
            $this->lastError = null;
            $this->lastResult = true;
            $this->lastInserts = $ids;
            $this->commit();
        }

        // Return
        return $this->connection->changes();
    }

    /**
     * @inheritDoc
     */
    public function update(string $schema, array $values, array $where = []): int
    {
        $columns = array_combine(
            array_keys($values), array_map(fn($key) => ":param_$key", array_keys($values))
        );
        $wheres = array_combine(
            array_keys($where), array_map(fn($key) => ":where_$key", array_keys($where))
        );
        $prepare = array_combine(
            [...array_values($columns), ...array_values($wheres)], 
            [...array_values($values), ...array_values($where)]
        );

        // Prepare Query
        $query   = "UPDATE $schema SET ";
        $query .= implode(', ', array_map(
            fn($key, $val) => "$key=$val", 
            array_keys($columns), 
            array_values($columns)
        ));
        if (!empty($wheres))  {
            $query .= ' WHERE ' . implode(', ', array_map(
                fn($key, $val) => "$key=$val", 
                array_keys($wheres), 
                array_values($wheres)
            ));
        }

        // Execute Statement
        $error = false;
        if (($stmt = $this->connection->prepare($query)) !== false) {
            foreach ($prepare AS $key => $value) {
                $stmt->bindValue($key, $value);
            }
            if (($result = $stmt->execute()) === false) {
                $error = true;
            }
        } else {
            $error = true;
        }

        // Finish
        $this->lastQuery = $stmt? $stmt->getSQL(): $query;
        if ($error) {
            $this->lastError = $this->connection->lastErrorMsg();
            $this->lastResult = false;
        } else {
            $this->lastError = null;
            $this->lastResult = true;
        }

        // Return
        return $this->connection->changes();
    }

    /**
     * @inheritDoc
     */
    public function delete(string $schema, array $where = []): int
    {
        $wheres = array_combine(
            array_keys($where), array_map(fn($key) => ":where_$key", array_keys($where))
        );
        $prepare = array_combine(array_values($wheres), array_values($where));

        // Prepare Query
        $query   = "DELERE FROM $schema";
        if (!empty($wheres))  {
            $query .= ' WHERE ' . implode(', ', array_map(
                fn($key, $val) => "$key=$val", 
                array_keys($wheres), 
                array_values($wheres)
            ));
        }

        // Execute Statement
        $error = false;
        if (($stmt = $this->connection->prepare($query)) !== false) {
            foreach ($prepare AS $key => $value) {
                $stmt->bindValue($key, $value);
            }
            if (($result = $stmt->execute()) === false) {
                $error = true;
            }
        } else {
            $error = true;
        }

        // Finish
        $this->lastQuery = $stmt? $stmt->getSQL(): $query;
        if ($error) {
            $this->lastError = $this->connection->lastErrorMsg();
            $this->lastResult = false;
        } else {
            $this->lastError = null;
            $this->lastResult = true;
        }

        // Return
        return $this->connection->changes();
    }

    /**
     * Begin a new transaction.
     *
     * @return void
     */
    public function begin(): void
    {
        if ($this->transaction) {
            $this->rollback();
            //@todo
            throw new \Exception('Multiple transactions on the same connection are not supported.');
        }
        $this->getConnection()->exec('BEGIN;');
        $this->transaction = true;
    }

    /**
     * Commit an existing transaction.
     *
     * @return void
     */
    public function commit(): void
    {
        if (!$this->transaction) {
            //@todo
            throw new \Exception('No transaction available to commit.');
        }
        $this->getConnection()->exec('COMMIT;');
        $this->transaction = false;
    }

    /**
     * Rollback an existing transaction.
     *
     * @return void
     */
    public function rollback(): void
    {
        if (!$this->transaction) {
            //@todo
            throw new \Exception('No transaction available to rollback.');
        }
        $this->getConnection()->exec('ROLLBACK;');
        $this->transaction = false;
    }

    /**
     * Check if transaction has been started.
     *
     * @return boolean
     */
    public function inTransaction(): bool
    {
        return $this->transaction;
    }

}
