<?php declare(strict_types=1);

use Crate\Database\Contracts\MigrationContract;
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

        $migrator->create('test', function (SchemaBuilder $schema) {

            $schema->string('string');
            $schema->string('string_specific')->length(20);
            $schema->string('string_minimum')->minLength(5);
            $schema->string('string_maxium')->maxLength(10);
            $schema->string('string_range')->minLength(5)->maxLength(10);
            $schema->string('string_enum')->enum(['value1', 'value2']);
            $schema->string('string_unique')->unique()->required();

            $schema->array('array');
            $schema->array('array_default')->default(['value1', 'value2', 'value3']);
            $schema->array('array_specific')->length(3);
            $schema->array('array_minimum')->minLength(1);
            $schema->array('array_maxium')->maxLength(3);
            $schema->array('array_range')->minLength(1)->maxLength(3);
            $schema->array('array_enum')->enum(['value1', 'value2', 'value3']);

            $schema->object('object');
            $schema->object('object_default')->default(['key' => 'value']);
            $schema->number('number');
            $schema->number('number_default')->default(20.2);
            $schema->integer('integer');
            $schema->integer('integer_default')->default(50);
            $schema->boolean('boolean');
            $schema->boolean('boolean_default_true')->default(true);
            $schema->boolean('boolean_default_false')->default(false);

            $schema->uid('special_uid');
            $schema->timestamp('special_timestamp');
            $schema->time('special_time');
            $schema->date('special_date');
            $schema->datetime('special_datetime');
            $schema->email('special_email');
            $schema->ipv4('special_ipv4');
            $schema->ipv6('special_ipv6');
            $schema->uuid('special_uuid');

        });

        // Ensures that the above command is executed before the next statement is called
        $migrator->commit();
        
        $migrator->update('test', function (SchemaEditor $schema) {

            // Add Properties
            $schema->email('custom_email', true);

            // Rename Property
            $schema->rename('special_uuid', 'custom_uuid');

            // Replace Property
            $schema->replace('special_email', 'custom_email');

            // Remove Property
            $schema->remove('boolean_default_true');
            $schema->remove('boolean_default_false');

        });

        // Ensures that the above command is executed before the next statement is called
        $migrator->commit();

        //$migrator->select('test', function (Repository $repo) {
//
        //});

    }
    
    /**
     * @inheritDoc
     */
    public function uninstall(Migrator $migrator): void
    {

    }

};
