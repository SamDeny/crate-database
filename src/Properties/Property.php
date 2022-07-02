<?php declare(strict_types=1);

namespace Crate\Database\Properties;

abstract class Property
{

    /**
     * Property Name.
     *
     * @var string
     */
    public string $name;

    /**
     * Property Type.
     *
     * @var string
     */
    public string $type;

    /**
     * Property Details.
     *
     * @var array
     */
    public array $details = [];

    /**
     * Create a new Schema Property.
     *
     * @param string $name
     * @param string $type
     */
    public function __construct(string $name, string $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * Array Representation of this property
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = ['type' => $this->type];

        foreach ($this->details AS $key => $value) {
            $result[$key] = $value;
        }

        return [$this->name => $result];
    }

    /**
     * Magic - Get Property detail.
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        if ($name === 'name' || $name === 'type') {
            return $this->$name;
        } else {
            return array_key_exists($name, $this->details)? $this->details[$name]: null;
        }
    }

    /**
     * Magic - Check if Property detail exists.
     *
     * @param string $name
     * @return boolean
     */
    public function __isset(string $name): bool
    {
        if ($name === 'name' || $name === 'type') {
            return true;
        } else {
            return array_key_exists($name, $this->details);
        }
    }

    /**
     * Magic - Unset existing Property detail.
     *
     * @param string $name
     * @return void
     */
    public function __unset(string $name): void
    {
        if ($name === 'name' || $name === 'type') {
            throw new \Exception("The name and type of a property cannot be unset.");
        } else {
            unset($this->details[$name]);
        }
    }

    /**
     * Set Property Description
     *
     * @param string $description
     * @return static
     */
    public function describe(string $description): static
    {
        $this->details['description'] = $description;
        return $this;
    }

    /**
     * Set Property Format.
     *
     * @param string $format
     * @return static
     */
    public function format(string $format): static
    {
        $this->details['format'] = $format;
        return $this;
    }

    /**
     * Set Property default value.
     *
     * @param mixed $default
     * @return static
     */
    public function default(mixed $default): static
    {
        $this->details['default'] = $default;
        return $this;
    }

    /**
     * Set Property required state to true.
     *
     * @return static
     */
    public function required(): static
    {
        $this->details['required'] = true;
        return $this;
    }

    /**
     * Set Property required state to false.
     *
     * @return static
     */
    public function optional(): static
    {
        unset($this->details['required']);
        return $this;
    }

}
