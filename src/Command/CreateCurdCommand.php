<?php

namespace App\Command;

use App\Service\CurdService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

// 先执行 ./sync.sh 生成getter/setter
// php bin/console create:curd --title=测试字典 --table=test_dict --service=TestDict --controller=TestDict --extra=test_dict.json --group="" --icon="" --readonly --export
#[AsCommand(
    name: 'create:curd',
    description: 'create curd for database tables. 所指定的文件名最好都是新增文件，尽量不在原有的文件上修改，避免函数名重复',
)]
class CreateCurdCommand extends Command
{
    private string $root;

    public function __construct(private CurdService $curdService)
    {
        parent::__construct();
        $this->root = dirname(__DIR__, 2);
    }

    protected function configure(): void
    {
        $this
            ->addOption('title', null, InputOption::VALUE_OPTIONAL, '功能名字')
            ->addOption('table', null, InputOption::VALUE_OPTIONAL, 'The database table name for curd')
            ->addOption('service', null, InputOption::VALUE_OPTIONAL,
                'The service file name. For example dict => DictService')
            ->addOption('controller', null, InputOption::VALUE_OPTIONAL,
                'The controller file name. For example dict => DictController')
            ->addOption('extra', null, InputOption::VALUE_OPTIONAL,
                'A json file path for the extra info, json格式文件。')
            ->addOption('group', "", InputOption::VALUE_OPTIONAL,
                'in the menu tree, the parent\'s label name')
            ->addOption('icon', "", InputOption::VALUE_OPTIONAL,
                'in the menu tree, the parent\'s label icon')
            ->addOption("readonly", null, InputOption::VALUE_NONE, "只生成查询")
            ->addOption("export", null, InputOption::VALUE_NONE, "添加导出功能")
            ->addOption("import", null, InputOption::VALUE_NONE, "添加导入功能");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $title = $input->getOption('title');
        $table = $input->getOption('table');
        $service = $input->getOption('service');
        $controller = $input->getOption('controller');
        $extraFile = (string)$input->getOption('extra');
        $groupName = $input->getOption('group');
        $iconName = $input->getOption('icon');
        $readonly = $input->getOption('readonly');
        $export = $input->getOption('export');
        $import = $input->getOption('import');
        $io->info("所指定的文件名最好都是空文件或者新增文件，尽量不在原有的文件上修改，避免函数名重复");
        if (file_exists($this->root . "/src/Controller/{$controller}Controller.php")) {
            $io->warning("src/Controller/{$controller}Controller.php 已存在");
        }
        if (file_exists($this->root . "/src/Service/{$service}Service.php")) {
            $io->warning("src/Service/{$service}Service.php 已存在");
        }
        $msg = $this->curdService->create($title, $table, $service, $controller, $extraFile, $groupName, $iconName,
            $readonly,
            $export, $import);
        if (empty($msg)) {
            $file = "新增/更新 文件：" . PHP_EOL;
            $file .= "src/Controller/{$controller}Controller.php" . PHP_EOL;
            $file .= "src/Service/{$service}Service.php" . PHP_EOL;
            $file .= "src/Repository/" . ucfirst($this->curdService->humpVariable($table)) . "Repository.php" . PHP_EOL;
            $file .= "templates/admin/" . str_replace("_", "/", $table) . "/*.html.twig" . PHP_EOL;
            if ($import) {
                $file .= "导入模板：" . PHP_EOL;
                $file .= $this->root . "/public/templates/{$table}.xlsx";
            }
            $io->info($file);
            $io->success("Done!");
            return Command::SUCCESS;
        } else {
            $io->error($msg);
            return Command::FAILURE;
        }
    }
}
