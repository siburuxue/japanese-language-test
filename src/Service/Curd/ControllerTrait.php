<?php

namespace App\Service\Curd;

use App\Lib\Constant\Element;

trait ControllerTrait
{
    /**
     * 列表页加载
     * @return string
     */
    private function indexFunction(): string
    {
        [$dictVar, $dictKey, $thisDictVar] = $this->getDictVar();
        return <<<EOF

    public function index(DictService \$dictService): Response
    {
{$dictVar}
        return \$this->render("admin/{$this->routePathPrefix}/index.html.twig", [
{$dictKey}
        ]);
    }
    
EOF;
    }

    /**
     * 列表页查询
     * @return string
     */
    private function listFunction(): string
    {
        [$dictVar, $dictKey, $thisDictVar] = $this->getDictVar();

        return <<<EOF

    public function list(Request \$request, DictService \$dictService, {$this->service}Service \${$this->humpTable}Service, PaginatorInterface \$paginator): Response
    {
        if (!\$this->csrfValid()) {
            return new Response(Tool::CSRF_ERROR);
        }
{$dictVar}
        \$page = (int)\$request->request->get('page', 1);
        \$limit = (int)\$request->request->get('limit', 10);
        \$param = Request::createFromGlobals()->request->all();
        \$total = \${$this->humpTable}Service->getCount(\$param);
        \$data = \${$this->humpTable}Service->list(\$param);
        \$pagination = PageTool::getDefaultPaginate(\$page, \$limit, \$total);
        return \$this->render('admin/{$this->routePathPrefix}/list.html.twig', [
            'pagination' => \$pagination,
            'data' => \$data,
{$dictKey}
        ]);   
    }
    
EOF;

    }

    /**
     * 添加页加载
     * @return string
     */
    private function addFunction(): string
    {
        [$dictVar, $dictKey, $thisDictVar] = $this->getDictVar();
        return <<<EOF
    
    public function add(DictService \$dictService): Response
    {
{$dictVar}
        return \$this->render("admin/{$this->routePathPrefix}/add.html.twig", [
{$dictKey}
        ]);
    }
    
EOF;
    }

    /**
     * 添加页提交
     * @return string
     */
    private function insertFunction(): string
    {
        $column = $this->getMultipleVar();
        return <<<EOF
        
    public function insert(Request \$request, {$this->service}Service \${$this->humpTable}Service): JsonResponse
    {
        if (!\$this->csrfValid()) {
            return \$this->error(Tool::CSRF_ERROR);
        }
        \$data = \$request->request->all();
{$column}        
        \${$this->humpTable}Service->insert(\$data);
        return \$this->insertSuccess();       
    }
        
EOF;

    }

    /**
     * 更新页加载
     * @return string
     */
    private function editFunction(): string
    {
        [$dictVar, $dictKey, $thisDictVar] = $this->getDictVar();
        return <<<EOF
        
    public function edit(Request \$request, DictService \$dictService, {$this->service}Service \${$this->humpTable}Service): Response
    {
{$dictVar}
        \$data = \$request->query->all();
        \$info = \${$this->humpTable}Service->detail(\$data['id']);
        return \$this->render("admin/{$this->routePathPrefix}/edit.html.twig",[
            'info' => \$info,
{$dictKey}
        ]);
    }
        
EOF;
    }

    /**
     * 更新页提交
     * @return string
     */
    private function updateFunction(): string
    {
        $column = $this->getMultipleVar();
        return <<<EOF

    public function update(Request \$request, {$this->service}Service \${$this->humpTable}Service): JsonResponse
    {
        if (!\$this->csrfValid()) {
            return \$this->error(Tool::CSRF_ERROR);
        }
        \$data = \$request->request->all();
{$column}        
        \${$this->humpTable}Service->update(\$data);
        return \$this->updateSuccess();
    }

EOF;

    }

    /**
     * 删除
     * @return string
     */
    private function deleteFunction(): string
    {
        return <<<EOF

    public function delete(Request \$request, {$this->service}Service \${$this->humpTable}Service): JsonResponse
    {
        if (!\$this->csrfValid()) {
            return \$this->error(Tool::CSRF_ERROR);
        }
        \$id = \$request->query->get('id'); 
        \${$this->humpTable}Service->delete(\$id);
        return \$this->deleteSuccess();
    }

EOF;

    }

    /**
     * 查看
     * @return string
     */
    private function infoFunction(): string
    {
        [$dictVar, $dictKey, $thisDictVar] = $this->getDictVar();
        return <<<EOF
        
    public function info(Request \$request,DictService \$dictService, {$this->service}Service \${$this->humpTable}Service): Response
    {
{$dictVar}
        \$data = \$request->query->all();
        \$info = \${$this->humpTable}Service->detail(\$data['id']);
        return \$this->render("admin/{$this->routePathPrefix}/info.html.twig",[
            'info' => \$info,
{$dictKey}
        ]);
    }
        
EOF;

    }

    private function exportFunction():string
    {
        return <<<EOF

    public function export(Request \$request, {$this->service}Service \${$this->humpTable}Service): JsonResponse
    {
        if (!\$this->csrfValid()) {
            return \$this->error(Tool::CSRF_ERROR);
        }
        \$param = Request::createFromGlobals()->request->all();
        \$data = \${$this->humpTable}Service->list(\$param);
        \$csvPath = \${$this->humpTable}Service->getCsv(\$data);
        return \$this->json(["url" => \$csvPath]);
    }
    
EOF;

    }

    private function importFunction():string
    {
        return <<<EOF

    public function import(Request \$request, {$this->service}Service \${$this->humpTable}Service): JsonResponse
    {
        if (!\$this->csrfValid()) {
            return \$this->error(Tool::CSRF_ERROR);
        }
        \$path = \$request->files->get('file')->getPathName();
        \$count = \${$this->humpTable}Service->import(\$path);
        return \$this->success("上传成功，共{\$count}条数据。");
    }
    
EOF;

    }
}