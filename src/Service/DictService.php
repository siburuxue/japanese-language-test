<?php

namespace App\Service;

use App\Entity\Dict;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class DictService extends CommonService
{
    private ObjectRepository $dictRegistry;

    public function __construct(ManagerRegistry $doctrine, RequestStack $requestStack)
    {
        parent::__construct($requestStack);
        $this->dictRegistry = $doctrine->getRepository(Dict::class);
    }

    /**
     * @param string $key
     * @return array
     */
    public function getDictMap(string $key): array
    {
        $result = $this->dictRegistry->getOptionMap($key);
        return array_column($result,'dValue','dKey');
    }

    public function getValues(string $key, mixed $value): string
    {
        $map = $this->getDictMap($key);
        if(is_array($value)){
            if(empty($value)){
                return "";
            }
            return implode(",", array_map(function($v)use($map){ return $map[$v] ?? ""; },$value));
        }else{
            if(str_contains($value, "[") || str_contains($value, "{")){
                $value = json_decode($value, true);
                if(empty($value)){
                   return "";
                }else{
                    return implode(",", array_map(function($v)use($map){ return $map[$v] ?? ""; },$value));
                }
            }else{
                return $map[$value] ?? "";
            }
        }
    }

    public function list(array $data = [])
    {
        return $this->dictRegistry->list($data);
    }
}