<?php

namespace App\Service\Curd;

use App\Lib\Constant\Element;
use App\Service\Curd\FormHtml\JsFormHtmlTrait;
use App\Service\Curd\FormHtml\SelectFromHtmlTrait;
use App\Service\Curd\FormHtml\AddFormHtmlTrait;
use App\Service\Curd\FormHtml\EditFormHtmlTrait;

trait ElementTrait
{
    use SelectFromHtmlTrait;

    use AddFormHtmlTrait;

    use EditFormHtmlTrait;

    use JsFormHtmlTrait;

    public function datetimeHtml(string $item, bool $range = false): string
    {
        $rangeStr = "";
        if ($range) {
            $rangeStr = ",range: true";
        }
        return <<<EOF
            laydate.render({
              elem: '#{$this->humpColumnNameMap[$item]}'
              ,type: 'datetime'
              {$rangeStr}
            });

EOF;
    }

    public function dateHtml(string $item, bool $range = false): string
    {
        $rangeStr = "";
        if ($range) {
            $rangeStr = ",range: true";
        }
        return <<<EOF
            laydate.render({
              elem: '#{$this->humpColumnNameMap[$item]}'
              ,type: 'date'
              {$rangeStr}
            });

EOF;
    }

    public function getIUInitHtml(): string
    {
        $initHtml = "";
        foreach ($this->json['type'] as $item => $type) {
            if ($type == Element::DATETIME) {
                $initHtml .= $this->datetimeHtml($item);
            }
            if ($type == Element::DATE) {
                $initHtml .= $this->dateHtml($item);
            }
        }
        return $initHtml;
    }

    public function getRangeInitHtml(): string
    {
        $initHtml = "";
        foreach ($this->json['type'] as $item => $type) {
            if ($type == Element::DATETIME) {
                $initHtml .= $this->datetimeHtml($item, true);
            }
            if ($type == Element::DATE) {
                $initHtml .= $this->dateHtml($item, true);
            }
        }
        return $initHtml;
    }

    public function getDictVar(): array
    {
        $thisDictVar = $dictVar = $dictKey = "";
        foreach ($this->dictSource as $index => $item) {
            $dictVar .= <<<EOF

        \${$this->humpColumnNameMap[$index]}Dict = \$dictService->getDictMap("{$item['type']}");
        
EOF;
            $thisDictVar .= <<<EOF

        \${$this->humpColumnNameMap[$index]}Dict = \$this->dictService->getDictMap("{$item['type']}");
        
EOF;
            $dictKey .= <<<EOF

            '{$this->humpColumnNameMap[$index]}Dict' => \${$this->humpColumnNameMap[$index]}Dict,

EOF;
        }
        return [$dictVar, $dictKey, $thisDictVar];
    }

    public function getMultipleVar():string
    {
        $column = "";
        foreach ($this->elementColumn as $item) {
            if (in_array($item, array_merge($this->checkboxColumn, $this->selectMultiColumn)) ) {
                $column .= "        if(isset(\$data['{$this->humpColumnNameMap[$item]}'])) {" . PHP_EOL;
                $column .= "            \$data['{$this->humpColumnNameMap[$item]}'] = json_decode(\$data['{$this->humpColumnNameMap[$item]}'], true);" . PHP_EOL;
                $column .= "        }" . PHP_EOL;
            }
        }
        return $column;
    }

    public function getHtml(string $key, String $prefix="Select"): string
    {
        $selectFunction = "get{$prefix}FormSelectHtml";
        $radioFunction = "get{$prefix}FormRadioHtml";
        $checkboxFunction = "get{$prefix}FormCheckboxHtml";
        $textFunction = "get{$prefix}FormTextHtml";
        if (array_key_exists($key, $this->json['type']) && is_array($this->json['type'][$key])) {
            $elementType = $this->json['type'][$key]['element'];
            return match ($elementType) {
                Element::SELECT => $this->$selectFunction($key),
                Element::RADIO => $this->$radioFunction($key),
                Element::CHECKBOX => $this->$checkboxFunction($key),
                default => $this->$textFunction($key),
            };
        }
        return $this->$textFunction($key);
    }
}