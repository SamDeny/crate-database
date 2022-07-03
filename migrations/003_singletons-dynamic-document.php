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

        $migrator->create('singletons', function (SchemaBuilder $schema) {

            $schema->dynamic = 'document';
            $schema->object('document');

        });

        // Ensures that the above command is executed before the next statement is called
        $migrator->commit();

        $migrator->select('singletons', function (Repository $repo) {
            
            $singleton = new Document();
            $singleton->someColumn = 'value';

            if ($repo->validate($singleton)) {
                $repo->insert($singleton);
            }

        });

    }
    
    /**
     * @inheritDoc
     */
    public function uninstall(Migrator $migrator): void
    {

    }

};
