;$(function(){
    $(document).on('click','.dataTables_paginate .paginate_button',function(){
        if($(this).hasClass('current')) return false;
        let tableBox = $(this).parents('.dataTables_paginate').parent();
        let param = tableBox.data('param');
        let current = $('.dataTables_paginate .current').data('page');
        let key = $(this).data('page');
        let total = $('.dataTables_paginate .last').data('total');
        switch(key){
            case "prev":
                if(current === 1) return false;
                param.page = current - 1;
                tableBox.data('param',param);
                tableBox.loadData();
                break;
            case "next":
                if(total === current) return false;
                param.page = current + 1;
                tableBox.data('param',param);
                tableBox.loadData();
                break;
            case "go":
                let num = parseInt($(this).prev().val());
                if(isNaN(num)) return false;
                if(num === current) return false;
                if(num < 1 || num > total) return false;
                param.page = num;
                tableBox.data('param',param);
                tableBox.loadData();
                break;
            default:
                param.page = key;
                tableBox.data('param',param);
                tableBox.loadData();
                break;
        }
    });
    $(document).on('change','.dataTables_paginate .page-count',function(){
        let limit = $(this).val();
        let tableBox = $(this).parents('.dataTables_paginate').parent();
        let param = tableBox.data('param');
        param.page = 1;
        param.limit = limit;
        tableBox.data('param',param);
        tableBox.loadData();
    });
});