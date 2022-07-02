<?php declare(strict_types=1);

namespace Crate\Database\Migrations;

use Crate\Database\Properties\Property;
use Crate\Database\Properties\StringProperty;
use Crate\Database\Schema;
use stdClass;

class SchemaEditor extends SchemaBuilder
{

    /**
     * Original Schema
     *
     * @var Schema
     */
    public Schema $originalSchema;

    /**
     * Original Properties
     *
     * @var object
     */
    public object $originalProperties;

    /**
     * Changed Properties
     *
     * @var array
     */
    public array $changedProperties = [];

    /**
     * Create a new SchemaEditor instance.
     * 
     * @param Schema $schema The existing Schema structure.
     */
    public function __construct(Schema $schema)
    {
        parent::__construct($schema->name);

        $this->originalSchema = $schema;
        $this->driver = $schema->options->driver;
        $this->id = $schema->_id;
        $this->title = $schema->title;
        $this->description = $schema->description;
        
        foreach ($schema->options AS $key => $value) {
            $this->$key = $value;
        }

        $this->originalProperties = $schema->properties;
    }

    /**
     * Check if Property exists.
     *
     * @param string $name
     * @return boolean
     */
    public function propertyExists(string $name): bool
    {
        if (parent::propertyExists($name)) {
            return true;
        } else {
            if (property_exists($this->originalProperties, $name)) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Select Property to change.
     *
     * @param string $name
     * @return void
     */
    public function property(string $name)
    {
        if (!property_exists($this->originalProperties, $name)) {
            throw new \Exception("The property name '$name' does not exist."); //@todo
        }
        $namespace = substr(Property::class, 0, strrpos(Property::class, '\\')+1);

        $property = $this->originalProperties->$name;
        $propertyClass = $namespace. ucfirst($property->type) . 'Property';

        // Create Instance
        $instance = new $propertyClass($name);
        foreach ($property AS $key => $value) {
            if ($key === 'default') {
                if ($value instanceof stdClass) {
                    $value = (array) $value;
                } else if (is_string($value) && ($value[0] === '[' || $value[0] === '{')) {
                    $value = json_decode($value, true);
                }
            }

            if (method_exists($instance, $key)) {
                $instance->{$key}($value);
            }
        }

        // Set & Return
        $this->properties[$name] = $instance;
        $this->changedProperties[] = ['replace', $name, $name];
        return $instance;
    }

    /**
     * Rename a Property.
     *
     * @param string $old
     * @param string $new
     * @return static
     */
    public function rename(string $old, string $new): static
    {
        $this->changedProperties[] = ['rename', $old, $new];
        return $this;
    }

    /**
     * Replace a Property with a different one.
     *
     * @param string $old
     * @param string $new
     * @param \Closure|null $converter
     * @return static
     */
    public function replace(string $old, string $new, ?\Closure $converter = null): static
    {
        $this->changedProperties[] = ['replace', $old, $new, $converter];
        return $this;
    }

    /**
     * Remove a Property.
     *
     * @param string $old
     * @return static
     */
    public function remove(string $old): static
    {
        $this->changedProperties[] = ['remove', $old];
        return $this;
    }

    /**
     * Convert SchemaEditor to SchemaBuilder
     *
     * @param string|null $name
     * @return SchemaBuilder
     */
    public function convertToBuilder(?string $name = null): SchemaBuilder
    {
        $schema = new SchemaBuilder($name? $name: $this->name);

        $schema->driver = $this->driver;
        $schema->storage = $this->storage;
        $schema->id = $this->id;
        $schema->title = $this->title;
        $schema->description = $this->description;
        $schema->primaryKey = $this->primaryKey;
        $schema->primaryKeyFormat = $this->primaryKeyFormat;
        $schema->created = $this->created;
        $schema->branches = $this->branches;
        $schema->history = $this->history;
        $schema->document = $this->document;

        $removed = array_filter(array_map(function ($action) {
            if ($action[0] === 'rename') {
                return $action[1];
            } else if ($action[0] === 'remove') {
                return $action[1];
            } else if ($action[0] === 'replace') {
                return $action[1] !== $action[2]? $action[1]: null;
            } else {
                return null;
            }
        }, $this->changedProperties));

        foreach ($this->originalProperties AS $name => $_) {
            if (in_array($name, $removed)) {
                continue;
            }
            $schema->properties[$name] = $this->property($name);
        }
        $schema->properties = array_merge($schema->properties, $this->properties);

        return $schema;
    }

}
