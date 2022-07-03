<?php declare(strict_types=1);

use Crate\Database\Contracts\MigrationContract;
use Crate\Database\Migrations\Migrator;
use Crate\Database\Migrations\SchemaBuilder;
use Crate\Database\Migrations\SchemaEditor;

return new class implements MigrationContract {
    
    /**
     * @inheritDoc
     */
    public function title(): string
    {
        return 'Install Migration Schema';
    }

    /**
     * @inheritDoc
     */
    public function install(Migrator $migrator): void
    {

        $migrator->create('migrations', function (SchemaBuilder $schema) {

            $schema->driver = 'crate';
            $schema->storage = false;
            $schema->primaryKey = 'id';
            $schema->primaryKeyFormat = 'id';
            $schema->created = 'migrated_at';
            $schema->updated = null;

            $schema->string('module')->required();
            $schema->string('migration')->required();
       
        });

    }
    
    /**
     * @inheritDoc
     */
    public function uninstall(Migrator $migrator): void
    {

        $migrator->delete('migrations');

    }

};
