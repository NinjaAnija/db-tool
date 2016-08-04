<?php
namespace NinjaAnija\DbTool;

use Doctrine\DBAL\DriverManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class GetSchemaCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('getSchema')
            ->addOption('driver', null, InputOption::VALUE_REQUIRED, 'database driver', null)
            ->addOption('host', null, InputOption::VALUE_REQUIRED, 'database host', null)
            ->addOption('user', null, InputOption::VALUE_REQUIRED, 'database user', null)
            ->addOption('dbname', null, InputOption::VALUE_REQUIRED, 'dbname', null)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $driver = CommandHelper::pickParam($input, $output, $helper, 'driver', true, 'pdo_mysql');
        $host   = CommandHelper::pickParam($input, $output, $helper, 'host',   true, 'localhost');
        $user   = CommandHelper::pickParam($input, $output, $helper, 'user',   true, 'root');
        $passwd = CommandHelper::pickParam($input, $output, $helper, 'passwd', false, '', true);
        $dbname = CommandHelper::pickParam($input, $output, $helper, 'dbname', true, '');

        $schema = null;
        try {
            $conn = DriverManager::getConnection([
                'driver' => $driver,
                'host' => $host,
                'user' => $user,
                'password' => $passwd,
                'dbname' => $dbname,
            ]);

            $schema = $conn->getSchemaManager()->createSchema();

        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
            exit(1);
        }

        $output->writeln('db-tool:getSchema succeed!');

        $outputFile =
            CommandHelper::getOutputDir($input, $output, $helper) .
            implode(array_slice(explode('\\', get_class($conn->getDatabasePlatform())), -1)) .
            '_' . $host . '_' . $dbname . '_serialized';

        CommandHelper::confirmFileOverride($input, $output, $helper, $outputFile);

        file_put_contents($outputFile, serialize($schema));

        $output->writeln('complete!');
        exit(0);
    }
}
