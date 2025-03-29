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
use Symfony\Component\Yaml\Yaml;

#[AsCommand(
    name: 'sync:route',
    description: 'Synchronous routing is kept with the data in the database',
)]
class SyncRouteCommand extends Command
{


    public function __construct(
        private PermissionService $permissionService,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $root = dirname(__DIR__, 2);
        $yaml = file_get_contents($root . "/config/routes/admin/routes.yaml");
        $routeJson = Yaml::parse($yaml);
        $routeName = [];
        foreach ($routeJson as $route => $item) {
            $routeName[] = [
                'route' => $route,
                'routeName' => '',
                'parentId' => 0
            ];
        }
        $databaseRoute = $this->permissionService->getRouteArray();
        $diff = array_diff(array_column($routeName, 'route'), $databaseRoute);
        if (!empty($diff)) {
            $io->error("These routes does not exist(配置文件中存在但是数据库中不存在): " . implode(" ", $diff));
            return Command::FAILURE;
        }
        $io->success('sync success.');

        return Command::SUCCESS;
    }
}
