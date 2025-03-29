<?php

namespace App\Service;

use App\Lib\Constant\Menu;
use App\Lib\Tool\TreeTool;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use App\Entity\Permission;
use App\Lib\Constant\Permission as PermissionConstant;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PermissionService extends CommonService
{
    private ObjectRepository $permissionRepository;

    public function __construct(
        private ManagerRegistry $doctrine,
        private TreeTool        $tree,
        private RequestStack    $requestStack,
    )
    {
        parent::__construct($requestStack);
        $this->permissionRepository = $doctrine->getRepository(Permission::class);
    }

    public function insert(array $route)
    {
        $item = [
            'route' => $route['route'],
            'routeName' => $route['routeName'],
            'parentId' => $route['parentId'],
            'isMenu' => $route['isMenu'],
            'isDefault' => $route['isDefault'],
            'groupName' => $route['groupName'],
            'iconName' => $route['iconName'],
        ];
        $item['createUser'] = isset($route['userId']) ?? $this->user['id'];
        $item['updateUser'] = isset($route['userId']) ?? $this->user['id'];
        $item['createTime'] = time();
        $item['updateTime'] = time();
        return $this->permissionRepository->insert($item);
    }

    public function delete(array $param): void
    {
        $this->permissionRepository->deleteAll($param);
    }

    public function getRouteArray(): array
    {
        $list = $this->permissionRepository->list();
        return array_column($list, 'route');
    }


    public function tree(): array
    {
        $permission = $this->permissionRepository->list(['isDefault' => PermissionConstant::UN_DEFAULT_PERMISSION]);
        $tree = $this->tree->data($permission, 'id', 'parentId');
        $defaultIcon = PermissionConstant::DEFAULT_PERMISSION_ICON;
        $default = $this->getDefault();
        foreach ($default as &$item) {
            $item['routeName'] = $defaultIcon . $item['routeName'];
        }
        array_unshift($tree, [
            'id' => "",
            'route' => '用户登陆成功后默认添加',
            'routeName' => $defaultIcon . '默认权限',
            'isDefault' => PermissionConstant::DEFAULT_PERMISSION,
            'children' => $this->tree->data($default, 'id', 'parentId')
        ]);
        return $tree;
    }

    public function getPermissionId(array $array): array
    {
        return $this->permissionRepository->getId($array);
    }

    public function getPermissionRoute(array $idArray): array
    {
        return $this->permissionRepository->getRoute($idArray);
    }

    public function getPermissionRouteName(array $idArray): array
    {
        return $this->permissionRepository->getRouteName($idArray);
    }

    public function getDefaultRouteId(): array
    {
        $rs = $this->getDefault();
        return array_column($rs, 'id');
    }

    public function getDefaultRoute(): array
    {
        $rs = $this->getDefault();
        return array_column($rs, 'route');
    }

    public function getDefault()
    {
        return $this->permissionRepository->default();
    }

    public function menu(array $routeArray): array
    {
        $permission = $this->permissionRepository->list(['isMenu' => PermissionConstant::MENU_PERMISSION, 'route' => $routeArray]);
        $menu = [];
        foreach ($permission as $item) {
            if (isset($menu[$item['groupName']])) {
                $menu[$item['groupName']][] = $item;
            } else {
                $menu[$item['groupName']] = [$item];
            }
        }
        return $menu;
    }

    public function getTreeRouteByRoute(string $routeName): string
    {
        $permission = $this->permissionRepository->list(['route' => $routeName]);
        if (empty($permission)) {
            return "";
        }
        if ($permission[0]['parentId'] == Menu::ROOT) {
            return $routeName;
        }
        $parentPermission = $this->permissionRepository->list(['id' => $permission[0]['parentId']]);
        if (empty($parentPermission)) {
            return "";
        }
        return $this->getTreeRouteByRoute($parentPermission[0]['route']);
    }

    public function getRouteConstant(string $route): string
    {
        return strtoupper(str_replace('-', '_', $route));
    }

    public function getTreeString(): string
    {
        $menu = $this->menu([]);

        $li = "";
        foreach ($menu as $key => $value) {
            if ($key == "") {
                foreach ($value as $item) {
                    $iconName = $item['iconName'];
                    $routeName = $item['routeName'];
                    $route = $item['route'];
                    $routeConstant = $this->getRouteConstant($route);
                    $li .= <<<EOF
                {% if hasPermission('{$routeConstant}') %}
                <li>
                    <a href="{{ path('{$route}') }}"><i class="menu-icon {$iconName}"></i><span>{$routeName}</span></a>
                </li>
                {% endif %}

EOF;
                }
            } else {
                $subLi = "";
                $routeList = array_map(function ($v) {
                    return $this->getRouteConstant($v);
                }, array_column($value, 'route'));
                $routeList = str_replace("\"", "'", json_encode($routeList));
                foreach ($value as $item) {
                    $routeName = $item['routeName'];
                    $route = $item['route'];
                    $routeConstant = $this->getRouteConstant($route);
                    $subLi .= <<<EOF
                        {% if hasPermission('{$routeConstant}') %}
                        <li><a href="{{ path('{$route}') }}">{$routeName}</a></li>
                        {% endif %}

EOF;
                }
                $subUl = <<<EOF
                    <ul class="sub-menu">
{$subLi}
                    </ul>
EOF;

                $li .= <<<EOF

                {% if anyPermissionList({$routeList})%}
                <li>
                    <a href="#">
                        <i class="menu-icon {$value[0]['iconName']}"></i><span>{$key}</span><i class="accordion-icon fa fa-angle-left"></i>
                    </a>
{$subUl}
                </li>
                {% endif %}
EOF;
            }
        }
        return <<<EOF
<div class="page-sidebar">
    <a class="logo-box" href="{{ path(constant_route('HOME')) }}">
        <span>{{ constant('App\\\\Lib\\\\Constant\\\\Tool::PROJECT_NAME') }}</span>
    </a>
    <div class="page-sidebar-inner">
        <div class="page-sidebar-menu" data-auto-load="false" data-load-url="{{ path(constant_route('MENU_TREE')) }}">
            <ul class="accordion-menu">
{$li}
            </ul>
        </div>
    </div>
</div>
<script>
    $(function (){
        let focus = window.location.pathname;
        Tools.ajax({
            url: '{{ path(constant_route("MENU_FOCUS")) }}',
            data: {focus},
            async: false,
            success: function (res) {
                focus = res.focus;
            }
        });
        if(focus === ""){
            focus = '{{ path(constant_route('HOME')) }}';
        }
        let activeMenu = $('.accordion-menu a[href="' + focus + '"]');
        let subMenu = activeMenu.parent().parent();
        if(!subMenu.hasClass('accordion-menu')){
            activeMenu.addClass('active');
            subMenu.show().find('> li').addClass('animation');
            subMenu.parent().addClass("active-page").addClass("open");
        }else{
            activeMenu.parent().addClass('active-page');
        }
    });
</script>
EOF;
    }
}