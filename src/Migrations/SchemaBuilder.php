<?php declare(strict_types=1);

namespace Crate\Database\Migrations;

use Crate\Database\Properties\ArrayProperty;
use Crate\Database\Properties\BooleanProperty;
use Crate\Database\Properties\IntegerProperty;
use Crate\Database\Properties\NumberProperty;
use Crate\Database\Properties\ObjectProperty;
use Crate\Database\Properties\Property;
use Crate\Database\Properties\StringProperty;

class SchemaBuilder
{

    /**
     * The Schema storage is required to be used within the Repository system. 
     * You can disable the Schema storage, when this Schema is not supported to 
     * be used this way (ex.: the migration storage disables the storage).
     *
     * @var boolean
     */
    public bool $storage = true;

    /**
     * Schema Database driver.
     *
     * @var string
     */
    public string $driver = 'default';

    /**
     * Schema ID
     *
     * @var string|null
     */
    public ?string $id = null;

    /**
     * Schema Name
     *
     * @var string
     */
    public string $name;

    /**
     * Schema Title
     *
     * @var string|null
     */
    public ?string $title = null;

    /**
     * Schema Description
     *
     * @var string|null
     */
    public ?string $description = null;

    /**
     * Schema Primary Key property name.
     *
     * @var string
     */
    public string $primaryKey = 'uuid';

    /**
     * Schema Primary Key property format. Possible values:
     * 'id'         Increment Digit
     * 'uid'        Random Unique ID
     * 'uuid'       Random Universally Unique IDentifier v4
     *
     * @var string
     */
    public string $primaryKeyFormat = 'uuid';

    /**
     * Schema Creation property name (null to disable).
     *
     * @var string|null
     */
    public ?string $created = 'created_at';

    /**
     * Schema Last-Update property name (null to disable).
     *
     * @var string|null
     */
    public ?string $updated = 'updated_at';

    /**
     * Schema dynamic property name.
     * Dynamic-declared Schemes can contain any values, the dynamic attribute 
     * must be set to an dynamic object property to work.
     *
     * @var ?string
     */
    public ?string $dynamic = null;

    /**
     * Schema supports multiple branches.
     *
     * @var boolean
     */
    public bool $branches = false;

    /**
     * Schema supports revisions / history.
     *
     * @var boolean
     */
    public bool $history = false;

    /**
     * Schema supports custom Document model.
     *
     * @var string|null
     */
    public ?string $document = null;

    /**
     * Schema Properties
     * 
     * @var Property[]
     */
    public array $properties = [];

    /**
     * Schema Unique Indexed
     * 
     * @var string[][]
     */
    public array $uniques = [];

    /**
     * Create a new SchemaBuilder instance.
     * 
     * @param string $name The desired Schema name.
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * String Representation of this Schema.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJSON();
    }

    /**
     * Export Schema as JSON Schema
     *
     * @return array
     */
    public function toJSON(bool $prettyPrint = false)
    {
        return json_encode($this->toArray(), $prettyPrint? \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES: 0);
    }

    /**
     * Export Schema as Array
     *
     * @return array
     */
    public function toArray(): array
    {
        if (!$this->storage) {
            throw new \Exception("The Schema '$this->name' does not support to be stored."); //@todo
        }
        $properties = [];
        $required = [];

        // Primary Key Property
        if ($this->primaryKeyFormat === 'id') {
            $properties[$this->primaryKey] = [
                'type' => 'integer'
            ];
        } else {
            $properties[$this->primaryKey] = [
                'type' => 'string',
                'format' => $this->primaryKeyFormat
            ];
        }

        // Loop Properties
        foreach ($this->properties AS $name => $property) {
            if ($property->required) {
                $required[] = $name;
            }
            $properties[$name] = $property->toArray()[$name];
        }

        // Created Property
        if ($this->created) {
            $properties[$this->created] = [
                'type' => 'string',
                'format' => 'date-time'
            ];
        }

        // Updated Property
        if ($this->updated) {
            $properties[$this->updated] = [
                'type' => 'string',
                'format' => 'date-time'
            ];
        }

        // Return Schema 
        return [
            '$schema'           => "https://json-schema.org/draft/2020-12/schema",
            '$id'               => $this->id ?? 'custom_' . $this->name,
            'name'              => $this->name,
            'title'             => $this->title ?? $this->name,
            'description'       => $this->description ?? '',
            'type'              => 'object',
            'internalConfig'    => [
                'driver'            => $this->driver,
                'dynamic'           => $this->dynamic,
                'branches'          => $this->branches,
                'history'           => $this->history,
                'document'          => $this->document,
                'uniques'           => $this->uniques
            ],
            'internalProperties'=> [
                'primary'           => $this->primaryKey,
                'created'           => $this->created,
                'updated'           => $this->updated
            ],
            'properties'        => $properties,
            'required'          => $required
        ];
    }

