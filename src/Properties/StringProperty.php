<?php declare(strict_types=1);

namespace Crate\Database\Properties;

class StringProperty extends Property
{

    /**
     * Create a new Schema StringProperty.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        parent::__construct($name, 'string');
    }

    /**
     * Set Property exact length.
     *
     * @param integer $length
     * @return static
     */
    public function length(int $length): static
    {
        $this->minLength = $length;
        $this->maxLength = $length;
        return $this;
    }

    /**
     * Set Property minimum length.
     *
     * @param integer $length
     * @return static
     */
    public function minLength(int $length): static
    {
        $this->minLength = $length;
        return $this;
    }

    /**
     * Set Property maximum length.
     *
     * @param integer $length
     * @return static
     */
    public function maxLength(int $length): static
    {
        $this->maxLength = $length;
        return $this;
    }

    /**
     * Set Property possible values.
     *
     * @param array $values
     * @return static
     */
    public function enum(array $values): static
    {
        $this->enums = $values;
        return $this;
    }

    /**
     * Set Property unique state.
     *
     * @return static
     */
    public function unique(): static
    {
        $this->unique = true;
        return $this;
    }

}
