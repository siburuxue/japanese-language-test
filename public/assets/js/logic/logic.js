;$(function () {
    // 头像颜色变化
    (function () {
        function getNumber() {
            return Math.floor(Math.random() * 256);
        }

        let r = getNumber();
        let g = getNumber();
        let b = getNumber();
        $('#user-avatar').css('background', "rgb(" + r + "," + g + "," + b + ")")
            .css('color', "rgb(" + (255 - r) + "," + (255 - g) + "," + (255 - b) + ")")
            .css('display', 'inline-block');
    })();

    // 自动加载数据
    let autoload = $('[data-auto-load=true]');
    if (autoload.length > 0) {
        autoload.each(function () {
            $(this).loadData();
        })
    }

    // 修改状态
    $(document).on('click', '.table-change-status', function () {
        let url = $(this).data('url');
        let tableBox = $(this).parents('.table-box');
        Tools.ajax({
            url,
            success:function (res) {
                layer.msg(res.msg, {time: 1500}, function () {
                    if (res.status) {
                        tableBox.reload();
                    }
                });
            },
            error:function (res, textStatus, errorThrown) {
                layer.msg(res.responseJSON.msg, {time: 1000});
            }
        })
    });
    // 带确认框修改状态
    $(document).on('click', '.table-confirm-change-status', function () {
        let url = $(this).data('url');
        let tableBox = $(this).parents('.table-box');
        layer.confirm("确定删除？", function () {
            Tools.ajax({
                url,
                success:function (res) {
                    layer.msg(res.msg, {time: 1500}, function () {
                        if (res.status) {
                            tableBox.reload();
                        }
                    });
                },
                error:function (res, textStatus, errorThrown) {
                    layer.msg(res.responseJSON.msg, {time: 1000});
                }
            })
        });
    });
    let $_file_input = $('input[data-file-input=true]');
    if ($_file_input.length > 0) {
        /**  @type {string} file-input 控件版本 */
        $.fn.fileinputBsVersion = '3.4.1';
        // file-input 控件自动初始化
        $_file_input.each(function () {
            $(this).fileinput({
                theme: 'gly',
                uploadUrl: '#',
                language: 'zh',
                overwriteInitial: true,
                initialPreviewAsData: true,
                showRemove: false,
                showCancel: false,
                showUpload: false,
                dropZoneEnabled: false,
                fileActionSettings: {
                    "showRemove": false,
                    "showDrag": true,
                    "showUpload": false,
                    "showZoom": false
                }
            });
        });

        // 绑定事件
        $_file_input.each(function () {
            // file-input 选择图片后自动上传到ali-oss
            if ($(this).attr('data-oss-upload') === 'true') {
                $(this).on('filebatchselected', function (event, files) {
                    let fileNum = 0, uploadedFileNum = 0;
                    $.each(files, function () {
                        fileNum++;
                    });
                    Tools.ossUpload(this, files, function (ossUrl, originName, id, ossThumbUrl = "") {
                        uploadedFileNum++;
                        // 每个文件上传之后业务逻辑
                        if (fileNum === uploadedFileNum) {
                            // 所有文件上传成功后业务逻辑
                        }
                    });
                });
            }
            // 当预览视图被清空后
            $(this).on('filecleared', function (event) {
                $(this).data('upload', {}).data('uploading', {}).data('thumb', {});
                $('#hid_oss_url_' + this.id).remove();
                $('#hid_origin_name_' + this.id).remove();
                $('#hid_thumb_url_' + this.id).remove();
            });
        });
    }
});