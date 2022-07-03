<?php declare(strict_types=1);

namespace Crate\Database;

class Query
{

    const OPERATORS = [
        '='         => 'eq',
        '=='        => 'eq',
        '!='        => 'neq',
        '<>'        => 'neq',
        '>'         => 'gt',
        '!<'        => 'gt',
        '>='        => 'gteq',
        '<'         => 'lt',
        '!>'        => 'lt',
        '<='        => 'lteq',
        'between'   => 'between',
        'in'        => 'in',
        'not in'    => 'not_in',
        'like'      => 'like',
        'glob'      => 'like_case',
        'is'        => 'eq',
        'is not'    => 'neq',
    ];
    
    /**
     * Create a new Query instance.
     */
    public function __construct()
    {

    }

    /**
     * Set Select columns clause.
     *
     * @param array|string $columns
     * @return static
     */
    public function select(array|string $columns): static
    {

        return $this;
    }

    /**
     * Add where clause.
     *
     * @return static
     */
    public function where(): static
    {

        return $this;
    }

    /**
     * Add order clause.
     *
     * @return static
     */
    public function order(): static
    {

        return $this;
    }

    /**
     * Add offset clause.
     *
     * @return static
     */
    public function offset(): static
    {

        return $this;
    }

    /**
     * Add limit clause.
     *
     * @return static
     */
    public function limit(): static
    {

        return $this;
    }

}
