<?php declare(strict_types=1);

namespace Crate\Database;

use Crate\Database\Contracts\DriverContract;

class Repository
{

    /**
     * Repository Schema
     *
     * @var Schema
     */
    protected Schema $schema;

    /**
     * Repository Branch
     *
     * @var string
     */
    protected string $branch;

    /**
     * Repository Driver Provider
     *
     * @var string
     */    
    protected string $provider;

    /**
     * Repository Driver Instance
     *
     * @var DriverContract
     */
    protected DriverContract $driver;

    /**
     * Create a new Repository instance.
     *
     * @param string|Schema $schema The desired Repository schema. 
     * @param string|null $branch The desires Repository branch or null to set 
     *                    the default branch for this repository access.
     */
    public function __construct(string|Schema $schema, ?string $branch = null)
    {
        if (is_string($schema)) {
            if (($schema = Schema::get($schema)) === null) {
                //@todo
                throw new \Exception("The passed schema name '$schema' does not exist.");
            }
        }
        $this->schema = $schema;

        if (!is_null($branch) && !$this->schema->supports('branches')) {
            //@todo
            throw new \Exception("The passed schema '$schema' does not support different branches.");
        }
        $this->branch = $branch ?? 'default';

        // Build Driver
        //@todo
        if ($schema->driver === 'default' || $schema->driver === 'crate') {
            $driver = config('database.' . $schema->driver);
        } else {
            $driver = $schema->driver;
        }

        $config = config('database.drivers.' . $driver);
        $provider = $config['provider'];
        unset($config['provider']);

        $this->provider = $provider;
        $this->driver = new $this->provider(...$config);
    }

    /**
     * Receive or Change Repository branch.
     *
     * @param string|null $branch The desired branch to switch to or null to 
     *                    get the current branch.
     * @return string|static
     */
    public function branch(?string $branch = null): string|static
    {
        if (is_null($branch)) {
            return $this->branch;
        } else {
            if (!$this->schema->supports('branches')) {
                //@todo
                throw new \Exception("The passed schema '{$this->schema->name}' does not support different branches.");
            }

            if ($branch === $this->branch) {
                $self = clone $this;
                return $self;
            } else {
                $self = clone $this;
                $self->branch = $branch;
                return $self;
            }
        }
    }

    /**
     * Validates a single document depending on the assigned Schema.
     *
     * @param Document $document
     * @return boolean
     */
    public function validate(Document $document): bool
    {
        //@todo
        return true;
    }

    /**
     * Select a single document by passing it's unique identifier.
     *
     * @param string $id
     * @return ?Document
     */
    public function select(string $id): ?Document
    {
        $query = new Query;
        $query->where($this->schema->primaryKey, $id);
        $query->limit(1);
        
        $result = $this->driver->select($this->schema->name, $query);
        if ($result) {
            $document = new Document($this->schema);
            return $document->fill($result[0]);
        } else {
            return null;
        }
    }

    /**
     * Select multiple documents by simple where clauses.
     *
     * @param array $where
     * @param integer $limit
     * @param integer $offset
     * @return Document[]
     */
    public function selectBy(array $where, int $limit = 0, int $offset = 0)
    {
        $query = new Query;
        $query->where($where);
        $query->limit($limit);
        $query->offset($offset);

        $results = $this->driver->select($this->schema->name, $query);
        if ($results) {
            $documents = array_map(fn($result) => (new Document($this->schema))->fill($result), $results);
            return $documents;
        } else {
            return [];
        }
    }

    /**
     * Query multiple documents by an extended query instance.
     *
     * @param Query $query
     * @return Document[]
     */
    public function query(Query $query)
    {
        $results = $this->driver->select($this->schema->name, $query);
        if ($results) {
            $documents = array_map(fn($result) => (new Document($this->schema))->fill($result), $results);
            return $documents;
        } else {
            return [];
        }
    }

    /**
     * Insert one or more documents.
     *
     * @param array|Document $documents
     * @return int
     */
    public function insert(array|Document $documents): int
    {
        $documents = !is_array($documents)? [$documents]: $documents;

        $results = [];
        foreach ($documents AS $document) {
            $result = $document->toArray();

            if ($this->schema->primaryKeyFormat !== 'id') {
                if ($this->schema->primaryKeyFormat === 'uid') {
                    $result[$this->schema->primaryKey] = bin2hex(random_bytes(14));
                } else if ($this->schema->primaryKeyFormat === 'uuid') {
                    $result[$this->schema->primaryKey] = uuid_create(\UUID_TYPE_TIME);
                }
            }

            $results[] = $result;
        }

        return $this->driver->insert($this->schema->name, $results);
    }

    /**
     * Update one or more documents.
     *
     * @param array|Document $documents
     * @return int
     */
    public function update(array|Document $documents)
    {
        $primaryKey = $this->schema->primaryKey;
        $documents = !is_array($documents)? [$documents]: $documents;

        $count = 0;
        foreach ($documents AS $document) {
            $result = $document->toArray();
            $where = [$primaryKey => $result[$primaryKey]];
            
            unset($result[$primaryKey]);
            if ($this->schema->created) {
                unset($result[$this->schema->created]);
            }
            if ($this->schema->updated) {
                unset($result[$this->schema->updated]);
            }

            $count += $this->driver->update($this->schema->name, $result, $where);
        }

        return $count;
    }

    /**
     * Replace (or Insert) one or more documents.
     *
     * @return int
     */
    public function replace(array|Document $documents)
    {

    }

    /**
     * Delete one or more documents.
     *
     * @return int
     */
    public function delete(array|Document $documents)
    {

    }

}
