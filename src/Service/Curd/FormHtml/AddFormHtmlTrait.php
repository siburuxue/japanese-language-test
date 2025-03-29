<?php

namespace App\Service\Curd\FormHtml;

trait AddFormHtmlTrait
{
    public function getAddFormTextHtml(string $key): string
    {
        return <<<EOF

                            <div class="form-group">
                                <label class="col-sm-2 control-label">{$this->json['columnText'][$key]}：</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control {$this->humpColumnNameMap[$key]}" value="" id="{$this->humpColumnNameMap[$key]}" name="{$this->humpColumnNameMap[$key]}">
                                </div>
                            </div>

EOF;
    }

    public function getAddFormSelectHtml(string $key): string
    {
        $multiple = empty($this->json['type'][$key]['option']) ? "" : "multiple";
        $emptyValue = empty($this->json['type'][$key]['option']) ? '<option value="">请选择</option>' : "";
        return <<<EOF

                            <div class="form-group">
                                <label class="col-sm-2 control-label">{$this->json['columnText'][$key]}：</label>
                                <div class="col-sm-10">
                                    <select {$multiple} class="form-control {$this->humpColumnNameMap[$key]}" id="{$this->humpColumnNameMap[$key]}" name="{$this->humpColumnNameMap[$key]}">
                                        {$emptyValue}
                                        {% for key, val in {$this->humpColumnNameMap[$key]}Dict %}
                                        <option value="{{ key }}">{{ val }}</option>
                                        {% endfor %}
                                    </select>
                                </div>
                            </div>

EOF;

    }

    public function getAddFormRadioHtml(string $key): string
    {
        return <<<EOF
            
                            <div class="form-group">
                                <label class="col-sm-2 control-label">{$this->json['columnText'][$key]}：</label>
                                <div class="col-sm-10">
                                    {% for key, val in {$this->humpColumnNameMap[$key]}Dict %}
                                    <label>
                                        <input type="radio" class="{$this->humpColumnNameMap[$key]}" name="{$this->humpColumnNameMap[$key]}" value="{{ key }}" /> {{ val }}
                                    </label>
                                    {% endfor %}
                                </div>
                            </div>
                    
EOF;
    }

    public function getAddFormCheckboxHtml(string $key): string
    {
        return <<<EOF
                     
                            <div class="form-group">
                                <label class="col-sm-2 control-label">{$this->json['columnText'][$key]}：</label>
                                <div class="col-sm-10">
                                    {% for key, val in {$this->humpColumnNameMap[$key]}Dict %}
                                    <label>
                                        <input type="checkbox" class="{$this->humpColumnNameMap[$key]}" name="{$this->humpColumnNameMap[$key]}" value="{{ key }}" /> {{ val }}
                                    </label>
                                    {% endfor %}
                                </div>
                            </div>
                                           
EOF;
    }
}