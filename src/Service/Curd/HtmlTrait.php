<?php

namespace App\Service\Curd;

trait HtmlTrait
{
    private function indexHtml($searchHtml, $initHtml, $searchColumn, $paramColumn, $exportJs, $importJs ): string
    {
        return <<<EOF
{% extends './admin/base/base.html.twig' %}
{% block content %}
    <div class="page-title">
        <h3 class="breadcrumb-header">
            {$this->title}列表
        </h3>
    </div>
    <div id="main-wrapper">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-white">
                    <div class="panel-heading">
                        <form class="form-horizontal">
{$searchHtml}
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="panel-body">
                    <div class="table-responsive">
                        {% if hasPermission('{$this->listRouteConst}') %}
                        <div id="data-list" class="dataTables_wrapper table-box" data-auto-load="true" data-load-url="{{ path(constant_route('{$this->listRouteConst}')) }}" data-param="{}"></div>
                        {% endif %}
                    </div>
                </div>
            </div>
        </div>
    </div>
{% endblock %}
{% block js %}
    <script type="text/javascript">
        \$(function (){
{$initHtml}
            \$('#search').click(function(){
{$searchColumn}
                \$('#data-list').data('param', {$paramColumn}).loadData();
            });
{$exportJs}
{$importJs}
        });
    </script>
{% endblock %}
EOF;
    }

    private function listHtml($th, $td): string
    {
        $action = $actionButton = "";
        if (!$this->readonly) {
            $action = "        <th>操作</th>";
            $actionButton = <<<EOF
            <td>
                {% if hasPermission('{$this->infoRouteConst}') %}
                <a href="{{ path(constant_route('{$this->infoRouteConst}'), {id : item.id}) }}" class="btn btn-default btn-xs"><i class="fa fa-search"></i> 查看</a>
                {% endif %}
                {% if hasPermission('{$this->editRouteConst}') %}
                <a href="{{ path(constant_route('{$this->editRouteConst}'), {id : item.id}) }}" class="btn btn-primary btn-xs"><i class="fa fa-pencil"></i> 编辑</a>
                {% endif %}
                {% if hasPermission('{$this->deleteRouteConst}') %}
                <button type="button" class="btn btn-danger btn-xs table-confirm-change-status" data-url="{{ path(constant_route('{$this->deleteRouteConst}'), {'id' : item.id}) }}"><i class="fa fa-trash"></i> 删除</button>
                {% endif %}
            </td>
EOF;

        }
        return <<<EOF
<table class="table table-bordered">
    <thead>
    <tr>
        <th>#</th>
{$th}
{$action}
    </tr>
    </thead>
    {% if data is not empty %}
    <tbody>
    {% for item in data %}
        <tr>
            <th scope="row">{{ item.id }}</th>
{$td}
{$actionButton}
        </tr>
    {% endfor %}
    </tbody>
    {% endif %}
</table>
{% if data is empty %}
<h1 class="no-data">无数据</h1>
{% endif %}
{{ pagination|raw }}
EOF;
    }

    private function addHtml($columnHtml, $initHtml, $columnVar, $columnVerify, $columnParam): string
    {
        return <<<EOF
{% extends './admin/base/base.html.twig' %}

{% block content %}
    <div class="page-title">
        <h3 class="breadcrumb-header">
            {$this->title}新增
            {% if hasPermission('{$this->indexRouteConst}') %}
            <a href="{{ path(constant_route('{$this->indexRouteConst}')) }}" class="btn btn-primary"><i class="fa fa-undo"></i>返回</a>
            {% endif %}
        </h3>
    </div>
    <div id="main-wrapper">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-white">
                    <div class="panel-heading">
                        <form class="form-horizontal">
{$columnHtml}
                            <div class="form-group">
                                <label for="input-Default" class="col-sm-2 control-label"></label>
                                <div class="col-sm-10">
                                    <button type="reset" id="clear" class="btn btn-default"><i class="fa fa-undo"></i> 重置</button>
                                    {% if hasPermission('{$this->insertRouteConst}') %}
                                    <button type="button" class="btn btn-primary" id="save"><i class="fa fa-save"></i> 保存</button>
                                    {% endif %}
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
{% endblock %}
{% block js %}
    <script type="text/javascript">
        \$(function () {
{$initHtml}
            \$('#save').click(function () {
{$columnVar}
{$columnVerify}
                Tools.ajax({
                    url: "{{ path(constant_route('{$this->insertRouteConst}')) }}",
                    data: {$columnParam},
                    success: function(res){
                        layer.msg(res.msg,{time:1500},function(){
                            if(res.status){
                                window.location.href = '{{ path(constant_route('{$this->indexRouteConst}')) }}';
                            }
                        });
                    }
                });
            });
        });
    </script>
{% endblock %}
EOF;
    }

