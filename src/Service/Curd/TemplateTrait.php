<?php

namespace App\Service\Curd;

use App\Lib\Constant\Element;

trait TemplateTrait
{

    use HtmlTrait;

    private function writeIndexTwig(): void
    {
        $searchHtml = $searchColumn = "";
        $paramColumn = "{";
        $count = count($this->conditionColumn);
        $lastColumn = "";
        $addButton = $this->getAddButtonHtml();
        $exportButton = $this->getExportButtonHtml();
        $importButton = $this->getImportButtonHtml();
        if ($count % 2 == 0) {
            $lastTr = <<<EOF
                            <div class="form-group" style="margin-bottom: 0">
                                <div class="col-sm-12 control-label">
                                    <button type="reset" class="btn btn-default" id="clear"><i class="fa fa-undo"></i> 清空</button>
                                    {% if hasPermission('{$this->indexRouteConst}') %}
                                    <button type="button" class="btn btn-primary" id="search"><i class="fa fa-search"></i> 查询</button>
                                    {% endif %}
{$addButton}
{$exportButton}
{$importButton}
                                </div>
                            </div>
EOF;
        } else {
            $lastColumn = array_pop($this->conditionColumn);
            $searchColumn .= $this->getConditionJs($lastColumn);
            $paramColumn .= $this->humpColumnNameMap[$lastColumn] . ",";
            $textHtml = $this->getHtml($lastColumn, "Select");
            $lastTr = <<<EOF
                            <div class="form-group" style="margin-bottom: 0">
{$textHtml}
                                <div class="col-sm-6 control-label">
                                    <button type="reset" class="btn btn-default" id="clear"><i class="fa fa-undo"></i> 清空</button>
                                    {% if hasPermission('{$this->indexRouteConst}') %}
                                    <button type="button" class="btn btn-primary" id="search"><i class="fa fa-search"></i> 查询</button>
                                    {% endif %}
{$addButton}
{$exportButton}
{$importButton}
                                </div>
                            </div>
EOF;
        }
        $columnGroup = array_chunk($this->conditionColumn, 2);
        foreach ($columnGroup as $item) {
            $groupHtml0 = $this->getHtml($item[0], "Select");
            $groupHtml1 = $this->getHtml($item[1], "Select");
            $searchHtml .= <<<EOF

                            <div class="form-group">
{$groupHtml0}
{$groupHtml1}
                            </div>

EOF;
            $searchColumn .= $this->getConditionJs($item[0]);
            $searchColumn .= $this->getConditionJs($item[1]);
            $paramColumn .= $this->humpColumnNameMap[$item[0]] . ",";
            $paramColumn .= $this->humpColumnNameMap[$item[1]] . ",";
        }
        $searchHtml .= $lastTr;
        $paramColumn .= "}";
        if ($count % 2 > 0) {
            $this->conditionColumn[] = $lastColumn;
        }
        $initHtml = $this->getRangeInitHtml();
        $exportJs = $this->getExportJs($searchColumn, $paramColumn);
        $importJs = $this->getImportJs();
        $html = $this->indexHtml($searchHtml, $initHtml, $searchColumn, $paramColumn, $exportJs, $importJs);
        file_put_contents($this->templateDir . "/index.html.twig", $html);
    }

    private function writeListTwig(): void
    {
        $th = $td = "";
        foreach ($this->json['table'] as $key => $item) {
            $th .= "        <th>{$item}</th>" . PHP_EOL;
            if (array_key_exists($key, $this->dictSource)) {
                if(($this->json['type'][$key]['element'] == Element::CHECKBOX) || ($this->json['type'][$key]['element'] == Element::SELECT && !empty($this->json['type'][$key]['option']) && $this->json['type'][$key]['option'] === 'multiple')) {
                    $td .= "            <td>{{ item." . $this->humpColumnNameMap[$key] . "|dictMulti(" . $this->humpColumnNameMap[$key] . "Dict)}}</td>" . PHP_EOL;
                }else{
                    $td .= "            <td>{{ item." . $this->humpColumnNameMap[$key] . "|dict(" . $this->humpColumnNameMap[$key] . "Dict)}}</td>" . PHP_EOL;
                }
            } else {
                $td .= "            <td>{{ item." . $this->humpColumnNameMap[$key] . "}}</td>" . PHP_EOL;
            }
        }
        $html = $this->listHtml($th, $td);
        file_put_contents($this->templateDir . "/list.html.twig", $html);
    }

    public function writeAddTwig(): void
    {
        $columnVar = $columnHtml = $columnVerify = "";
        $columnParam = "{";
        foreach ($this->elementColumn as $item) {
            $columnHtml .= $this->getHtml($item, "Add");
            $columnParam .= $this->humpColumnNameMap[$item] . ", ";
            $columnVar .= $this->getVarJs($item);
            $columnVerify .= $this->getVerifyJs($item);
        }
        $columnParam .= "}";
        $initHtml = $this->getIUInitHtml();
        $html = $this->addHtml($columnHtml, $initHtml, $columnVar, $columnVerify, $columnParam);
        file_put_contents($this->templateDir . "/add.html.twig", $html);
    }

    public function writeInfoTwig(): void
    {
        $columnHtml = "";
        foreach ($this->elementColumn as $item) {
            $columnHtml .= $this->getHtml($item, "Edit");
        }
        $html = $this->infoHtml($columnHtml);
        file_put_contents($this->templateDir . "/info.html.twig", $html);
    }

    public function writeEditTwig(): void
    {
        $columnVar = $columnHtml = $columnVerify = "";
        $columnParam = "{id, ";
        foreach ($this->elementColumn as $item) {
            $columnHtml .= $this->getHtml($item, "Edit");
            $columnParam .= $this->humpColumnNameMap[$item] . ", ";
            $columnVar .= $this->getVarJs($item);
            $columnVerify .= $this->getVerifyJs($item);
        }
        $columnParam .= "}";
        $initHtml = $this->getIUInitHtml();
        $html = $this->editHtml($columnHtml, $initHtml,$columnVar, $columnVerify, $columnParam);
        file_put_contents($this->templateDir . "/edit.html.twig", $html);
    }
}