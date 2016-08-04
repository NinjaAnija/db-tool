<?php
namespace NinjaAnija\DbTool;

use Doctrine\DBAL\Platforms;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class GetDiffCommand extends GetSchemaCommand
{
    protected function configure()
    {
        $this
            ->setName('getDiff')
            ->addOption('srcSchema', 's', InputOption::VALUE_REQUIRED, 'src schema file path', null)
            ->addOption('destSchema', 'd', InputOption::VALUE_REQUIRED, 'dest schema file path', null)
        ;
    }

    private function _getSchema($helper, $input, $output, $type) {
        $current = "${type}Schema";
        $file = CommandHelper::adjustPath(
            CommandHelper::pickParam($input, $output, $helper, $current, true)
        );
        while (strlen($file) == 0 || !file_exists($file)) {
            $file = CommandHelper::adjustPath(
                CommandHelper::pickParamWithoutArgs($input, $output, $helper, "${type}Schema", true)
            );
        }
        $platform = explode('_', basename($file))[0];
        return [$platform, unserialize(file_get_contents($file)), basename($file)];
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        list($srcPlatform, $srcSchema, $srcBaseName) = $this->_getSchema($helper, $input, $output, 'src');
        list($destPlatform, $destSchema, $destBaseName) = $this->_getSchema($helper, $input, $output, 'dest');

        if ($srcPlatform !== $destPlatform) {
            $output->writeln(
                'FAILED cannot get different platform diff "' .
                $srcFlatform . '/' . $destPlatform .
                '"');
            exit(1);
        }

        $platformClazz = 'Doctrine' . '\\' . 'DBAL' . '\\' . 'Platforms' . '\\' . $destPlatform;
        try {
            $outputFile =
                CommandHelper::getOutputDir($input, $output, $helper) .
                "Diff_${srcBaseName}_to_${destBaseName}.sql";

            $sqls = $srcSchema->getMigrateToSql(
                $destSchema,
                new $platformClazz);

            CommandHelper::confirmFileOverride($input, $output, $helper, $outputFile);

            file_put_contents($outputFile, implode("\n", $sqls));

        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
            exit(1);
        }

        $output->writeln('complete!');
        exit(0);
    }
}
