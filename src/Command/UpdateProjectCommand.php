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
    name: 'app:project:update',
    description: 'Met a jour le projet (git pull, composer install, migrations, cache) - destinee a etre lancee par un cron.'
)]
class UpdateProjectCommand extends Command
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
        #[Autowire(service: 'monolog.logger.cron')]
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Mise a jour du projet');
        $this->logger->info('=== Debut de la mise a jour du projet ===');

        $steps = [];

        if (is_dir($this->projectDir . '/.git')) {
            $steps['git pull'] = ['git', 'pull'];
        } else {
            $this->logger->notice('Aucun depot git detecte, etape "git pull" ignoree.');
            $io->note('Pas de depot git detecte, etape "git pull" ignoree.');
        }

        $steps['composer install'] = ['composer', 'install', '--no-interaction', '--optimize-autoloader'];
        $steps['migrations Doctrine'] = [PHP_BINARY, $this->projectDir . '/bin/console', 'doctrine:migrations:migrate', '--no-interaction'];
        $steps['vidage du cache'] = [PHP_BINARY, $this->projectDir . '/bin/console', 'cache:clear'];

        foreach ($steps as $label => $commandLine) {
            $io->section($label);

            $process = new Process($commandLine, $this->projectDir);
            $process->setTimeout(300);
            $process->run();

            if (!$process->isSuccessful()) {
                $this->logger->error(sprintf('Etape "%s" en echec (code %s)', $label, $process->getExitCode()), [
                    'command' => implode(' ', $commandLine),
                    'output' => $process->getOutput(),
                    'error' => $process->getErrorOutput(),
                ]);
                $io->error(sprintf('Etape "%s" a echoue. Voir var/log/cron.log pour le detail.', $label));

                return Command::FAILURE;
            }

            $this->logger->info(sprintf('Etape "%s" reussie', $label), [
                'command' => implode(' ', $commandLine),
                'output' => trim($process->getOutput()),
            ]);
            $io->writeln(trim($process->getOutput()));
        }

        $this->logger->info('=== Mise a jour du projet terminee avec succes ===');
        $io->success('Projet mis a jour avec succes.');

        return Command::SUCCESS;
    }
}
