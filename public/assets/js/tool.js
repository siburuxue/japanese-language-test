let Tools = {
    /**
     * 获取当前日期 YYYYMMDDhis
     * @returns {string}
     * @constructor
     */
    CurrentTime: function (format = 'YmdHis',time=0) {
        let now;

        if (time > 0) {
            now = new Date(time);
        } else {
            now = new Date();
        }

        let year = now.getFullYear();       //年
        let month = now.getMonth() + 1;     //月
        let day = now.getDate();            //日

        let h = now.getHours();            //时
        let i = now.getMinutes();          //分
        let s = now.getSeconds();          //秒

        if (month < 10) month = "0" + month;
        if (day < 10) day = "0" + day;
        if (h < 10) h = "0" + h;
        if (i < 10) i = '0' + i;
        if (s < 10) s = '0' + s;

        format = format.replace("Y", year);
        format = format.replace("m", month);
        format = format.replace("d", day);
        format = format.replace("H", h);
        format = format.replace("i", i);
        format = format.replace("s", s);

        return format;
    },
    formatTimeFromNumber:(currentTime) => {
        currentTime = parseInt(currentTime);
        let second = currentTime % 60;
        if(second < 10){
            second = "0" + second;
        }
        let minute = (currentTime - second) / 60;
        if(minute >= 60){
            let tmp = minute % 60;
            let hour = (minute - tmp) / 60;
            if(tmp < 10){
                tmp = "0" + tmp;
            }
            minute = tmp;
            return hour + ":" + minute + ":" + second;
        }
        return minute + ':' + second;
    },
    /**
     * 获取随机字符串
     * @param n 指定字符串长度
     * @returns {string}
     * @constructor
     */
    RandomString: function (n) {
        let str = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        let arr = str.split("");
        return Array.from({length: n}, (v, i) => i).map(function (v, k) {
            return arr[Math.random() * str.length | 0];
        }).join("");
    },
    /**
     * ajax函数
     * @param config
     */
    ajax: function (config) {
        let setting = {
            dataType: 'json',
            type: 'post',
            error: function (err) {
                if(typeof layer !== 'undefined'){
                    if(err.responseJSON && err.responseJSON.msg){
                        layer.msg(err.responseJSON.msg,{time:1500});
                    }else{
                        layer.msg("系统异常",{time:1500});
                    }
                }
                console.dir(err);
            }
        };
        if(typeof config['data'] === 'undefined'){
            setting['type'] = 'get';
        }else{
            setting['type'] = 'post';
        }
        if (!config['url'].includes("#")) {
            config['url'] = config['url'] + "#";
        }
        config = $.extend(setting, config)
        $.ajax(config);
    },
    // oss 上传
    ossUpload: function (fileInput, fileList, fn) {
        new this.ossUploadFunc(fileInput, fileList, fn);
    },
    /**
     * web oss 分片上传
     * @param fileInput
     * @param fileList
     * @param fn [ossUrl, originName, id, ossThumbUrl]
     * @callback-param ossUrl oss-url 完整路径
     * @callback-param originName 原始文件名
     * @callback-param id 子表数据外键
     * @callback-param ossThumbUrl 视频封面 oss-url 完整路径
     */
    ossUploadFunc: function (fileInput, fileList, fn) {
        let $_this = this;
        $_this.id = $(fileInput).data('id');
        $_this.urlPrefix = $(fileInput).data('url-prefix');
        $_this.fileObjectName = $(fileInput).attr('name').replace('[]', "");
        // file-input box
        $_this.$_file_input_box = $(fileInput).parents('.file-input');

        // 已经上传文件
        $_this.uploaded = $(fileInput).data('upload') || {};
        //正在上传中的文件
        $_this.uploading = $(fileInput).data('uploading') || {};
        // 视频封面
        $_this.thumb = $(fileInput).data('thumb') || {};
        // 获取 sts-token Url
        $_this.ossTokenRoute = Routing.generate('oss-sts-token-refresh');
        $_this.ossConfig = {};
        Tools.ajax({
            url: $_this.ossTokenRoute,
            async: false,
            success: function (res) {
                $_this.ossConfig = res
            }
        })
        $_this.client = new OSS({
            region: $_this.ossConfig['region'],
            accessKeyId: $_this.ossConfig['access-key'],
            accessKeySecret: $_this.ossConfig['access-secret'],
            stsToken: $_this.ossConfig['sts-token'],
            bucket: $_this.ossConfig['bucket'],
            refreshSTSToken: () => {
                let refreshTokenData = {};
                Tools.ajax({
                    url: $_this.ossTokenRoute,
                    async: false,
                    success: function (res) {
                        refreshTokenData = res
                    }
                })
                return {
                    accessKeyId: refreshTokenData['access-key'],
                    accessKeySecret: refreshTokenData['access-secret'],
                    stsToken: refreshTokenData['sts-token'],
                };
            }
        });

        // fileList与uploaded,uploading做差集 没有上传的文件才重新上传
        $_this.tmp = {};
        // 需要上传的文件数量
        $_this.fileNeedUpload = 0;
        $.each(fileList, function (k, v) {
            if (typeof $_this.uploaded[k] === 'undefined' && typeof $_this.uploading[k] === 'undefined') {
                $_this.fileNeedUpload++;
                $_this.tmp[k] = v;
            }
        });
        // 需要上传的文件，已经上传完成的数量
        $_this.fileUploadDone = 0;
        $.each($_this.tmp, async function (fileName, fileInfo) {
            let size = fileInfo.size;
            let originName = encodeURIComponent(fileInfo.file.name);
            let nameArray = originName.split(".");
            let ext = nameArray.pop();
            let filename = $_this.urlPrefix + "/" + $_this.id + "/" + Tools.CurrentTime() + "_" + Tools.RandomString(10) + "." + ext;
            //上传状态
            let $_file_preview_frame = $_this.$_file_input_box.find('.file-preview-frame[data-fileid="' + fileName + '"]');
            let $_file_upload_status = $_file_preview_frame.find('file-upload-status')
            if ($_file_upload_status.length === 0) {
                $_file_preview_frame.find('.file-size-info').after(function () {
                    return '<div class="file-upload-status"><samp>数据准备中...</samp></div>';
                });
            }
            // 生成缩略图
            if ($_this.urlPrefix === 'video') {
                Tools.thumb(fileInfo.file, async function (base64, thumbFile) {
                    try {
                        let originName = encodeURIComponent(thumbFile.name);
                        let nameArray = originName.split(".");
                        let ext = nameArray.pop();
                        let thumbFilePath = "thumb/" + $_this.id + "/" + Tools.CurrentTime() + "_" + Tools.RandomString(10) + "." + ext;
                        const result = await $_this.client.put(thumbFilePath, thumbFile);
                        console.log("上传缩略图：", result);
                        $_this.thumb[fileName] = "https://" + $_this.ossConfig['bucket'] + '.' + $_this.ossConfig['end-point'] + "/" + thumbFilePath
                    } catch (e) {
                        console.log("上传缩略图回调函数错误：", e);
                    }
                })
            } else {
                $_this.thumb[fileName] = "";
            }
            // oss-upload 配置
            const headers = {
                // 指定该Object被下载时的网页缓存行为。
                "Cache-Control": "no-cache",
                // 指定该Object被下载时的名称。
                "Content-Disposition": originName,
                // 指定该Object被下载时的内容编码格式。
                "Content-Encoding": "utf-8",
                // 指定过期时间，单位为毫秒。
                Expires: "1000",
                // 指定Object的存储类型。
                // "x-oss-storage-class": "Standard",
                // 指定Object标签，可同时设置多个标签。
                // "x-oss-tagging": "Tag1=1&Tag2=2",
                // 指定初始化分片上传时是否覆盖同名Object。此处设置为true，表示禁止覆盖同名Object。
                "x-oss-forbid-overwrite": true,
            };
            const options = {
                // 设置并发上传的分片数量。
                parallel: 10,
                // 设置分片大小。默认值为1 MB，最小值为100 KB。
                partSize: 1024 * 1024,
                // headers,
                // 自定义元数据，通过HeadObject接口可以获取Object的元数据。
                meta: {
                    length: size,
                    originName: fileInfo.file.name,
                    type: fileInfo.file.type,
                    fileObjectName: $_this.fileObjectName
                },
                mime: "text/plain",
            };

            try {
                const res = await $_this.client.multipartUpload(filename, fileInfo.file, {
                    options,
                    headers,
                    // 获取分片上传进度、断点和返回值。
                    progress: (p, cpt, res) => {
                        // uploading: #c9c9c9
                        // upload-done: #25DEBD
                        if (p === 1) {
                            // 上传完成
                            $_file_preview_frame.css("background", "#25DEBD");
                            let fullOssUrl = "https://" + $_this.ossConfig['bucket'] + '.' + $_this.ossConfig['end-point'] + "/" + filename
                            $_this.uploaded[fileName] = fullOssUrl;
                            $_file_preview_frame.find('.file-upload-status').find('samp').text('上传完成');
                            $_this.fileUploadDone++;
                            // 全部完成
                            if ($_this.fileUploadDone === $_this.fileNeedUpload) {
                                $(fileInput).data('upload', $_this.uploaded);
                                $(fileInput).data('thumb', $_this.thumb);
                                // 保存上传链接到控件中
                                let ossUrlArray = [];
                                let originNameArray = [];
                                let thumbArray = [];
                                $_this.$_file_input_box.find('.file-preview-frame').each(function () {
                                    originNameArray.push($(this).find('.file-caption-info').text());
                                    let key = $(this).data('fileid');
                                    let value = $_this.uploaded[key];
                                    ossUrlArray.push(value);
                                    thumbArray.push($_this.thumb[key]);
                                });
                                let ossUrlId = 'hid_oss_url_' + $_this.fileObjectName;
                                let originNameId = 'hid_origin_name_' + $_this.fileObjectName;
                                let thumbUrlId = 'hid_thumb_url_' + $_this.fileObjectName;
                                $('#' + ossUrlId).remove();
                                $('#' + originNameId).remove();
                                $('#' + thumbUrlId).remove();
                                $(fileInput).parents('form').append("<input type='hidden' id='" + ossUrlId + "' name='" + ossUrlId + "' value='" + ossUrlArray.join() + "'>");
                                $(fileInput).parents('form').append("<input type='hidden' id='" + originNameId + "' name='" + originNameId + "' value='" + originNameArray.join() + "'>");
                                $(fileInput).parents('form').append("<input type='hidden' id='" + thumbUrlId + "' name='" + thumbUrlId + "' value='" + thumbArray.join() + "'>");
                            }
                            delete $_this.uploading[fileName];
                            // 完成回调
                            if (typeof fn === 'function') {
                                try {
                                    fn.call(null, fullOssUrl, fileInfo.file.name, $_this.id, $_this.thumb[fileName]);
                                } catch (e) {
                                    console.log("上传视频回调函数错误：", e)
                                }
                            }
                        } else {
                            // 上传中
                            let uploadPercent = (p * 100).toFixed(2);
                            let backgroundStr = "linear-gradient(to top, #c9c9c9 0% " + uploadPercent + "%, white " + uploadPercent + "% 100%)";
                            $_file_preview_frame.css("background", backgroundStr);
                            $_file_preview_frame.find('.file-upload-status').find('samp').text('上传中(' + uploadPercent + '%)...')
                            $_this.uploading[fileName] = 1;
                            $(fileInput).data('uploading', $_this.uploading);
                        }
                    },
                });
            } catch (err) {
                console.log(err);
            }
        });
    },
    /**
     * 截取视频第一帧
     * @param source 视频源 url链接或者是file对象
     * @param fn
     * @param-fn base64 图片base64值
     * @param-fn file 图片file对象
     * @returns {boolean}
     */
    thumb: function (source, fn) {
        let url = null, dataUrl = null, file = null, mime = "image/jpeg";
        if (typeof source === 'string') {
            url = source;
        } else if (typeof source === 'object') {
            window.URL = window.URL || window.webkitURL;
            url = window.URL.createObjectURL(source);
        } else {
            if (typeof fn === 'function') {
                fn.call(null, null, null);
            } else {
                return false;
            }
        }
        let video = document.createElement('video');
        video.setAttribute('crossorigin', 'Anonymous');
        video.innerHTML = "<source src='" + url + "'>";
        video.setAttribute('preload', 'auto');
        // video.setAttribute('currentTime', "3.0");
        // video.setAttribute('autoplay', 'auto');
        // video.setAttribute('muted', 'true');
        video.addEventListener('loadeddata', function () {
            let canvas = document.createElement('canvas');
            let width = video.clientWidth || video.width || video.videoWidth;
            let height = video.clientHeight || video.height || video.videoHeight;
            canvas.width = width;
            canvas.height = height;
            canvas.getContext('2d').drawImage(video, 0, 0, width, height);
            dataUrl = canvas.toDataURL('image/jpeg')
            // let img = document.createElement('img')
            // img.src = dataUrl;
            // document.querySelector('body').appendChild(img)
            const bytes = window.atob(dataUrl.split(',')[1]);
            const ab = new ArrayBuffer(bytes.length);
            const ia = new Uint8Array(ab);
            for (let i = 0; i < bytes.length; i++) {
                ia[i] = bytes.charCodeAt(i);
            }
            let blob = new Blob([ab], {type: mime});
            let fileName = (new Date()).getTime() + ".jpg";
            file = new File([blob], fileName, {type: mime});
            if (typeof fn === 'function') {
                fn.call(null, dataUrl, file);
            }
        })
    },
    getNumberFromTime(time) {
        let arr = time.split(':');
        let len = arr.length;
        let num = 0;
        arr.forEach((v, k) => {
            num += v * Math.pow(60, len - k - 1);
        })
        return num;
    }
}

