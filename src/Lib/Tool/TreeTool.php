<?php

namespace App\Lib\Tool;

use App\Lib\Constant\Tool;

class TreeTool
{
    private array $rs = [];

    private string $type;

    /**
     * $treeTool->data($data,'id','parentId');
     * @param array $data
     * @param string $column
     * @param string $parent
     * @param mixed $initParent
     * @param $type
     * @return array
     */
    public function data(array $data, string $column, string $parent, mixed $initParent = 0, $type = Tool::TYPE_ARRAY): array
    {
        $this->rs = $data;
        $this->type = $type;
        return $this->tree($column,$parent,$initParent);
    }

    private function tree(string $column, string $parent, mixed $initParent): array
    {
        $list = [];
        foreach ($this->rs as $r) {
            if($r[$parent] == $initParent){
                $tmp = $r;
                $tmp['children'] = $this->tree($column,$parent,$r[$column]);
                if($this->type == Tool::TYPE_ARRAY){
                    $list[] = $tmp;
                }else{
                    $list[$r[$column]] = $tmp;
                }
            }
        }
        return $list;
    }
}