<?php declare(strict_types=1);

use Crate\Database\Contracts\MigrationContract;
use Crate\Database\Document;
use Crate\Database\Migrations\Migrator;
use Crate\Database\Migrations\SchemaBuilder;
use Crate\Database\Migrations\SchemaEditor;
use Crate\Database\Repository;

return new class implements MigrationContract {
    
    /**
     * @inheritDoc
     */
    public function title(): string
    {
        return 'Install all kind of Properties';
    }

    /**
     * @inheritDoc
     */
    public function install(Migrator $migrator): void
    {

        $migrator->create('users', function (SchemaBuilder $schema) {

            $schema->string('username')->unique()->required();
            $schema->email('email')->unique()->required();
            $schema->string('display_name');

        });

        // Ensures that the above command is executed before the next statement is called
        $migrator->commit();


    }
    
    /**
     * @inheritDoc
     */
    public function uninstall(Migrator $migrator): void
    {

    }

};