    /**
     * Check if Property exists.
     *
     * @param string $name
     * @return boolean
     */
    public function propertyExists(string $name): bool
    {
        if (array_key_exists($name, $this->properties)) {
            return true;
        } else if ($this->primaryKey === $name) {
            return true;
        } else if ($this->created === $name) {
            return true;
        } else if ($this->updated === $name) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Create a new StringProperty.
     *
     * @param string $name
     * @return StringProperty
     */
    public function string(string $name): StringProperty
    {
        if ($this->propertyExists($name)) {
            throw new \Exception("The property name '$name' does already exist.");  //@todo
        }

        /** @var StringProperty */
        $this->properties[$name] = new StringProperty($name);
        return $this->properties[$name];
    }

    /**
     * Create a new ArrayProperty.
     *
     * @param string $name
     * @return ArrayProperty
     */
    public function array(string $name): ArrayProperty
    {
        if ($this->propertyExists($name)) {
            throw new \Exception("The property name '$name' does already exist.");  //@todo
        }

        /** @var ArrayProperty */
        $this->properties[$name] = new ArrayProperty($name);
        return $this->properties[$name];
    }

    /**
     * Create a new ObjectProperty.
     *
     * @param string $name
     * @return ObjectProperty
     */
    public function object(string $name): ObjectProperty
    {
        if ($this->propertyExists($name)) {
            throw new \Exception("The property name '$name' does already exist.");  //@todo
        }

        /** @var ObjectProperty */
        $this->properties[$name] = new ObjectProperty($name);
        return $this->properties[$name];
    }

    /**
     * Create a new NumberProperty.
     *
     * @param string $name
     * @return NumberProperty
     */
    public function number(string $name): NumberProperty
    {
        if ($this->propertyExists($name)) {
            throw new \Exception("The property name '$name' does already exist.");  //@todo
        }

        /** @var NumberProperty */
        $this->properties[$name] = new NumberProperty($name);
        return $this->properties[$name];
    }

    /**
     * Create a new IntegerProperty.
     *
     * @param string $name
     * @return IntegerProperty
     */
    public function integer(string $name): IntegerProperty
    {
        if ($this->propertyExists($name)) {
            throw new \Exception("The property name '$name' does already exist.");  //@todo
        }

        /** @var IntegerProperty */
        $this->properties[$name] = new IntegerProperty($name);
        return $this->properties[$name];
    }

    /**
     * Create a new BooleanProperty.
     *
     * @param string $name
     * @return BooleanProperty
     */
    public function boolean(string $name): BooleanProperty
    {
        if ($this->propertyExists($name)) {
            throw new \Exception("The property name '$name' does already exist.");  //@todo
        }
        
        /** @var BooleanProperty */
        $this->properties[$name] = new BooleanProperty($name);
        return $this->properties[$name];
    }

    /**
     * Create a new UID StringProperty.
     *
     * @param string $name
     * @return StringProperty
     */
    public function uid(string $name): StringProperty
    {
        if ($this->propertyExists($name)) {
            throw new \Exception("The property name '$name' does already exist.");  //@todo
        }

        /** @var StringProperty */
        $this->properties[$name] = new StringProperty($name);
        $this->properties[$name]->format('uid');
        $this->properties[$name]->length(28);
        $this->properties[$name]->unique();
        return $this->properties[$name];
    }

    /**
     * Create a new UUID StringProperty.
     *
     * @param string $name
     * @return StringProperty
     */
    public function uuid(string $name): StringProperty
    {
        if ($this->propertyExists($name)) {
            throw new \Exception("The property name '$name' does already exist.");  //@todo
        }
        
        /** @var StringProperty */
        $this->properties[$name] = new StringProperty($name);
        $this->properties[$name]->format('uuid');
        $this->properties[$name]->length(36);
        $this->properties[$name]->unique();
        return $this->properties[$name];
    }

    /**
     * Create a new UNIX Timestamp IntegerProperty.
     *
     * @param string $name
     * @return IntegerProperty
     */
    public function timestamp(string $name): IntegerProperty
    {
        if ($this->propertyExists($name)) {
            throw new \Exception("The property name '$name' does already exist.");  //@todo
        }

        /** @var StringProperty */
        $this->properties[$name] = new IntegerProperty($name);
        $this->properties[$name]->format('timestamp');
        $this->properties[$name]->unsigned();
        return $this->properties[$name];
    }

    /**
     * Create a new Time StringProperty.
     *
     * @param string $name
     * @return StringProperty
     */
    public function time(string $name): StringProperty
    {
        if ($this->propertyExists($name)) {
            throw new \Exception("The property name '$name' does already exist.");  //@todo
        }

        /** @var StringProperty */
        $this->properties[$name] = new StringProperty($name);
        $this->properties[$name]->format('time');
        return $this->properties[$name];
    }

    /**
     * Create a new Date StringPRoperty.
     *
     * @param string $name
     * @return StringProperty
     */
    public function date(string $name): StringProperty
    {
        if ($this->propertyExists($name)) {
            throw new \Exception("The property name '$name' does already exist.");  //@todo
        }

        /** @var StringProperty */
        $this->properties[$name] = new StringProperty($name);
        $this->properties[$name]->format('date');
        return $this->properties[$name];
    }

    /**
     * Create a new DateTime StringProperty.
     *
     * @param string $name
     * @return StringProperty
     */
    public function datetime(string $name): StringProperty
    {
        if ($this->propertyExists($name)) {
            throw new \Exception("The property name '$name' does already exist.");  //@todo
        }

        /** @var StringProperty */
        $this->properties[$name] = new StringProperty($name);
        $this->properties[$name]->format('date-time');
        return $this->properties[$name];
    }

    /**
     * Create a new eMail StringProperty.
     *
     * @param string $name
     * @param bool $rfc6531 Support email addresses according to RFC6531.
     * @return StringProperty
     */
    public function email(string $name, bool $rfc6531 = false): StringProperty
    {
        if ($this->propertyExists($name)) {
            throw new \Exception("The property name '$name' does already exist.");  //@todo
        }

        /** @var StringProperty */
        $this->properties[$name] = new StringProperty($name);
        $this->properties[$name]->format($rfc6531? 'idn-email': 'email');
        return $this->properties[$name];
    }

    /**
     * Create a new IPv4 StringProperty.
     *
     * @param string $name
     * @return StringProperty
     */
    public function ipv4(string $name): StringProperty
    {
        if ($this->propertyExists($name)) {
            throw new \Exception("The property name '$name' does already exist.");  //@todo
        }

        /** @var StringProperty */
        $this->properties[$name] = new StringProperty($name);
        $this->properties[$name]->format('ipv4');
        return $this->properties[$name];
    }

    /**
     * Create a new IPv6 StringProperty.
     *
     * @param string $name
     * @return StringProperty
     */
    public function ipv6(string $name): StringProperty
    {
        if ($this->propertyExists($name)) {
            throw new \Exception("The property name '$name' does already exist.");  //@todo
        }

        /** @var StringProperty */
        $this->properties[$name] = new StringProperty($name);
        $this->properties[$name]->format('ipv6');
        return $this->properties[$name];
    }

    /**
     * Create a new Unique Index based on one or more columns
     *
     * @param string|array $properties
     * @return static
     */
    public function unique(string|array $properties): static
    {
        $properties = is_array($properties)? $properties: [$properties];

        $missing = array_filter($properties, fn($prop) => !isset($this->properties[$prop]));
        if (count($missing) > 0) {
            //@todo
            throw new \Exception("The following properties for this unique index are unknown: " . implode(', ', $missing));
        }

        $this->uniques[] = is_array($properties)? $properties: [$properties];
        return $this;
    }

}
