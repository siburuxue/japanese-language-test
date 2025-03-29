<?php

namespace App\Service\Curd\FormHtml;

use App\Lib\Constant\Element;

trait JsFormHtmlTrait
{
    public function getVarJs(string $item): string
    {
        if(!empty($this->json['type'][$item])) {
            if(!empty($this->json['type'][$item]['type'])){
                switch($this->json['type'][$item]['element']){
                    case Element::RADIO:
                        return <<<EOF

                let {$this->humpColumnNameMap[$item]} = \$('.{$this->humpColumnNameMap[$item]}:checked').val();
                
EOF;
                    case Element::CHECKBOX:
                        return <<<EOF

                let {$this->humpColumnNameMap[$item]} = \$('.{$this->humpColumnNameMap[$item]}:checked').map(function(){ return $(this).val() }).get();
                {$this->humpColumnNameMap[$item]} = JSON.stringify({$this->humpColumnNameMap[$item]});
                
EOF;
                    case Element::SELECT:
                        if(!empty($this->json['type'][$item]['option']) && $this->json['type'][$item]['option'] === 'multiple'){
                            return <<<EOF

                let {$this->humpColumnNameMap[$item]} = \$('.{$this->humpColumnNameMap[$item]} option:selected').map(function(){ return $(this).val() }).get();
                {$this->humpColumnNameMap[$item]} = JSON.stringify({$this->humpColumnNameMap[$item]});
                
EOF;
                        }else{
                            return <<<EOF

                let {$this->humpColumnNameMap[$item]} = \$('.{$this->humpColumnNameMap[$item]}').val().trim();
                
EOF;
                        }

                }
            }
        }
        return <<<EOF

                let {$this->humpColumnNameMap[$item]} = \$('.{$this->humpColumnNameMap[$item]}').val().trim();
                
EOF;
    }
    public function getVerifyJs(string $item): string
    {
        if($this->tableColumnMap[$item]['Null'] === "YES"){
            return "";
        }
        if(!empty($this->json['type'][$item])) {
            if(!empty($this->json['type'][$item]['type'])){
                switch($this->json['type'][$item]['element']){
                    case Element::RADIO:
                    case Element::CHECKBOX:
                        return <<<EOF

                if(\$('.{$this->humpColumnNameMap[$item]}:checked').length === 0) {
                    layer.msg("请选择{$this->json['columnText'][$item]}",{timeout:1500});
                    return false;
                }
                
EOF;
                    case Element::SELECT:
                        if(!empty($this->json['type'][$item]['option']) && $this->json['type'][$item]['option'] === 'multiple'){
                            return <<<EOF

                if(\$('.{$this->humpColumnNameMap[$item]} option:selected').length === 0) {
                    layer.msg("请选择{$this->json['columnText'][$item]}",{timeout:1500});
                    return false;
                }
                
EOF;
                        }else{
                            return <<<EOF

                if({$this->humpColumnNameMap[$item]} === ""){
                    layer.msg("请选择{$this->json['columnText'][$item]}",{timeout:1500},function(){
                        \$('.{$this->humpColumnNameMap[$item]}').focus();
                    });
                    return false;
                }
                
EOF;
                        }

                }
            }
        }
        return <<<EOF

                if({$this->humpColumnNameMap[$item]} === ""){
                    layer.msg("请输入{$this->json['columnText'][$item]}",{timeout:1500},function(){
                        \$('.{$this->humpColumnNameMap[$item]}').focus();
                    });
                    return false;
                }
                
EOF;
    }

    public function getConditionJs(string $item): string
    {
        if(!empty($this->json['type'][$item])) {
            if (!empty($this->json['type'][$item]['type'])) {
                switch($this->json['type'][$item]['element']){
                    case Element::RADIO:
                        return "                let {$this->humpColumnNameMap[$item]} = \$('.{$this->humpColumnNameMap[$item]}:checked').val();" . PHP_EOL;
                    case Element::CHECKBOX:
                        return "                let {$this->humpColumnNameMap[$item]} = \$('.{$this->humpColumnNameMap[$item]}:checked').map(function(){ return $(this).val(); }).get();" . PHP_EOL;
                    case Element::SELECT:
                        if(!empty($this->json['type'][$item]['option']) && $this->json['type'][$item]['option'] === 'multiple'){
                            return "                let {$this->humpColumnNameMap[$item]} = \$('.{$this->humpColumnNameMap[$item]} option:selected').map(function(){ return $(this).val(); }).get();" . PHP_EOL;
                        }
                }
            }
        }
        return "                let {$this->humpColumnNameMap[$item]} = \$('.{$this->humpColumnNameMap[$item]}').val().trim();" . PHP_EOL;
    }

    public function getExportJs($searchColumn, $paramColumn): string
    {
        if ($this->export) {
            return <<<EOF
            \$('#export').click(function(){
{$searchColumn}
                Tools.ajax({
                    url: "{{ path(constant_route('{$this->exportRouteConst}')) }}",
                    data: {$paramColumn},
                    success: function(res){
                        window.location.href = res.url;
                    }
                });
            });
EOF;
        }else{
            return "";
        }
    }

    public function getImportJs(): string
    {
        if ($this->import) {
            return <<<EOF
            \$('#import').click(function(){
                \$('#file').click();
            });
            \$('#file').change(function(){
                let data = new FormData();
                data.append('file',this.files[0]);
                Tools.ajax({
                    processData:false,
                    contentType:false,
                    url: "{{ path(constant_route('{$this->importRouteConst}')) }}",
                    data: data,
                    beforeSend:function(){
                        layer.msg('上传中...',{anim:-1,time:999999000});
                    },
                    success: function (data) {
                        layer.closeAll();
                        layer.msg(data.msg, {anim:-1, time:1500}, function(){
                            \$('#data-list').reload();
                        });
                    },
                    error:function(){
                       layer.closeAll();
                       layer.msg("上传失败");
                    }
                });
            });
EOF;
        }else{
            return "";
        }
    }
}