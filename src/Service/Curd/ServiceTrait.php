<?php

namespace App\Service\Curd;

use App\Lib\Constant\Element;

trait ServiceTrait
{
    public function writeServiceList(): string
    {
        return <<<EOF
    
    public function list(array \$param)
    {
        return \$this->{$this->humpTable}Repository->list(\$param);
    }
    
    public function getCount(array \$param): int
    {
        return \$this->{$this->humpTable}Repository->getCount(\$param);
    }
    
EOF;

    }

    public function writeServiceInsert(): string
    {
        return <<<EOF

    public function insert(array \$param): ?int
    {
        \$item = \$this->getCreateInfo();
        \$item = array_merge(\$item, \$param);
        return \$this->{$this->humpTable}Repository->insert(\$item);
    }

EOF;
    }

    public function writeServiceUpdate(): string
    {
        return <<<EOF

    public function update(array \$param)
    {
        \$item = \$this->getUpdateInfo();
        \$item = array_merge(\$item, \$param);
        \$this->{$this->humpTable}Repository->update(\$item);
    }

EOF;

    }

    public function writeServiceInfo(): string
    {
        return <<<EOF

    public function detail(int \$id)
    {
        return \$this->{$this->humpTable}Repository->one(\$id);
    }
    
EOF;

    }

    public function writeServiceDelete(): string
    {
        return <<<EOF

    public function delete(int \$id)
    {
        \$item = \$this->getUpdateInfo();
        \$item['id'] = \$id;
        \$this->{$this->humpTable}Repository->logicDelete(\$item);
    }

EOF;

    }

    public function writeServiceExport(): string
    {
        $header = "        \$header = \"\\\"序号\\\"";
        $body = <<<EOF

            \$body = "\"" . \$item->getId() . "\"";

EOF;

        foreach ($this->json['table'] as $key => $item) {
            $header .= ",\\\"{$item}\\\"";
            if(isset($this->json['type'][$key]) && is_array($this->json['type'][$key])){
                $body .= "            \$body .= \",\\\"\" . " . "\$this->dictService->getValues(\"{$this->json['type'][$key]['type']}\", \$item->get" . ucfirst($this->humpColumnNameMap[$key]) . "())" . " . \"\\\"\";" . PHP_EOL;
            }else{
                $body .= "            \$body .= \",\\\"\" . " . "\$item->get" . ucfirst($this->humpColumnNameMap[$key]) . "()" . " . \"\\\"\";" . PHP_EOL;
            }
        }
        $header .= "\";";
        return <<<EOF

    public function getCsv(array \$data):string
    {
        \$csvDir = "/{$this->title}导出/" . date('Ymd'). "/";
        if(!file_exists(\$csvDir)){
            mkdir("./".\$csvDir, 0755, true);
        }
        \$csvName = "data_" . date("YmdHis") . "_" . \\App\\Lib\\Tool\\StringTool::random(10) . ".csv";
        \$csvPath = \$csvDir . \$csvName;
        \$fn = fopen(".".\$csvPath, 'w+');
        fwrite(\$fn, "\xEF\xBB\xBF"); 
{$header}
        fwrite(\$fn, \$header . PHP_EOL);
        foreach(\$data as \$item) {
{$body}
            fwrite(\$fn, \$body . PHP_EOL);
        }
        fclose(\$fn);
        return \$csvPath;
    }

EOF;

    }

    public function writeServiceImport(): string
    {
        $dataStr = "";
        $index = 0;
        foreach ($this->elementColumn as $item) {
            if(in_array($item, array_merge($this->checkboxColumn,$this->selectMultiColumn))){
                $dataStr .= <<<EOF
                \$row[{$index}] = \App\Lib\Tool\StringTool::getArrayFromString(\$row[{$index}]);
                \$data['{$this->humpColumnNameMap[$item]}'] = \$row[{$index}];

EOF;
            }else{
                $dataStr .= <<<EOF
                \$data['{$this->humpColumnNameMap[$item]}'] = (string)\$row[{$index}];

EOF;
            }
            $index++;
        }
        return <<<EOF
    public function import(string \$path):int
    {
        \$index = 0;
        \$item = \$this->getCreateInfo();
        \$excel = new \Vtiful\Kernel\Excel(['path' => '']);
        \$excel->openFile(\$path)->openSheet();
        while ((\$row = \$excel->nextRow()) !== NULL) {
            if(\$index > 0){
                \$data = [];
{$dataStr}
                \$item = array_merge(\$item, \$data);
                \$this->{$this->humpTable}Repository->import(\$item);
            }
            \$index++;
        }
        return \$index - 1;
    }
EOF;

    }
}