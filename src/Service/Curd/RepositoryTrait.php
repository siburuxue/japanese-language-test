<?php

namespace App\Service\Curd;

use App\Lib\Constant\Element;

trait RepositoryTrait
{
    public function getCondition(): array
    {
        $param = $where = "";
        $diff = array_diff($this->varcharColumnList, array_keys($this->json['type']), $this->checkboxColumn,
            $this->selectMultiColumn);
        foreach ($diff as $item) {
            $where .= <<<EOF

        if(!empty(\$param['{$this->humpColumnNameMap[$item]}'])){
            \$sql .= " and t.{$item} like :{$this->humpColumnNameMap[$item]}";
        }
            
EOF;
            $param .= <<<EOF

        if(!empty(\$param['{$this->humpColumnNameMap[$item]}'])){
            \$query->setParameter(":{$this->humpColumnNameMap[$item]}", "'%{\$param['{$this->humpColumnNameMap[$item]}']}%'");
        }
            
EOF;
        }
        foreach ($this->json['type'] as $item => $type) {
            if (is_array($type)) {
                switch ($type['element']) {
                    case Element::SELECT:
                        if (!empty($type['option']) && $type['option'] === 'multiple') {
                            $where .= <<<EOF

        if(!empty(\$param['{$this->humpColumnNameMap[$item]}'])){
           foreach(\$param['{$this->humpColumnNameMap[$item]}'] as \$i => \$key){
               \$sql .= " and :{$this->humpColumnNameMap[$item]}{\$i} member of(t.{$item})";
            } 
        } 
        
EOF;
                            $param .= <<<EOF

        if(!empty(\$param['{$this->humpColumnNameMap[$item]}'])){
           foreach(\$param['{$this->humpColumnNameMap[$item]}'] as \$i => \$key){
               \$query->setParameter(":{$this->humpColumnNameMap[$item]}{\$i}", \$key);
            } 
        } 
        
EOF;
                        } else {
                            $where .= <<<EOF

        if(!empty(\$param['{$this->humpColumnNameMap[$item]}'])){
            \$sql .= " and t.{$item} = :{$this->humpColumnNameMap[$item]}";
        }
            
EOF;
                            $param .= <<<EOF

        if(!empty(\$param['{$this->humpColumnNameMap[$item]}'])){
            \$query->setParameter(":{$this->humpColumnNameMap[$item]}", "'%{\$param['{$this->humpColumnNameMap[$item]}']}%'");
        }
            
EOF;
                        }
                        break;
                    case Element::RADIO:
                        $where .= <<<EOF

        if(!empty(\$param['{$this->humpColumnNameMap[$item]}'])){
            \$sql .= " and t.{$item} = :{$this->humpColumnNameMap[$item]}";
        }
            
EOF;
                        $param .= <<<EOF

        if(!empty(\$param['{$this->humpColumnNameMap[$item]}'])){
            \$query->setParameter(":{$this->humpColumnNameMap[$item]}", "'%{\$param['{$this->humpColumnNameMap[$item]}']}%'");
        }
            
EOF;
                        break;
                    case Element::CHECKBOX:
                        if ($this->tableColumnMap[$item]['Type'] == Element::JSON || str_contains($this->tableColumnMap[$item]['Type'],
                                Element::VARCHAR)) {
                            $where .= <<<EOF

        if(!empty(\$param['{$this->humpColumnNameMap[$item]}'])){
           foreach(\$param['{$this->humpColumnNameMap[$item]}'] as \$i => \$key){
                \$sql .= " and :{$this->humpColumnNameMap[$item]}{\$i} member of(t.{$item})";
            } 
        } 
        
EOF;
                            $param .= <<<EOF

        if(!empty(\$param['{$this->humpColumnNameMap[$item]}'])){
           foreach(\$param['{$this->humpColumnNameMap[$item]}'] as \$i => \$key){
               \$query->setParameter(":{$this->humpColumnNameMap[$item]}{\$i}", \$key);
            } 
        } 
        
EOF;
                        }
                        break;
                }
            } else {
                if (in_array($type, [Element::DATETIME, Element::DATE])) {
                    if ($this->tableColumnMap[$item]['Type'] == Element::INT) {
                        $where .= <<<EOF

        \${$this->humpColumnNameMap[$item]}Arr = explode(' - ', \$param['{$this->humpColumnNameMap[$item]}']); 
        if(!empty(\$param['{$this->humpColumnNameMap[$item]}'])){
            \$sql .= " and t.{$item} >= :{$this->humpColumnNameMap[$item]}start";
            \$sql .= " and t.{$item} <= :{$this->humpColumnNameMap[$item]}end";
        }
            
EOF;
                        $param .= <<<EOF

        if(!empty(\$param['{$this->humpColumnNameMap[$item]}'])){
            \$query->setParameter(":{$this->humpColumnNameMap[$item]}start", strtotime(\${$this->humpColumnNameMap[$item]}Arr[0]));
            \$query->setParameter(":{$this->humpColumnNameMap[$item]}end", strtotime(\${$this->humpColumnNameMap[$item]}Arr[1]));
        }
            
EOF;
                    } else {
                        $where .= <<<EOF

        \${$this->humpColumnNameMap[$item]}Arr = explode(' - ', \$param['{$this->humpColumnNameMap[$item]}']); 
        if(!empty(\$param['{$this->humpColumnNameMap[$item]}'])){
            \$sql .= " and t.{$item} >= :{$this->humpColumnNameMap[$item]}start";
            \$sql .= " and t.{$item} <= :{$this->humpColumnNameMap[$item]}end";
        }
            
EOF;
                        $param .= <<<EOF

        if(!empty(\$param['{$this->humpColumnNameMap[$item]}'])){
            \$query->setParameter(":{$this->humpColumnNameMap[$item]}start", \${$this->humpColumnNameMap[$item]}Arr[0]);
            \$query->setParameter(":{$this->humpColumnNameMap[$item]}end", \${$this->humpColumnNameMap[$item]}Arr[1]);
        }
            
EOF;
                    }
                }
            }
        }
        return [$where, $param];
    }

