<?php

namespace ShopmasterZalandoConnectorSix\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ZalandoCommand extends Command implements RunProcessInterface
{
    private static InputInterface $input;
    private static OutputInterface $output;


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        self::$input = $input;
        self::$output = $output;
        $this->runProcess();
        return 0;
    }

    /**
     * @return InputInterface
     */
    public static function getInput(): InputInterface
    {
        return self::$input;
    }

    /**
     * @return OutputInterface
     */
    public static function getOutput(): OutputInterface
    {
        return self::$output;
    }

}