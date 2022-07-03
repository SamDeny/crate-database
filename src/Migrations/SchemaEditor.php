<?php declare(strict_types=1);

namespace Crate\Database\Migrations;

use Crate\Database\Properties\Property;
use Crate\Database\Schema;

class SchemaEditor extends SchemaBuilder
{

    /**
     * Original Schema
     *
     * @var Schema
     */
    public Schema $originalSchema;

    /**
     * Original Schema Properties
     * 
     * @var Property[]
     */
    public array $originalProperties = [];
    
    /**
     * Changed Schema Properties
     * 
     * @var array{0: string, 1: ?Property, 2?: ?\Closure}
     */
    public array $changedProperties = [];

    /**
     * Added Schema Properties
     * 
     * @var Property[]
     */
    public array $properties = [];

    /**
     * Original Schema Uniques
     *
     * @var array
     */
    public array $originalUniques = [];

    /**
     * Create a new SchemaEditor instance.
     * 
     * @param Schema $schema The existing Schema structure.
     */
    public function __construct(Schema $schema)
    {
        $this->originalSchema = $schema;

        // Restore Schema Details
        $this->driver = $schema->internalConfig->driver;
        $this->id = $schema->_id;
        $this->name = $schema->name;
        $this->title = $schema->title;
        $this->description = $schema->description;
        $this->primaryKey = $schema->internalProperties->primary;
        $this->primaryKeyFormat = $schema->properties->{$this->primaryKey}->format;
        $this->created = $schema->internalProperties->created;
        $this->updated = $schema->internalProperties->updated;
        $this->branches = $schema->internalConfig->branches;
        $this->history = $schema->internalConfig->history;
        $this->document = $schema->internalConfig->document;

        // Set original Schema Properties
        $properties = (array) $schema->properties;

        unset($properties[$this->primaryKey]);

        if ($this->created) {
            unset($properties[$this->created]);
        }

        if ($this->updated) {
            unset($properties[$this->updated]);
        }

        $namespace = substr(Property::class, 0, strrpos(Property::class, '\\')+1);
        foreach ($properties AS $name => &$property) {
            $propertyClass = $namespace. ucfirst($property->type) . 'Property';

            /** @var Property */
            $instance = new $propertyClass($name);
            foreach ($property AS $key => $value) {
                if ($key === 'default') {
                    if ($value instanceof \stdClass) {
                        $value = (array) $value;
                    }
                }
    
                if (method_exists($instance, $key)) {
                    $instance->{$key}($value);
                }
            }
            $this->originalProperties[$name] = $instance;
        }
        $this->originalUniques = $schema->internalConfig->uniques;
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
            if (array_key_exists($name, $this->originalProperties)) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Select a Property to change.
     *
     * @param string $name
     * @return Property
     */
    public function property(string $name): Property
    {
        if (!array_key_exists($name, $this->originalProperties)) {
            throw new \Exception("The property name '$name' does not exist."); //@todo
        }

        $property = clone $this->originalProperties[$name];
        $this->changedProperties[] = [$name, $property, null];
        return $property;
    }

    /**
     * Rename a Property.
     *
     * @param string $name The existing name of the desired property.
     * @param string $newName The new name of the desired property.
     * @return static
     */
    public function rename(string $name, string $newName): static
    {
        if (!array_key_exists($name, $this->originalProperties)) {
            throw new \Exception("The property name '$name' does not exist."); //@todo
        }
        if ($this->propertyExists($newName)) {
            throw new \Exception("The property name '$newName' does already exist."); //@todo
        }

        $property = clone $this->originalProperties[$name];
        $property->name = $newName;
        $this->changedProperties[] = [$name, $property];
        return $this;
    }

    /**
     * Replace a Property with a different one.
     *
     * @param string $name The existing name of the desired property.
     * @param string $newName The new name of the desired new-created property.
     * @param \Closure|null $converter An additional function to copy the values
     *                      of the old property to the new one.
     * @return static
     */
    public function replace(string $name, string $newName, ?\Closure $converter = null): static
    {
        if (!array_key_exists($name, $this->originalProperties)) {
            throw new \Exception("The property name '$name' does not exist."); //@todo
        }
        if (!$this->propertyExists($newName)) {
            throw new \Exception("The property name '$newName' does not exist."); //@todo
        }

        $this->changedProperties[] = [$name, $this->properties[$newName], $converter];
        return $this;
    }

    /**
     * Remove a Property.
     *
     * @param string $name The existing name of the desired property.
     * @return static
     */
    public function remove(string $name): static
    {
        if (!array_key_exists($name, $this->originalProperties)) {
            throw new \Exception("The property name '$name' does not exist."); //@todo
        }

        $this->changedProperties[] = [$name, null];
        return $this;
    }

    /**
     * Evaluate original, changed and added Properties.
     *
     * @return Property[]
     */
    public function evaluateProperties(): array
    {
        $properties = array_merge($this->originalProperties, $this->properties);

        foreach ($this->changedProperties AS $details) {
            $old = $details[0];
            $new = $details[1];

            unset($properties[$old]);
            if (!is_null($new)) {
                $properties[$new->name] = $details[1];
            }
        }

        return $properties;
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
        $schema->name = $this->name;
        $schema->title = $this->title;
        $schema->description = $this->description;
        $schema->primaryKey = $this->primaryKey;
        $schema->primaryKeyFormat = $this->primaryKeyFormat;
        $schema->created = $this->created;
        $schema->updated = $this->updated;
        $schema->branches = $this->branches;
        $schema->history = $this->history;
        $schema->document = $this->document;
        $schema->properties = $this->evaluateProperties();
        $schema->uniques = array_merge($this->originalUniques, $this->uniques);

        return $schema;
    }

}
