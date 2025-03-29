<?php

namespace App\Service\Curd\FormHtml;

trait SelectFromHtmlTrait
{
    public function getSelectFormTextHtml(string $key): string
    {
        return <<<EOF
                                <label for="group-name" class="col-sm-1 control-label">{$this->json['columnText'][$key]}</label>
                                <div class="col-sm-5">
                                    <input type="text" class="form-control {$this->humpColumnNameMap[$key]}" id="{$this->humpColumnNameMap[$key]}" name="{$this->humpColumnNameMap[$key]}">
                                </div>
EOF;
    }

    public function getSelectFormSelectHtml(string $key): string
    {
        $multiple = empty($this->json['type'][$key]['option']) ? "" : "multiple";
        $emptyValue = empty($this->json['type'][$key]['option']) ? '<option value="">请选择</option>' : "";
        return <<<EOF

                                <label for="group-name" class="col-sm-1 control-label">{$this->json['columnText'][$key]}</label>
                                <div class="col-sm-5">
                                    <select {$multiple} class="form-control {$this->humpColumnNameMap[$key]}" id="{$this->humpColumnNameMap[$key]}" name="{$this->humpColumnNameMap[$key]}">
                                        {$emptyValue}
                                        {% for key, val in {$this->humpColumnNameMap[$key]}Dict %}
                                        <option value="{{ key }}">{{ val }}</option>
                                        {% endfor %}
                                    </select>
                                </div>

EOF;
    }

    public function getSelectFormRadioHtml(string $key): string
    {
        return <<<EOF
        
                                <label for="group-name" class="col-sm-1 control-label">{$this->json['columnText'][$key]}</label>
                                <div class="col-sm-5">
                                    {% for key, val in {$this->humpColumnNameMap[$key]}Dict %}
                                    <label>
                                        <input type="radio" class="{$this->humpColumnNameMap[$key]}" name="{$this->humpColumnNameMap[$key]}" value="{{ key }}" /> {{ val }}
                                    </label>
                                    {% endfor %}
                                </div>
                        
EOF;
    }

    public function getSelectFormCheckboxHtml(string $key): string
    {
        return <<<EOF
                     
                                <label for="group-name" class="col-sm-1 control-label">{$this->json['columnText'][$key]}</label>
                                <div class="col-sm-5">
                                    {% for key, val in {$this->humpColumnNameMap[$key]}Dict %}
                                    <label>
                                        <input type="checkbox" class="{$this->humpColumnNameMap[$key]}" name="{$this->humpColumnNameMap[$key]}" value="{{ key }}" /> {{ val }}
                                    </label>
                                    {% endfor %}
                                </div>
                                           
EOF;
    }
}