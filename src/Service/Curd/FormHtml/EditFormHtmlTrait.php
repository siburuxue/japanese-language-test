<?php

namespace App\Service\Curd\FormHtml;

trait EditFormHtmlTrait
{
    public function getEditFormTextHtml(string $key): string
    {
        return <<<EOF

                            <div class="form-group">
                                <label class="col-sm-2 control-label">{$this->json['columnText'][$key]}：</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control {$this->humpColumnNameMap[$key]}" value="{{ info.{$this->humpColumnNameMap[$key]} }}" id="{$this->humpColumnNameMap[$key]}" name="{$this->humpColumnNameMap[$key]}">
                                </div>
                            </div>

EOF;
    }

    public function getEditFormSelectHtml(string $key): string
    {
        $emptyValue = $multiple = $selected = "";
        if(!empty($this->json['type'][$key]['option'])){
            $multiple = "multiple";
            $selected = "{% if key in info.{$this->humpColumnNameMap[$key]} %}selected{% endif %}";
        }else{
            $emptyValue = '<option value="">请选择</option>';
            $selected = "{% if info.{$this->humpColumnNameMap[$key]} == key %}selected{% endif %}";
        }
        return <<<EOF

                            <div class="form-group">
                                <label class="col-sm-2 control-label">{$this->json['columnText'][$key]}：</label>
                                <div class="col-sm-10">
                                    <select {$multiple} class="form-control {$this->humpColumnNameMap[$key]}" id="{$this->humpColumnNameMap[$key]}" name="{$this->humpColumnNameMap[$key]}">
                                        {$emptyValue}
                                        {% for key, val in {$this->humpColumnNameMap[$key]}Dict %}
                                        <option value="{{ key }}" {$selected}>{{ val }}</option>
                                        {% endfor %}
                                    </select>
                                </div>
                            </div>

EOF;

    }

    public function getEditFormRadioHtml(string $key): string
    {
        return <<<EOF
            
                            <div class="form-group">
                                <label class="col-sm-2 control-label">{$this->json['columnText'][$key]}：</label>
                                <div class="col-sm-10">
                                    {% for key, val in {$this->humpColumnNameMap[$key]}Dict %}
                                    <label>
                                        <input type="radio" class="{$this->humpColumnNameMap[$key]}" name="{$this->humpColumnNameMap[$key]}" value="{{ key }}" {% if info.{$this->humpColumnNameMap[$key]} == key %}checked{% endif %} /> {{ val }}
                                    </label>
                                    {% endfor %}
                                </div>
                            </div>
                    
EOF;
    }

    public function getEditFormCheckboxHtml(string $key): string
    {
        return <<<EOF
                     
                            <div class="form-group">
                                <label class="col-sm-2 control-label">{$this->json['columnText'][$key]}：</label>
                                <div class="col-sm-10">
                                    {% for key, val in {$this->humpColumnNameMap[$key]}Dict %}
                                    <label>
                                        <input type="checkbox" class="{$this->humpColumnNameMap[$key]}" name="{$this->humpColumnNameMap[$key]}" value="{{ key }}" {% if key in info.{$this->humpColumnNameMap[$key]}  %}checked{% endif %} /> {{ val }}
                                    </label>
                                    {% endfor %}
                                </div>
                            </div>
                                           
EOF;
    }
}