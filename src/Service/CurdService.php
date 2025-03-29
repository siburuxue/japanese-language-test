<?php

namespace App\Service;

use App\Lib\Constant\Element;
use App\Lib\Constant\Menu;
use App\Lib\Constant\Permission;
use App\Lib\Tool\StringTool;
use App\Service\Curd\ControllerTrait;
use App\Service\Curd\ElementTrait;
use App\Service\Curd\RepositoryTrait;
use App\Service\Curd\ServiceTrait;
use App\Service\Curd\TemplateTrait;
use Doctrine\Persistence\ManagerRegistry;

class CurdService
{
    private string $root;

    /** @var string 标题 */
    private string $title;

    /** @var string service File Name */
    private string $service;

    /** @var string controller File Name */
    private string $controller;

    /** @var string 表名 */
    private string $table;

    private string $humpTable;
    private string $upperTable;

    /** @var string 扩展属性文件名 */
    private string $extraFile;

    private bool $readonly;

    private bool $export;

    private bool $import;

    private array $tableInfo;

    private string $indexRouteConst = "";
    private string $listRouteConst = "";
    private string $addRouteConst = "";
    private string $insertRouteConst = "";
    private string $editRouteConst = "";
    private string $updateRouteConst = "";
    private string $deleteRouteConst = "";
    private string $infoRouteConst = "";

    private string $exportRouteConst = "";

    private string $importRouteConst = "";

    /*** @var array|string|string[] */
    private string|array $routePathPrefix;

    /**
     * @var int[]|string[]
     */
    private array $varcharColumnList = [];

    private array $humpColumnNameMap = [];

    private mixed $json;

    private array $unElementColumn = ['id', 'is_del', 'create_time', 'create_user', 'update_time', 'update_user'];

    private string $templateDir = "";

    /** @var array 数据库中的列 */
    private array $tableColumn = [];

    private array $elementColumn;

    private array $tableColumnMap;

    private array $dictSource = [];

    private array $checkboxColumn = [];

    private array $conditionColumn;

    private array $selectMultiColumn = [];

    /** @var string 菜单中父分组名字 */
    private string $groupName;

    /** @var string 菜单中父分组图标 */
    private string $iconName;

    use ControllerTrait;

    use TemplateTrait;

    use ServiceTrait;

    use RepositoryTrait;

    use ElementTrait;

    public function __construct(
        private ManagerRegistry $doctrine,
        private PermissionService $permissionService,
    ) {
        $this->root = dirname(__DIR__, 2);
    }

    /**
     * 创建CURD
     * @param string $title 功能名字
     * @param string $table 指定表名
     * @param string $service 指定服务名
     * @param string $controller 指定控制器名
     * @param string $extraFile 其他属性文件名
     * @param string $groupName 菜单中父节点名字
     * @param string $iconName 菜单中父节点图标
     * @param bool $readonly 只生成查询
     * @param bool $export 添加导出功能
     * @param bool $import 添加导入功能
     * @return string
     */
    public function create(
        string $title,
        string $table,
        string $service,
        string $controller,
        string $extraFile,
        string $groupName,
        string $iconName,
        bool $readonly = false,
        bool $export = false,
        bool $import = false,
    ): string {
        $this->title = $title;
        $this->table = $table;
        $this->service = $service;
        $this->controller = $controller;
        $this->extraFile = $extraFile;
        $this->groupName = $groupName;
        $this->iconName = $iconName;
        $this->readonly = $readonly;
        $this->export = $export;
        $this->import = $import;

        $msg = $this->configure();
        if (!empty($msg)) {
            return $msg;
        }
        $this->addRoute();
        $this->writeController();
        $this->writeTemplate();
        $this->writeService();
        $this->writeRepository();
        if ($this->import) {
            $this->writeImportTemplate();
        }
        return '';
    }

