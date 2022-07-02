<?php declare(strict_types=1);

namespace Crate\Database\Migrations;

use Crate\Database\Schema;

class SchemaEditor extends SchemaBuilder
{

    /**
     * Original Schema
     *
     * @var Schema
     */
    protected Schema $originalSchema;

    /**
     * Original Properties
     *
     * @var object
     */
    protected object $originalProperties;

    /**
     * Changed Properties
     *
     * @var array
     */
    protected array $changedProperties = [];

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

        $property = $this->originalProperties[$name];
        $propertyClass = ucfirst($property->type) . 'Property';

        // Create Instance
        $instance = new $propertyClass;
        foreach ($property AS $key => $value) {
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

}
