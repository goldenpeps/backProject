<?php

namespace App\Command;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'app:tests:run',
    description: 'Execute la suite de tests unitaires (PHPUnit) et trace le resultat dans var/log/tests.log via Monolog - destinee a etre lancee par un cron.'
)]
class RunTestsCommand extends Command
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
        #[Autowire(service: 'monolog.logger.tests')]
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Execution de la suite de tests unitaires');
        $this->logger->info('=== Debut de la suite de tests unitaires ===');

        $process = new Process([PHP_BINARY, $this->projectDir . '/bin/phpunit', '--colors=never'], $this->projectDir);
        $process->setTimeout(600);
        $process->run();

        $rawOutput = trim($process->getOutput() . $process->getErrorOutput());
        $io->writeln($rawOutput);
        $this->logger->debug('Sortie complete de PHPUnit', ['output' => $rawOutput]);

        $summary = $this->extractSummary($rawOutput);

        if ($process->isSuccessful()) {
            $this->logger->info(sprintf('=== Tests unitaires : SUCCES - %s ===', $summary ?? 'voir la sortie complete'));
            $io->success(trim('Tous les tests sont passes. ' . ($summary ?? '')));

            return Command::SUCCESS;
        }

        $this->logger->error(sprintf('=== Tests unitaires : ECHEC - %s ===', $summary ?? 'voir la sortie complete'), [
            'exitCode' => $process->getExitCode(),
        ]);
        $io->error(trim('Des tests ont echoue. ' . ($summary ?? '') . ' Voir var/log/tests.log pour le detail.'));

        return Command::FAILURE;
    }

    private function extractSummary(string $output): ?string
    {
        if (preg_match('/OK \(\d+ tests?, \d+ assertions?\)/', $output, $matches)) {
            return $matches[0];
        }

        if (preg_match('/Tests:\s*\d+,\s*Assertions:\s*\d+(?:,\s*\w+:\s*\d+)*\.?/', $output, $matches)) {
            return $matches[0];
        }

        return null;
    }
}
