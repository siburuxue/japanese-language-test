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
    name: 'add:route',
    description: 'add a route in the project command:「php bin/console add:route test-menu 测试菜单 /test/menu App\\Controller\\TestController::menu --group="" --icon="" --menu=1 --parent=0 --default=0」',
)]
class AddRouteCommand extends Command
{

    private string $root;

    public function __construct(
        private PermissionService $permissionService
    )
    {
        parent::__construct();
        $this->root = dirname(__DIR__, 2);
    }

    private function createTree(): void
    {
        $filePath = $this->root . '/templates/admin/base/left.html.twig';
        $tree = $this->permissionService->getTreeString();
        file_put_contents($filePath, $tree);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('route', InputArgument::OPTIONAL, 'route name')
            ->addArgument('name', InputArgument::OPTIONAL, 'route text')
            ->addArgument('path', InputArgument::OPTIONAL, 'url path')
            ->addArgument('controller', InputArgument::OPTIONAL, 'Controller Name::Function Name')
            ->addOption('parent', "", InputOption::VALUE_OPTIONAL, 'parent route id')
            ->addOption('menu', "", InputOption::VALUE_OPTIONAL, 'the route is show in the tree menu or not')
            ->addOption('default', "", InputOption::VALUE_OPTIONAL,
                'add the route to the login user when login success')
            ->addOption('group', "", InputOption::VALUE_OPTIONAL,
                'in the menu tree, the parent\'s label name')
            ->addOption('icon', "", InputOption::VALUE_OPTIONAL,
                'in the menu tree, the parent\'s label icon');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $route = $input->getArgument('route');
        $name = $input->getArgument('name');
        $path = $input->getArgument('path');
        $controller = $input->getArgument('controller');
        $parent = (int)$input->getOption('parent');
        $menu = (int)$input->getOption('menu');
        $default = (int)$input->getOption('default');
        $groupName = $input->getOption('group');
        $iconName = $input->getOption('icon');
        $this->permissionService->insert([
            'route' => $route,
            'routeName' => $name,
            'parentId' => $parent,
            'groupName' => $groupName,
            'iconName' => $iconName,
            'isMenu' => $menu,
            'isDefault' => $default,
            'userId' => 1
        ]);
        $constName = strtoupper(str_replace('-', '_', $route));
        $routeConstantFilePath = $this->root . "/src/Lib/Constant/Route.php";
        $routeConstantString = file_get_contents($routeConstantFilePath);
        $newConstRoute = <<<EOF
 
    const {$constName} = "{$route}";
}    
EOF;
        $routeConstantString = str_replace("}", $newConstRoute, $routeConstantString);
        file_put_contents($routeConstantFilePath, $routeConstantString);
        $routeYamlPath = $this->root . "/config/routes/admin/routes.yaml";
        $newRouteYamlString = <<<EOF

{$route}:
  path: {$path}
  controller: {$controller}
EOF;
        $routeYamlString = file_get_contents($routeYamlPath);
        $routeYamlString .= $newRouteYamlString;
        file_put_contents($routeYamlPath, $routeYamlString);
        $io->info("control the button in the page use this if it's exist: {% if hasPermission('{$constName}') %}{% endif %}");
        $controller = explode("::", $controller);
        $className = str_replace("App\\Controller\\", "", $controller[0]);
        if (!class_exists($controller[0])) {
            $class = <<<EOF
<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class {$className} extends CommonController{

    public function {$controller[1]}(Request \$request): Response
    {
        return new Response("");
    }
}
EOF;
            file_put_contents($this->root . "/src/Controller/{$className}.php", $class);
        } else {
//            $io->info('add this function into the ' . $controller[0] . ": public function " . $controller[1] . "(Request \$request){}");
            $controllerContent = file_get_contents($this->root . "/src/Controller/{$className}.php");
            $controllerContent = rtrim($controllerContent);
            $controllerContent = rtrim($controllerContent, "}");
            $functionContent = <<<EOF

    public function {$controller[1]}(Request \$request): Response
    {
        return new Response("");
    }
}
     
EOF;
            $controllerContent .= $functionContent;
            file_put_contents($this->root . "/src/Controller/{$className}.php", $controllerContent);
        }
        if ($menu == '1') {
            $this->createTree();
        }
        return Command::SUCCESS;
    }
}
