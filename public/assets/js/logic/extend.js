;$.fn.extend({
    loadData: function (fn=()=>{}) {
        let $_this = $(this);
        let url = $(this).data('load-url');
        let param = $(this).data('param');
        if (typeof param === 'undefined') {
            param = {};
        }
        if (typeof param['page'] === 'undefined') {
            param['page'] = 1;
        }
        if (typeof param['limit'] === 'undefined') {
            param['limit'] = 10;
        }
        $_this.load(url, param, fn);
    },
    reload: function (fn=()=>{}) {
        let url = $(this).data('load-url');
        let param = $(this).data('param');
        $(this).load(url, param, fn);
    },
    ossUpload: function(fn=()=>{}){
        $(this).on('filebatchselected', function (event, files) {
            // 上传文件数量
            let fileNum = 0;
            // 已经上传文件数量
            let uploadedFileNum = 0;
            $.each(files,function(){
                fileNum++;
            });
            Tools.ossUpload(this, files, function(ossUrl, originName, id, ossThumbUrl=""){
                // 每个文件上传之后回调函数
                uploadedFileNum++;
                if (typeof fn === 'function') {
                    fn.call(null, ossUrl, originName, id, ossThumbUrl, fileNum === uploadedFileNum);
                }
            });
        });
    }
});