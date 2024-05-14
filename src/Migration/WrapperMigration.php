<?php
namespace LeadingSystems\HelpersBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class WrapperMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }


    public function getName(): string
    {
        return "Wrapper Migration";
    }

    public function shouldRun(): bool
    {

        //Testausgabe
        $myfile = fopen("newfile.txt", "w") or die("Unable to open file!");
        $txt = "shouldrun\n";
        fwrite($myfile, $txt);
        fclose($myfile);


        $stmt = $this->connection->prepare("
            SELECT *
            FROM tl_content
            WHERE type = 'htmlWrapperStart'
        ");

        $test = $stmt->execute();

        return ($test->rowCount() > 0);
    }

    //currently not running
    public function run(): MigrationResult
    {

        //Testausgabe
        $myfile = fopen("newfile2.txt", "w") or die("Unable to open file!");
        $txt = "run hier\n";
        fwrite($myfile, $txt);
        fclose($myfile);
        

        $databaseStatement = $this->connection->prepare("
                UPDATE
                    tl_content
                SET
                    type = 'rsce_htmlwrapper-start'
                WHERE 
                    type = 'htmlWrapperStart'
        ");
        $result = $databaseStatement->execute();

        return new MigrationResult(true, "test");
    }
}