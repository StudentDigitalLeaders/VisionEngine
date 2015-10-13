<?php

namespace Bolt\Extension\Bolt\ClientLogin\Database;

use Doctrine\DBAL\Schema\Schema as DbalSchema;

/**
 * ClientLogin database schema
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Schema
{
    /** @var \Bolt\Storage\Database\Schema\Manager */
    private $schemaManager;
    /** @var string */
    private $tableName;

    /**
     * Constructor
     *
     * @param mixed $schemaManager
     *
     * Note that $schemaManager will be either:
     * \Bolt\Storage\Database\Schema\Manager
     * \Bolt\Database\IntegrityChecker
     */
    public function __construct($schemaManager, $tableName)
    {
        $this->schemaManager = $schemaManager;
        $this->tableName = $tableName;
    }

    /**
     * Create/update database table.
     */
    public function build()
    {
        $tableName = $this->tableName;

        // User/client account table
        $this->schemaManager->registerExtensionTable(
            function (DbalSchema $schema) use ($tableName) {
                // @codingStandardsIgnoreStart
                $table = $schema->createTable($tableName . '_account');
                $table->addColumn('guid',              'guid',     []);
                $table->addColumn('resource_owner_id', 'string',   ['notnull' => false, 'length' => 128]);
                $table->addColumn('password',          'string',   ['notnull' => false, 'length' => 64]);
                $table->addColumn('email',             'string',   ['notnull' => false, 'length' => 254]);
                $table->addColumn('enabled',           'boolean',  ['default' => false]);

                $table->setPrimaryKey(['guid']);

                $table->addUniqueIndex(['resource_owner_id']);
                $table->addUniqueIndex(['email']);
                $table->addIndex(['enabled']);

                return $table;
                // @codingStandardsIgnoreEnd
            }
        );

        // User/client provider table
        $this->schemaManager->registerExtensionTable(
            function (DbalSchema $schema) use ($tableName) {
                // @codingStandardsIgnoreStart
                $table = $schema->createTable($tableName . '_provider');
                $table->addColumn('guid',              'guid',     []);
                $table->addColumn('provider',          'string',   ['length' => 64]);
                $table->addColumn('resource_owner_id', 'string',   ['length' => 128]);
                $table->addColumn('refresh_token',     'string',   ['notnull' => false, 'default' => null, 'length' => 128]);
                $table->addColumn('resource_owner',    'text',     ['notnull' => false, 'default' => null]);
                $table->addColumn('lastupdate',        'datetime', ['notnull' => false, 'default' => null]);

                $table->setPrimaryKey(['guid']);

                $table->addIndex(['provider']);
                $table->addIndex(['resource_owner_id']);
                $table->addIndex(['refresh_token']);

                return $table;
                // @codingStandardsIgnoreEnd
            }
        );

        // User/client provider table
        $this->schemaManager->registerExtensionTable(
            function (DbalSchema $schema) use ($tableName) {
                // @codingStandardsIgnoreStart
                $table = $schema->createTable($tableName . '_tokens');
                $table->addColumn('access_token',      'string',   ['length' => 128]);
                $table->addColumn('guid',              'guid',     []);
                $table->addColumn('access_token_data', 'text',     ['notnull' => false, 'default' => null]);
                $table->addColumn('expires',           'integer',  ['notnull' => false, 'default' => null]);

                $table->setPrimaryKey(['access_token']);

                $table->addIndex(['guid']);
                $table->addIndex(['expires']);

                return $table;
                // @codingStandardsIgnoreEnd
            }
        );
    }
}
