<?php

namespace App\Repository\Common;

use App\Lib\Constant\Code;

trait CommonRepositoryTrait
{
    public function delete(array $data)
    {
        $item = $this->findOneBy($data);
        if(!empty($item)){
            $this->manager->remove($item);
            $this->manager->flush();
        }
    }

    public function logicDelete(array $data): void
    {
        $item = $this->find($data['id']);
        if(!empty($item)){
            $item->setIsDel(Code::DELETED);
            $item->setUpdateUser($data['updateUser']);
            $item->setUpdateTime($data['updateTime']);
            $this->manager->persist($item);
            $this->manager->flush();
        }
    }
}