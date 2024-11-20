<?php

namespace aeroWorker\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RestartWorkerCommand extends Command
{
    protected static $defaultName = 'worker:restart';

    protected function configure()
    {
        $this->setDescription('Restarts the aeroworker service.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Restarting aeroworker service...');
        $result = shell_exec('supervisorctl restart aeroworker:*');

        if ($result === null) {
            $output->writeln('Failed to restart the service.');
            return Command::FAILURE;
        }

        // Optionally, check the output for errors
        if (strpos($result, 'error') !== false) {
            $output->writeln('Error occurred: ' . $result);
            return Command::FAILURE; // Return failure if an error is found in the output
        }

        // If everything is successful
        $output->writeln('Service restarted successfully.');
        return Command::SUCCESS; // Return success
    }
}