    /**
     * 将变量变为驼峰写法
     * @param string $variable
     * @return string
     */
    public function humpVariable(string $variable): string
    {
        if (!str_contains($variable, "_")) {
            return $variable;
        }
        $arr = explode('_', $variable);
        $first = array_shift($arr);
        $arr = array_map(function ($v) {
            return ucfirst($v);
        }, $arr);
        return $first . implode('', $arr);
    }

    private function configure(): string
    {
        $this->humpTable = $this->humpVariable($this->table);
        $this->upperTable = ucfirst($this->humpTable);
        $exist = $this->tableExists();
        if (!$exist) {
            return "{$this->table} 不存在";
        }
        $this->tableInfo = $this->getTableStruct();
        // 数据库中的列
        $this->tableColumn = array_column($this->tableInfo, 'Field');
        $this->tableColumnMap = array_column($this->tableInfo, null, 'Field');
        foreach ($this->tableColumn as $item) {
            $this->humpColumnNameMap[$item] = $this->humpVariable($item);
        }
        // 出现在html中的列
        $this->elementColumn = array_diff($this->tableColumn, $this->unElementColumn);

        $json = file_get_contents($this->root . "/src/Command/extra/" . $this->extraFile);
        $this->json = json_decode($json, true);
        // 出现在index页面查询条件中的列
        $this->conditionColumn = array_keys($this->json['table']);
        // type属性中来源为dict的列
        // extra.json中type属性的字段来源是字典的数据
        foreach ($this->json['type'] as $key => $item) {
            if (in_array($key, $this->tableColumn) && is_array($item) && $item['source'] == 'dict') {
                $this->dictSource[$key] = $item;
            }
        }
        // extra.json中table的列
        $column = array_keys($this->json['table']);
        $diff = array_diff($column, $this->tableColumn);
        // extra.json中type的列
        $typeColumn = array_keys($this->json['type']);
        $typeDiff = array_diff($typeColumn, $this->tableColumn);
        // extra.json中column的列
        $col = array_keys($this->json['columnText']);
        $colDiff = array_diff($col, $this->tableColumn);
        // 查找在文件中存在但是数据库中不存在的列
        $diff = array_merge($diff, $typeDiff, $colDiff);
        if (!empty($diff)) {
            return "列 " . implode(',', $diff) . "不存在";
        }
        // checkbox列
        if (isset($this->json['type'])) {
            foreach ($this->json['type'] as $key => $item) {
                if ($item['element'] == Element::CHECKBOX) {
                    $this->checkboxColumn[] = $key;
                }
            }
        }
        // select multiple列
        if (isset($this->json['type'])) {
            foreach ($this->json['type'] as $key => $item) {
                if ($item['element'] == Element::SELECT && !empty($item['option']) && $item['option'] == 'multiple') {
                    $this->selectMultiColumn[] = $key;
                }
            }
        }
        /******* 查询条件 **********/
        // 选取varchar类型字段
        foreach ($this->tableInfo as $v) {
            if (in_array($v['Field'], $column)) {
                if (str_contains($v['Type'], 'varchar')) {
                    $this->varcharColumnList[] = $v['Field'];
                }
            }
        }
        /******* 查询条件 **********/
        return "";
    }

    private function tableExists(): bool
    {
        $sql = "show tables like '{$this->table}'";
        $connection = $this->doctrine->getConnection();
        $result = $connection->fetchAllNumeric($sql);
        return count($result) > 0;
    }

    private function getTableStruct(): array
    {
        $sql = "desc {$this->table}";
        $connection = $this->doctrine->getConnection();
        return $connection->fetchAllAssociative($sql);
    }

