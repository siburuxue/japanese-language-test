# oss 客户端分片上传文件
## 这个例子使用symfony框架，如果换做其他框架，`$_this.ossTokenRoute = Routing.generate('oss-sts-token-refresh');` 这里的代码要做修改。
### symfony官网：https://symfony.com/doc/current/index.html
#### input 属性
- 控件属性要同时有id和name
- data-upload: 已经上传文件
- data-uploading: 正在上传文件
- data-oss-upload: 是否用ali-oss上传文件 
  - true: 自动上传, 并且该控件与其他[data-oss-upload=true]的控件使用统一回到函数: `logic.js:36`
  - 如果未指定属性，单独设置上传后的回调函数: `extend.js`
- data-file-input="true" 控件初始化
- data-id="19": 父表ID/外键ID 与 data-url-prefix 共同构成 oss-url 前缀
- data-url-prefix: oss-url前缀 文件类型「video/audio/image...」（如果是video类型，会截取视频第一帧作为视频封面）
- 文件上传成功后 oss-url: https://[bucket-name].oss-cn-beijing.aliyuncs.com/video/19/20230831130034_OwGHo20VGk.mp4
- 封面 oss-url：https://[bucket-name].oss-cn-beijing.aliyuncs.com/thumb/19/20230831130035_qJl9FaUVXc.jpg
- 每次上传成功后都会重新生成隐藏变量存储多个文件上传结果(origin_name为视频文件原文件名，尽量简洁，不要有特殊符号影响保存/查询)
```html
<input id="content" name="content" type="file" required multiple data-upload="{}" data-uploading="{}" data-oss-upload="true" data-file-input="true" data-id="19" data-url-prefix="video">
<!-- 文件上传完成之后自动生成 -->
<input type="hidden" id="hid_oss_url_content" name="hid_oss_url_content" value="">
<input type="hidden" id="hid_origin_name_content" name="hid_origin_name_content" value="">
<input type="hidden" id="hid_thumb_url_content" name="hid_thumb_url_content" value="">
```
#### 相关代码
##### 准备工作：
- https://symfony.com/doc/current/frontend/encore/installation.html
- https://jquery.com/download/
- https://getbootstrap.com/docs/5.3/getting-started/download/
- https://github.com/kartik-v/bootstrap-fileinput/releases
```shell
composer require alibabacloud/sdk
composer require jacobcyl/ali-oss-storage
composer require friendsofsymfony/jsrouting-bundle
composer require symfony/webpack-encore-bundle
npm install
npm install @symfony/webpack-encore --save
npm install @symfony/stimulus-bridge --save
npm install @babel/plugin-proposal-class-properties
npm install regenerator-runtime
npm install core-js
npm install @hotwired/stimulus --save
npm install webpack-notifier@^1.15.0 --save-dev
npm install ali-oss --save
php bin/console assets:install --symlink public
```
##### assets/controllers/hello_controller.js
```js
import { Controller } from '@hotwired/stimulus';

/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="hello" attribute will cause
 * this controller to be executed. The name "hello" comes from the filename:
 * hello_controller.js -> "hello"
 *
 * Delete this file or adapt it for your use!
 */
export default class extends Controller {
    connect() {
        this.element.textContent = 'Hello Stimulus! Edit me in assets/controllers/hello_controller.js';
    }
}

```
##### assets/app.js
```js
...
global.OSS  =  require('ali-oss');
```
```shell
npm run dev
```
##### config/routes.yaml
```yaml
...
oss-function:
    path: /oss/index
    controller: App\Controller\OssController::index
oss-sts-token-refresh:
    path: /oss/sts/token/refresh
    controller: App\Controller\OssController::stsTokenRefresh
```
##### config/packages/framework.yaml
```yaml
...
# js routing
fos_js_routing:
    routes_to_expose: [ oss-sts-token-refresh ]
```
##### src/Controller/OssController.php
```php
<?php
namespace App\Controller;

use App\Lib\Tool\OssTool;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class OssController extends AbstractController
{
    public function index():Response
    {
        return $this->render('oss/index.html.twig');
    }

    public function stsTokenRefresh(): Response
    {
        $ossTokenConfig = OssTool::getDefaultOssSTSToken();
        return $this->json($ossTokenConfig);
    }
}
```
##### src/Lib/Tool/OssTool.php
```php
<?php
namespace App\Lib\Tool;

use AlibabaCloud\Client\AlibabaCloud;
use App\Lib\Constant\Oss;
use OSS\Core\OssException;
use OSS\OssClient;

class OssTool
{
    public static function getDefaultOssSTSToken(): array
    {
        $data = self::getOSSToken([
            'stsAccessKey' => Oss::STS_ACCESS_KEY,
            'stsAccessSecret' => Oss::STS_ACCESS_SECRET,
            'stsRegionId' => Oss::STS_REGION_ID,
            'stsArm' => Oss::STS_ARM,
        ]);
        return [
            "region" => Oss::STS_REGION_ID,
            "access-key" => $data['Credentials']['AccessKeyId'],
            "access-secret" => $data['Credentials']['AccessKeySecret'],
            "sts-token" => $data['Credentials']['SecurityToken'],
            "bucket" => Oss::OSS_BUCKET,
            "timeout" => Oss::STS_TIMEOUT,
            "end-point" => Oss::OSS_ENDPOINT,
        ];
    }

    public static function getOSSToken(array $config=[]): array
    {
        try {
            AlibabaCloud::accessKeyClient($config['stsAccessKey'], $config['stsAccessSecret'])
                ->regionId($config['stsRegionId'])
                ->asDefaultClient();
            $result = AlibabaCloud::rpc()
                ->product('Sts')
                ->scheme('https')
                ->version('2015-04-01')
                ->action('AssumeRole')
                ->method('POST')
                ->host("sts.aliyuncs.com")
                ->options([
                    'query' => [
                        'RegionId' => $config['stsRegionId'],
                        'RoleArn' => $config['stsArm'],
                        'RoleSessionName' => "jxgw",
                    ],
                ])
                ->request();
            return $result->toArray();
        } catch (\Exception $e) {
            echo "<pre>";
            echo $e->getMessage() . PHP_EOL;
            echo $e->getErrorCode() . PHP_EOL;
            echo $e->getRequestId() . PHP_EOL;
            echo $e->getErrorMessage() . PHP_EOL;
        }
    }

    public static function getSignUrl($url, $data)
    {
        if($url == "") return "";
        $prefix = "https://" . $data['bucket'] . "." . $data['end-point'] . "/";
        $object = str_replace($prefix, "", $url);
        try {
            $ossClient = new OssClient($data['access-key'], $data['access-secret'], $data['end-point'], false,
                $data['sts-token']);
            // 生成签名URL。
            $signUrl = $ossClient->signUrl($data['bucket'], $object, $data['timeout']);
            return str_replace("http://", "https://", $signUrl);
        } catch (OssException $e) {
            printf(__FUNCTION__ . ": FAILED\n");
            printf($e->getMessage() . "\n");
            return "";
        }
    }

}
```
##### src/Lib/Constant/Oss.php
```php
<?php
namespace App\Lib\Constant;

class Oss
{
    /** oss存储配置 */
    const OSS_ACCESS_KEY = "";
    const OSS_ACCESS_SECRET = "";
    const OSS_BUCKET = "";
    const OSS_ENDPOINT = "";
    const OSS_CDN_DOMAIN = "";
    const OSS_SSL = "true";
    const OSS_IS_CNAME = "false";
    const OSS_DEBUG = "false";
    const OSS_REGION_ID = "oss-cn-beijing";
    const DEFAULT_URL_PREFIX = "";

    /** sts配置 */
    const STS_ACCESS_KEY = "";
    const STS_ACCESS_SECRET = "";
    const STS_REGION_ID = "oss-cn-beijing";
    const STS_ENDPOINT = "sts.cn-beijing.aliyuncs.com";
    const STS_ARM = "";
    /** @var string signurl 过期时间 60 * 60 * 24 */
    const STS_TIMEOUT = "86400";
}
```
##### templates/oss/index.html.twig
```html
<link href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/plugins/bootstrap-fileinput/css/fileinput.min.css') }}" media="all" rel="stylesheet"
      type="text/css"/>
<div id="main-wrapper">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-white">
                <div class="panel-heading">
                    <form class="form-horizontal">
                        <div class="form-group">
                            <div class="col-sm-1"></div>
                            <div class="col-sm-5">
                                <input type="file" id="file" name="file" multiple data-upload="{}" data-uploading="{}"
                                       data-file-input="true" data-id="19"
                                       data-url-prefix="video">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-fileinput/js/plugins/piexif.min.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-fileinput/js/plugins/sortable.min.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-fileinput/js/fileinput.min.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-fileinput/js/locales/zh.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-fileinput/themes/gly/theme.min.js') }}"></script>

<!-- ali-oss -->
{{ encore_entry_script_tags('app') }}

<script src="{{ asset('js/extend.js') }}"></script>
<script src="{{ asset('js/logic.js') }}"></script>
<script src="{{ asset('js/tool.js') }}"></script>

<script src="{{ asset('bundles/fosjsrouting/js/router.min.js') }}"></script>
<script src="{{ path('fos_js_routing_js', { callback: 'fos.Router.setData' }) }}"></script>
<script>
    $('#file').ossUpload(function(ossUrl, originName, id, ossThumbUrl, complete){
        console.log(ossUrl)
        console.log(originName)
        console.log(id)
        console.log(ossThumbUrl)
        console.log(complete)
    })
</script>
```
##### public/js/extend.js
```js
;$.fn.extend({
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
```
##### public/js/logic.js
```js
;$(function(){
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
```
##### public/js/tool.js
```js
;let Tools = {
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
    }
}
```
浏览器访问地址：http[s]://localhost[域名]/oss/index