<?php

namespace LeadingSystems\HelpersBundle\Migration;

use Composer\InstalledVersions;
use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class WrapperMigration extends AbstractMigration
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tablesExist(['tl_content'])) {
            return false;
        }

        if (!InstalledVersions::isInstalled('leadingsystems/lsce')) {
            return false;
        }

        if(version_compare(
            InstalledVersions::getPrettyVersion('leadingsystems/lsce'),
            "2.0.0",
            '<'
        )){
            return false;
        }

        $test = $this->connection->fetchNumeric("
            SELECT TRUE
            FROM `tl_content` 
            WHERE type='htmlWrapperStart' OR type='htmlWrapperStop'
        ");

        return false !== $test;
    }

    public function run(): MigrationResult
    {
        $this->connection->update('tl_content', ['type' => 'rsce_htmlwrapper-start'], ['type' => 'htmlWrapperStart']);

        $this->connection->update('tl_content', ['type' => 'rsce_htmlwrapper-stop'], ['type' => 'htmlWrapperStop']);

        return $this->createResult(true);
    }
}