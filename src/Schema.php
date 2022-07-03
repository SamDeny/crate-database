<?php declare(strict_types=1);

namespace Crate\Database;

class Schema
{

    /**
     * Loaded Schemes
     *
     * @var Schema[]
     */
    static protected array $schemes = [];

    /**
     * Load or get a cached Schema.
     *
     * @param string $name
     * @return Schema
     */
    static public function get(string $name): ?Schema
    {
        if (array_key_exists($name, self::$schemes)) {
            return self::$schemes[$name];
        } else {
            $filepath = __DIR__ . '/../storage/schemes/' . $name . '.schema.json';
            if (!file_exists($filepath)) {
                throw new \Exception('');   //@todo
            }

            self::$schemes[$name] = new Schema($filepath);
            return self::$schemes[$name];
        }
    }


    /**
     * Schema Content
     * 
     * @var object
     */
    protected object $schema;
    
    /**
     * Create a new Schema instance.
     *
     * @param string $name
     * @param string|null $filepath
     */
    public function __construct(string $filepath)
    {
        if (!file_exists($filepath)) {
            throw new \Exception("The Schema file '$filepath' does not exist.");   //@todo
        }

        $content = json_decode(file_get_contents($filepath));
        if (!property_exists($content, '$schema')) {
            throw new \Exception("The passed Schema file '$filepath' is invalod or corrupt.");
        }
        
        $this->schema = $content;
    }

    /**
     * Get Schema Detail.
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        if (strpos($name, '0') === '_') {
            $name = '$' . substr($name, 1);
        }

        if ($name === 'primaryKey') {
            return $this->schema->internalProperties->primary;
        } else if ($name === 'primaryKeyFormat') {
            $primaryKey = $this->schema->internalProperties->primary;
            return $this->schema->properties->{$primaryKey}->foramt;
        } else if ($name === 'created') {
            return $this->schema->internalProperties->created;
        } else if ($name === 'updated') {
            return $this->schema->internalProperties->updated;
        }

        if (property_exists($this->schema, $name)) {
            return $this->schema->$name;
        } else {
            return null;
        }
    }

    /**
     * Check if Schema supports a specific feature. 
     *
     * @param string $key
     * @return boolean
     */
    public function supports(string $key): bool
    {
        if (array_key_exists($key, $this->schema->details)) {
            return is_bool($this->schema->details[$key])? $this->schema->details[$key]: false;
        } else {
            return false;
        }
    }

}