    private function infoHtml($columnHtml): string
    {
        return <<<EOF
{% extends './admin/base/base.html.twig' %}

{% block content %}
    <div class="page-title">
        <h3 class="breadcrumb-header">
            {$this->title}详细
            {% if hasPermission('{$this->indexRouteConst}') %}
            <a href="{{ path(constant_route('{$this->indexRouteConst}')) }}" class="btn btn-primary"><i class="fa fa-undo"></i>返回</a>
            {% endif %}
        </h3>
    </div>
    <div id="main-wrapper">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-white">
                    <div class="panel-heading">
                        <form class="form-horizontal">
{$columnHtml}
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
{% endblock %}
{% block js %}
    <script type="text/javascript">
        \$(function () {
            $('#main-wrapper *').prop('disabled', true);
        });
    </script>
{% endblock %}

EOF;
    }

    private function editHtml($columnHtml, $initHtml, $columnVar, $columnVerify, $columnParam): string
    {
        return <<<EOF
{% extends './admin/base/base.html.twig' %}

{% block content %}
    <div class="page-title">
        <h3 class="breadcrumb-header">
            {$this->title}编辑
            {% if hasPermission('{$this->indexRouteConst}') %}
            <a href="{{ path(constant_route('{$this->indexRouteConst}')) }}" class="btn btn-primary"><i class="fa fa-undo"></i>返回</a>
            {% endif %}
        </h3>
    </div>
    <div id="main-wrapper">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-white">
                    <div class="panel-heading">
                        <form class="form-horizontal">
{$columnHtml}
                            <div class="form-group">
                                <label for="input-Default" class="col-sm-2 control-label"></label>
                                <div class="col-sm-10">
                                    <input type="hidden" id="id" value="{{info.id}}">
                                    <button type="reset" id="clear" class="btn btn-default"><i class="fa fa-undo"></i> 重置</button>
                                    {% if hasPermission('{$this->updateRouteConst}') %}
                                    <button type="button" class="btn btn-primary" id="save"><i class="fa fa-save"></i> 保存</button>
                                    {% endif %}
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
{% endblock %}
{% block js %}
    <script type="text/javascript">
        \$(function () {
{$initHtml}
            \$('#save').click(function () {
                let id = $('#id').val()
{$columnVar}
{$columnVerify}
                Tools.ajax({
                    url: "{{ path(constant_route('{$this->updateRouteConst}')) }}",
                    data: {$columnParam},
                    success: function(res){
                        layer.msg(res.msg,{time:1500},function(){
                            if(res.status){
                                window.location.href = '{{ path(constant_route('{$this->indexRouteConst}')) }}';
                            }
                        });
                    }
                });
            });
        });
    </script>
{% endblock %}
EOF;
    }

    private function getAddButtonHtml(): string
    {
        if ($this->readonly) {
            return "";
        }
        return <<<EOF
                                    {% if hasPermission('{$this->addRouteConst}') %}
                                    <a href="{{ path(constant_route('{$this->addRouteConst}')) }}" class="btn btn-primary"><i class="fa fa-plus"></i> 新增</a>
                                    {% endif %}
EOF;

    }

    private function getExportButtonHtml(): string
    {
        if ($this->export) {
            return <<<EOF
                                    {% if hasPermission('{$this->exportRouteConst}') %}
                                    <button type="button" class="btn btn-primary" id="export"><i class="fa fa-download"></i> 导出</button>
                                    {% endif %}
EOF;
        }
        return "";
    }

    private function getImportButtonHtml(): string
    {
        if ($this->import) {
            return <<<EOF
                                    {% if hasPermission('{$this->importRouteConst}') %}
                                    <button type="button" class="btn btn-primary" id="import"><i class="fa fa-upload"></i> 导入</button>
                                    <input type="file" id="file" name="file" style="display: none">
                                    {% endif %}
EOF;
        }
        return "";
    }
}