    private function addRoute(): void
    {
        $routeNamePrefix = str_replace("_", "-", $this->table);
        $this->routePathPrefix = str_replace("_", "/", $this->table);
        $routeConstPrefix = strtoupper($this->table);
        $route = "# {$this->title}" . PHP_EOL;
        $addRoute = $insertRoute = $editRoute = $updateRoute = $deleteRoute = $infoRoute = $exportRoute = $importRoute = "";
        // 列表页加载
        $indexRoute = $routeNamePrefix . "-index";
        $this->indexRouteConst = $routeConstPrefix . '_INDEX';
        $route .= $indexRoute . ":" . PHP_EOL;
        $route .= "  path: /{$this->routePathPrefix}/index" . PHP_EOL;
        $route .= "  controller: App\\Controller\\{$this->controller}Controller::index" . PHP_EOL;
        // 列表页 查询table
        $listRoute = $routeNamePrefix . "-list";
        $this->listRouteConst = $routeConstPrefix . '_LIST';
        $route .= $listRoute . ":" . PHP_EOL;
        $route .= "  path: /{$this->routePathPrefix}/list" . PHP_EOL;
        $route .= "  controller: App\\Controller\\{$this->controller}Controller::list" . PHP_EOL;
        if (!$this->readonly) {
            // 新增页加载
            $addRoute = $routeNamePrefix . "-add";
            $this->addRouteConst = $routeConstPrefix . '_ADD';
            $route .= $addRoute . ":" . PHP_EOL;
            $route .= "  path: /{$this->routePathPrefix}/add" . PHP_EOL;
            $route .= "  controller: App\\Controller\\{$this->controller}Controller::add" . PHP_EOL;
            // 新增页提交
            $insertRoute = $routeNamePrefix . "-insert";
            $this->insertRouteConst = $routeConstPrefix . '_INSERT';
            $route .= $insertRoute . ":" . PHP_EOL;
            $route .= "  path: /{$this->routePathPrefix}/insert" . PHP_EOL;
            $route .= "  controller: App\\Controller\\{$this->controller}Controller::insert" . PHP_EOL;
            // 更新页加载
            $editRoute = $routeNamePrefix . "-edit";
            $this->editRouteConst = $routeConstPrefix . '_EDIT';
            $route .= $editRoute . ":" . PHP_EOL;
            $route .= "  path: /{$this->routePathPrefix}/edit" . PHP_EOL;
            $route .= "  controller: App\\Controller\\{$this->controller}Controller::edit" . PHP_EOL;
            // 更新页提交
            $updateRoute = $routeNamePrefix . "-update";
            $this->updateRouteConst = $routeConstPrefix . '_UPDATE';
            $route .= $updateRoute . ":" . PHP_EOL;
            $route .= "  path: /{$this->routePathPrefix}/update" . PHP_EOL;
            $route .= "  controller: App\\Controller\\{$this->controller}Controller::update" . PHP_EOL;
            // 删除
            $deleteRoute = $routeNamePrefix . "-delete";
            $this->deleteRouteConst = $routeConstPrefix . '_DELETE';
            $route .= $deleteRoute . ":" . PHP_EOL;
            $route .= "  path: /{$this->routePathPrefix}/delete" . PHP_EOL;
            $route .= "  controller: App\\Controller\\{$this->controller}Controller::delete" . PHP_EOL;
            // 查看
            $infoRoute = $routeNamePrefix . "-info";
            $this->infoRouteConst = $routeConstPrefix . '_INFO';
            $route .= $infoRoute . ":" . PHP_EOL;
            $route .= "  path: /{$this->routePathPrefix}/info" . PHP_EOL;
            $route .= "  controller: App\\Controller\\{$this->controller}Controller::info" . PHP_EOL;
        }
        if ($this->export) {
            // 导出
            $exportRoute = $routeNamePrefix . "-export";
            $this->exportRouteConst = $routeConstPrefix . '_EXPORT';
            $route .= $exportRoute . ":" . PHP_EOL;
            $route .= "  path: /{$this->routePathPrefix}/export" . PHP_EOL;
            $route .= "  controller: App\\Controller\\{$this->controller}Controller::export" . PHP_EOL;
        }

        if ($this->import) {
            // 导入
            $importRoute = $routeNamePrefix . "-import";
            $this->importRouteConst = $routeConstPrefix . '_IMPORT';
            $route .= $importRoute . ":" . PHP_EOL;
            $route .= "  path: /{$this->routePathPrefix}/import" . PHP_EOL;
            $route .= "  controller: App\\Controller\\{$this->controller}Controller::import" . PHP_EOL;
        }
        $route .= "# {$this->title}" . PHP_EOL;

        file_put_contents($this->root . "/config/routes/admin/routes.yaml", $route, FILE_APPEND);
        $constant = "    const {$this->indexRouteConst} = \"{$indexRoute}\";" . PHP_EOL . PHP_EOL;
        $constant .= "    const {$this->listRouteConst} = \"{$listRoute}\";" . PHP_EOL . PHP_EOL;
        if (!$this->readonly) {
            $constant .= "    const {$this->addRouteConst} = \"{$addRoute}\";" . PHP_EOL . PHP_EOL;
            $constant .= "    const {$this->insertRouteConst} = \"{$insertRoute}\";" . PHP_EOL . PHP_EOL;
            $constant .= "    const {$this->editRouteConst} = \"{$editRoute}\";" . PHP_EOL . PHP_EOL;
            $constant .= "    const {$this->updateRouteConst} = \"{$updateRoute}\";" . PHP_EOL . PHP_EOL;
            $constant .= "    const {$this->deleteRouteConst} = \"{$deleteRoute}\";" . PHP_EOL . PHP_EOL;
            $constant .= "    const {$this->infoRouteConst} = \"{$infoRoute}\";" . PHP_EOL . PHP_EOL;
        }
        if ($this->export) {
            $constant .= "    const {$this->exportRouteConst} = \"{$exportRoute}\";" . PHP_EOL . PHP_EOL;
        }
        if ($this->import) {
            $constant .= "    const {$this->importRouteConst} = \"{$importRoute}\";" . PHP_EOL . PHP_EOL;
        }
        $routeConstantFile = $this->root . "/src/Lib/Constant/Route.php";
        $string = file_get_contents($routeConstantFile);
        $string = rtrim($string);
        $string = rtrim($string, "}");
        $string .= PHP_EOL . $constant . "}" . PHP_EOL;
        file_put_contents($routeConstantFile, $string);

        $indexId = $this->permissionService->insert([
            'route' => $indexRoute,
            'routeName' => $this->title,
            'parentId' => Menu::ROOT,
            'groupName' => $this->groupName,
            'iconName' => $this->iconName,
            'isMenu' => Permission::MENU_PERMISSION,
            'isDefault' => Permission::UN_DEFAULT_PERMISSION,
            'userId' => 1
        ]);
        $this->permissionService->insert([
            'route' => $listRoute,
            'routeName' => $this->title . "列表页查询",
            'parentId' => $indexId,
            'groupName' => "",
            'iconName' => "",
            'isMenu' => Permission::UN_MENU_PERMISSION,
            'isDefault' => Permission::UN_DEFAULT_PERMISSION,
            'userId' => 1
        ]);
        if (!$this->readonly) {
            $addId = $this->permissionService->insert([
                'route' => $addRoute,
                'routeName' => $this->title . "添加页加载",
                'parentId' => $indexId,
                'groupName' => "",
                'iconName' => "",
                'isMenu' => Permission::UN_MENU_PERMISSION,
                'isDefault' => Permission::UN_DEFAULT_PERMISSION,
                'userId' => 1,
            ]);
            $this->permissionService->insert([
                'route' => $insertRoute,
                'routeName' => $this->title . "添加页提交",
                'parentId' => $addId,
                'groupName' => "",
                'iconName' => "",
                'isMenu' => Permission::UN_MENU_PERMISSION,
                'isDefault' => Permission::UN_DEFAULT_PERMISSION,
                'userId' => 1,
            ]);
            $editId = $this->permissionService->insert([
                'route' => $editRoute,
                'routeName' => $this->title . "更新页加载",
                'parentId' => $indexId,
                'groupName' => "",
                'iconName' => "",
                'isMenu' => Permission::UN_MENU_PERMISSION,
                'isDefault' => Permission::UN_DEFAULT_PERMISSION,
                'userId' => 1,
            ]);
            $this->permissionService->insert([
                'route' => $updateRoute,
                'routeName' => $this->title . "更新页提交",
                'parentId' => $editId,
                'groupName' => "",
                'iconName' => "",
                'isMenu' => Permission::UN_MENU_PERMISSION,
                'isDefault' => Permission::UN_DEFAULT_PERMISSION,
                'userId' => 1,
            ]);
            $this->permissionService->insert([
                'route' => $deleteRoute,
                'routeName' => $this->title . "删除",
                'parentId' => $indexId,
                'groupName' => "",
                'iconName' => "",
                'isMenu' => Permission::UN_MENU_PERMISSION,
                'isDefault' => Permission::UN_DEFAULT_PERMISSION,
                'userId' => 1,
            ]);
            $this->permissionService->insert([
                'route' => $infoRoute,
                'routeName' => $this->title . "查看详情",
                'parentId' => $indexId,
                'groupName' => "",
                'iconName' => "",
                'isMenu' => Permission::UN_MENU_PERMISSION,
                'isDefault' => Permission::UN_DEFAULT_PERMISSION,
                'userId' => 1,
            ]);
        }
        if ($this->export) {
            $this->permissionService->insert([
                'route' => $exportRoute,
                'routeName' => $this->title . "导出",
                'parentId' => $indexId,
                'groupName' => "",
                'iconName' => "",
                'isMenu' => Permission::UN_MENU_PERMISSION,
                'isDefault' => Permission::UN_DEFAULT_PERMISSION,
                'userId' => 1
            ]);
        }
        if ($this->import) {
            $this->permissionService->insert([
                'route' => $importRoute,
                'routeName' => $this->title . "导入",
                'parentId' => $indexId,
                'groupName' => "",
                'iconName' => "",
                'isMenu' => Permission::UN_MENU_PERMISSION,
                'isDefault' => Permission::UN_DEFAULT_PERMISSION,
                'userId' => 1
            ]);
        }
    }

