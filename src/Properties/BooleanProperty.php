<?php declare(strict_types=1);

namespace Crate\Database\Properties;

class BooleanProperty extends Property
{

    /**
     * Create a new Schema BooleanProperty.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        parent::__construct($name, 'boolean');
    }

    /**
     * Set Property default value.
     *
     * @param mixed $default
     * @return static
     */
    public function default(mixed $default): static
    {
        $this->details['default'] = !!$default;
        return $this;
    }

}
