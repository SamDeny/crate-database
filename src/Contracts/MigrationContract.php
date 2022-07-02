<?php declare(strict_types=1);

namespace Crate\Database\Contracts;

use Crate\Database\Migrations\Migrator;

interface MigrationContract
{

    /**
     * Return Migration Title
     *
     * @return string
     */
    function title(): string;

    /**
     * Install Migration
     *
     * @param Migrator $migrator
     * @return void
     */
    function install(Migrator $migrator): void;

    /**
     * Uninstall Migration
     *
     * @param Migrator $migrator
     * @return void
     */
    function uninstall(Migrator $migrator): void;

}
