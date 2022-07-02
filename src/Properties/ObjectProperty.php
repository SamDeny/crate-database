<?php declare(strict_types=1);

namespace Crate\Database\Properties;

class ObjectProperty extends Property
{

    /**
     * Create a new Schema ObjectProperty.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        parent::__construct($name, 'object');
    }

}
