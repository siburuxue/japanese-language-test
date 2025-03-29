<?php

namespace App\Command;

use App\Service\PermissionService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'clear:curd',
    description: 'clear curd\'s routes in the db when create:curd is fail',
)]
class ClearCurdCommand extends Command
{

    public function __construct(private PermissionService $permissionService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('prefix', null, InputOption::VALUE_OPTIONAL, '功能/路由前缀');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $prefix = (string)$input->getOption('prefix');
        if ($prefix == '') {
            $io->error("prefix can not be empty");
            exit;
        }
        $routes = ['index', 'list', 'add', 'insert', 'edit', 'update', 'delete', 'info', 'export', 'import'];
        $routesArray = array_map(function ($v) use ($prefix) {
            return $prefix . '-' . $v;
        }, $routes);
        $this->permissionService->delete(['route' => $routesArray]);
        $io->success('Done!');
        return Command::SUCCESS;
    }
}