    private function writeController(): void
    {
        $function = $this->indexFunction();
        $function .= $this->listFunction();
        if (!$this->readonly) {
            $function .= $this->addFunction();
            $function .= $this->insertFunction();
            $function .= $this->editFunction();
            $function .= $this->updateFunction();
            $function .= $this->deleteFunction();
            $function .= $this->infoFunction();
        }
        if ($this->export) {
            $function .= $this->exportFunction();
        }
        if ($this->import) {
            $function .= $this->importFunction();
        }
        $controllerPath = $this->root . "/src/Controller/" . $this->controller . "Controller.php";
        $controllerExists = file_exists($controllerPath);
        if ($controllerExists) {
            $controllerText = file_get_contents($controllerPath);
            $controllerText = rtrim($controllerText);
            $controllerText = rtrim($controllerText, "}");
            $controllerText .= $function;
            file_put_contents($controllerPath, $controllerText . PHP_EOL . "}" . PHP_EOL);
        } else {
            $controller = <<<EOF
<?php
namespace App\\Controller;

use Symfony\\Component\\HttpFoundation\\Request;
use Symfony\\Component\\HttpFoundation\\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\\Component\\Pager\\PaginatorInterface;
use App\\Service\\{$this->service}Service;
use App\\Service\\DictService;
use App\\Lib\\Tool\\PageTool;
use App\\Lib\\Constant\\Tool;

class {$this->controller}Controller extends CommonController{
{$function}
}
EOF;
            file_put_contents($controllerPath, $controller);
        }
    }

