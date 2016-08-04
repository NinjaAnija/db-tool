<?php
namespace NinjaAnija\DbTool;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class CommandHelper
{
    public static function pickParamWithoutArgs(
        InputInterface $input,
        OutputInterface $output,
        QuestionHelper $helper,
        $option,
        $required,
        $default = '',
        $hidden = false
    ) {
        $questionString = $option;
        if (strlen($default) > 0) {
            $questionString .= "(${default})";
        }
        $questionString .= "\n> ";
        $question = new Question($questionString, $default);
        if ($hidden) {
            $question->setHidden(true);
            $question->setHiddenFallback(true);
        }
        $val = $helper->ask($input, $output, $question);
        if ($required && strlen($val) === 0) {
            return self::pickParam($input, $output, $helper, $option, $required, $default, $hidden);
        }
        return $val;
    }

    public static function pickParam(
        InputInterface $input,
        OutputInterface $output,
        QuestionHelper $helper,
        $option,
        $required,
        $default = '',
        $hidden = false
    ) {
        $val = null;
        if ($input->hasOption($option)) {
            $val = $input->getOption($option);
        }
        if (strlen($val) > 0) {
            return $val;
        }
        $questionString = $option;
        if (strlen($default) > 0) {
            $questionString .= "(${default})";
        }
        $questionString .= "\n> ";
        $question = new Question($questionString, $default);
        if ($hidden) {
            $question->setHidden(true);
            $question->setHiddenFallback(true);
        }
        $val = $helper->ask($input, $output, $question);
        if ($required && strlen($val) === 0) {
            return self::pickParamWithoutArgs($input, $output, $helper, $option, $required, $default, $hidden);
        }
        return $val;
    }

    public static function adjustPath($path, $isDir = false)
    {
        if (strpos($path, '/') !== 0) {
            $path = getcwd() . '/' . $path;
        }
        if ($isDir) {
            if (strrpos($path, '/') !== strlen($path) - strlen('/')) {
                $path .= '/';
            }
        }
        return $path;
    }

    public static function getOutputDir(
        InputInterface $input,
        OutputInterface $output,
        QuestionHelper $helper
    ) {
        $defaultOutputDir = realpath(__DIR__ . '/../data/');
        $question = new Question("output dir?(${defaultOutputDir})\n> ");
        $outputDir = $helper->ask($input, $output, $question);

        if (strlen($outputDir) == 0) {
            $outputDir = $defaultOutputDir;
        }

        $outputDir = self::adjustPath($outputDir, true);

        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755);
        }

        if (!is_dir($outputDir)) {
            $output->writeln('FAILED cannot write to "' . $outputDir . '"');
            exit(2);
        }

        return $outputDir;
    }

    public static function confirmFileOverride(
        InputInterface $input,
        OutputInterface $output,
        QuestionHelper $helper,
        $outputFile
    ) {
        if (file_exists($outputFile)) {
            $question = new ConfirmationQuestion("${outputFile} is always exists, overwrite?(y|n)\n> ", false);
            if (!$helper->ask($input, $output, $question)) {
                $output->writeln('exit!');
                exit(0);
            }
        }
    }

}
