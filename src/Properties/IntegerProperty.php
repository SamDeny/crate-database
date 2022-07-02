<?php declare(strict_types=1);

namespace Crate\Database\Properties;

class IntegerProperty extends Property
{

    /**
     * Create a new Schema IntegerProperty.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        parent::__construct($name, 'integer');
    }

    /**
     * Set Property minimum value.
     *
     * @param integer $num
     * @return static
     */
    public function min(int $num): static
    {
        $this->min = intval($num);
        return $this;
    }
    
    /**
     * Set Property maximum value.
     *
     * @param integer $num
     * @return static
     */
    public function max(int $num): static
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