    private function writeTemplate(): void
    {
        $this->templateDir = $this->root . "/templates/admin/" . $this->routePathPrefix;
        if (!file_exists($this->templateDir)) {
            mkdir($this->templateDir, 0755, true);
        }
        $this->writeIndexTwig();
        $this->writeListTwig();
        if (!$this->readonly) {
            $this->writeAddTwig();
            $this->writeInfoTwig();
            $this->writeEditTwig();
        }
    }

    private function writeService(): void
    {
        $function = $this->writeServiceList();
        if (!$this->readonly) {
            $function .= $this->writeServiceInsert();
            $function .= $this->writeServiceUpdate();
            $function .= $this->writeServiceInfo();
            $function .= $this->writeServiceDelete();
        }
        if ($this->export) {
            $function .= $this->writeServiceExport();
        }
        if ($this->import) {
            $function .= $this->writeServiceImport();
        }
        $servicePath = $this->root . "/src/Service/" . $this->service . "Service.php";
        $serviceExists = file_exists($servicePath);
        if ($serviceExists) {
            $serviceText = file_get_contents($servicePath);
            $serviceText = rtrim($serviceText);
            $serviceText = rtrim($serviceText, "}");
            $serviceText .= $function;
            file_put_contents($servicePath, $serviceText . PHP_EOL . "}" . PHP_EOL);
        } else {
            $service = <<<EOF
<?php

namespace App\\Service;

use App\\Entity\\{$this->upperTable};
use Doctrine\\Persistence\\ManagerRegistry;
use Doctrine\\Persistence\\ObjectRepository;
use Symfony\\Component\\HttpFoundation\\RequestStack;

class {$this->service}Service extends CommonService
{
    private ObjectRepository \${$this->humpTable}Repository;
    
    public function __construct(
        private ManagerRegistry \$doctrine, 
        private RequestStack \$requestStack, 
        private DictService \$dictService
    ){
        parent::__construct(\$requestStack);
        \$this->{$this->humpTable}Repository = \$doctrine->getRepository({$this->upperTable}::class);
    }
    {$function}
}
EOF;
            file_put_contents($servicePath, $service);
        }
    }