    public function getRsm():string
    {
        $rsm = "";
        foreach ($this->humpColumnNameMap as $k => $item) {
            $rsm .= "        \$rsm->addFieldResult('t', '{$k}', '{$item}');" . PHP_EOL;
        }
        return <<<EOF
        \$rsm = new \\Doctrine\\ORM\\Query\\ResultSetMapping();
        \$rsm->addEntityResult({$this->upperTable}::class, 't');
{$rsm}
EOF;
    }

    public function writeRepositoryList(): string
    {
        [$where, $param] = $this->getCondition();
        $rsm = $this->getRsm();
        return <<<EOF

    public function getRsm(): \Doctrine\ORM\Query\ResultSetMapping
    {
{$rsm}
        return \$rsm;      
    }
    
    public function getCondition(array \$param): string
    {
        \$sql = "";
 {$where}
        return \$sql;       
    }
    
    public function getQuery(\$sql, \$rsm, \$param): \Doctrine\ORM\NativeQuery
    {
        \$query = \$this->_em->createNativeQuery(\$sql, \$rsm);
{$param}
        return \$query;
    }
    
    public function list(array \$param = []): array
    {
        \$rsm = \$this->getRsm();        
        \$sql = "select t.* from test_dict t where t.is_del = " . Code::UN_DELETE;
        \$sql .= \$this->getCondition(\$param);
        \$sql .= " order by t.id desc";
        
        if(!empty(\$param['page']) && !empty(\$param['limit'])){
            \$page = \$param['page'];
            \$limit = \$param['limit'];
            \$sql .= " limit " . (\$page - 1) * \$limit . ", " . \$limit;
        }
        
        \$query = \$this->getQuery(\$sql, \$rsm, \$param);
        return \$query->getResult();
    }

    public function getCount(array \$param = []): int
    {
        \$rsm = \$this->getRsm();
        \$rsm->addScalarResult('num', 'num');

        \$sql = "select count(1) as num from test_dict t where t.is_del = " . Code::UN_DELETE;
        \$sql .= \$this->getCondition(\$param);
        \$query = \$this->getQuery(\$sql, \$rsm, \$param);
        \$rs = \$query->getResult();
        return (int)\$rs[0]['num'];
    }
    
EOF;

    }

    public function writeRepositoryInsert(): string
    {
        $column = "";
        foreach ($this->elementColumn as $item) {
            $column .= "        if(isset(\$data['{$this->humpColumnNameMap[$item]}'])) {" . PHP_EOL;
            if (in_array($item, array_merge($this->checkboxColumn,
                    $this->selectMultiColumn)) && str_contains($this->tableColumnMap[$item]['Type'],
                    Element::VARCHAR)) {
                $column .= "            \${$this->humpTable}->set" . ucfirst($this->humpColumnNameMap[$item]) . "(json_encode(\$data['{$this->humpColumnNameMap[$item]}']));" . PHP_EOL;
            } else {
                $column .= "            \${$this->humpTable}->set" . ucfirst($this->humpColumnNameMap[$item]) . "(\$data['{$this->humpColumnNameMap[$item]}']);" . PHP_EOL;
            }
            if(!empty($this->json[$item]) && $this->json[$item] == Element::DATETIME){
                if($this->tableColumnMap[$item]['Type'] == Element::INT){
                    $column .= "            \${$this->humpTable}->set" . ucfirst($this->humpColumnNameMap[$item]) . "(strtotime(\$data['{$this->humpColumnNameMap[$item]}']));" . PHP_EOL;
                }
            }
            $column .= "        }" . PHP_EOL;
        }
        return <<<EOF

    public function setColumn({$this->upperTable} &\${$this->humpTable}, array \$data): void
    {
{$column}
    }

    public function makeEntity(array \$data): {$this->upperTable}
    {
        \${$this->humpTable} = new {$this->upperTable}();
        \$this->setColumn(\${$this->humpTable}, \$data);
        \${$this->humpTable}->setCreateUser(\$data['createUser']);
        \${$this->humpTable}->setCreateTime(\$data['createTime']);
        \${$this->humpTable}->setUpdateUser(\$data['updateUser']);
        \${$this->humpTable}->setUpdateTime(\$data['updateTime']);
        return \${$this->humpTable};
    }

    public function insert(array \$data): ?int
    {
        \$entity = \$this->makeEntity(\$data);
        \$this->manager->persist(\$entity);
        \$this->manager->flush();
        return \$entity->getId();
    }
    
EOF;

    }

    public function writeRepositoryUpdate(): string
    {
        return <<<EOF

    public function update(array \$data): void
    {
        \${$this->humpTable} = \$this->one(\$data['id']);
        \$this->setColumn(\${$this->humpTable}, \$data);
        \${$this->humpTable}->setUpdateUser(\$data['updateUser']);
        \${$this->humpTable}->setUpdateTime(\$data['updateTime']);
        \$this->manager->persist(\${$this->humpTable});
        \$this->manager->flush();
    }

EOF;

    }

    public function writeResourceImport(): string
    {
        return <<<EOF

    public function import(array \$data): void
    {
        \$this->_em->getConnection()->getConfiguration()->setSQLLogger(null);
        \$entity = \$this->makeEntity(\$data);
        \$this->_em->persist(\$entity);
        \$this->_em->flush();
        \$this->_em->detach(\$entity);
        \$this->_em->clear();
    }

EOF;
    }
}