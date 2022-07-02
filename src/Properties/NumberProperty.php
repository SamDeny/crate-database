<?php declare(strict_types=1);

namespace Crate\Database\Properties;

class NumberProperty extends Property
{

    /**
     * Create a new Schema NumberProperty.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        parent::__construct($name, 'number');
    }

    /**
     * Set Property minimum value.
     *
     * @param float $num
     * @return static
     */
    public function min(int|float $num): static
    {
        $this->min = intval($num);
        return $this;
    }
    
    /**
     * Set Property maximum value.
     *
     * @param float $num
     * @return static
     */
    public function max(int|float $num): static
    {
        $this->max = intval($num);
        return $this;
    }

    /**
     * Set Property unsigned state.
     *
     * @return static
     */
    public function unsigned(): static
    {
        $this->unsigned = true;
        return $this;
    }

    /**
     * Set Property signed state.
     *
     * @return static
     */
    public function signed(): static
    {
        $this->unsigned = false;
        return $this;
    }

}