    private function writeRepository(): void
    {
        $function = $this->writeRepositoryList();
        if (!$this->readonly) {
            $function .= $this->writeRepositoryInsert();
            $function .= $this->writeRepositoryUpdate();
        }
        if ($this->import) {
            $function .= $this->writeResourceImport();
        }
        $repositoryPath = $this->root . "/src/Repository/" . $this->upperTable . "Repository.php";
        $repositoryText = file_get_contents($repositoryPath);
        $repositoryText = rtrim($repositoryText);
        $repositoryText = rtrim($repositoryText, "}");
        $repositoryText .= PHP_EOL . "    use Common\\CommonRepositoryTrait;" . PHP_EOL;
        $repositoryText .= $function;
        file_put_contents($repositoryPath, $repositoryText . PHP_EOL . "}" . PHP_EOL);
    }

    private function writeImportTemplate(): string
    {
        $dir = "/public/templates";
        if (!file_exists("." . $dir)) {
            mkdir("." . $dir, 0755);
        }
        $fileName = $this->table . ".xlsx";
        $header = array_values($this->json['columnText']);
        $values = [];
        foreach ($this->elementColumn as $item) {
            $type = $this->tableColumnMap[$item]['Type'];
            if(isset($this->json['type'][$item])){
                switch ($this->json['type'][$item]['element']){
                    case Element::RADIO:
                        $values[] = 1;
                        break;
                    case Element::CHECKBOX:
                        $values[] = "[1,2,3]";
                        break;
                    case Element::SELECT:
                        if(isset($this->json['type'][$item]['option']) && $this->json['type'][$item]['option'] == 'mutiple'){
                            $values[] = "[1,2,3]";
                        }else{
                            $values[] = 1;
                        }
                        break;
                    case Element::DATETIME:
                        $values[] = date('Y-m-d H:i:s');
                        break;
                    case Element::DATE:
                        $values[] = date('Y-m-d');
                        break;
                }
            }else{
                if($type == Element::INT){
                    $values[] = 1;
                }else{
                    $values[] = StringTool::random(5);
                }
            }
        }
        $config = ['path' => '.'.$dir];
        $excel  = new \Vtiful\Kernel\Excel($config);
        return $excel->fileName($fileName, 'sheet1')
            ->header($header)
            ->data([$values])
            ->output();
    }
}