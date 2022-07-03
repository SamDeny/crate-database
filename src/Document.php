<?php declare(strict_types=1);

namespace Crate\Database;

class Document
{

    /**
     * Switch if the document exists or not.
     *
     * @var boolean
     */
    protected bool $exists = false;

    /**
     * Switch if the document has been changed or not.
     *
     * @var boolean
     */
    protected bool $dirty = false;

    /**
     * Document Properties
     *
     * @var array
     */
    protected array $properties = [];

    /**
     * Create a new empty Document.
     */
    public function __construct()
    {
        
    }

    /**
     * Clone this Document.
     *
     * @return void
     */
    public function __clone()
    {
        $this->exists = false;
        $this->dirty = true;
    }

    /**
     * Get a Document Property value.
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        $method = 'get' . str_replace('_', '', ucwords($name, '_'));
        
        if (method_exists($this, $method)) {
            return $this->{$method}();
        } else {
            return array_key_exists($name, $this->properties)? $this->properties[$name]: null;
        }
    }

    /**
     * Set a Document Property value.
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set(string $name, mixed $value): void
    {
        $method = 'set' . str_replace('_', '', ucwords($name, '_'));
        
        $this->dirty = true;
        if (method_exists($this, $method)) {
            $this->{$method}($value);
        } else {
            $this->properties[$name] = $value;
        }
    }

    /**
     * Check if a Document Property exists.
     *
     * @param string $name
     * @return boolean
     */
    public function __isset(string $name): bool
    {
        return array_key_exists($name, $this->properties);
    }

    /**
     * Unset a Document Property.
     *
     * @param string $name
     * @return void
     */
    public function __unset(string $name): void
    {
        unset($this->properties[$name]);
    }

    /**
     * Return Array representation of the document.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->properties;
    }

    /**
     * Fill document.
     *
     * @param array|object $dataset
     * @return static
     */
    public function fill(array|object $dataset): static
    {
        return $this;
    }

    /**
     * Get Document Type
     *
     * @return string Document type, either 'original' or 'translation'.
     */
    public function type(): string
    {
        //@todo
        return '';
    }

    /**
     * Get Document State
     *
     * @return string Document state, either 'main', 'revision' or <custom>.
     */
    public function state(): string
    {
        //@todo
        return '';
    }

    /**
     * Return Revisions of this document.
     *
     * @return array
     */
    public function revisions(): array
    {
        //@todo
        return [];
    }

    /**
     * Rollback to an older version of this document.
     *
     * @return boolean
     */
    public function rollback(Document $document): bool
    {
        //@todo
        return false;
    }
    
}
