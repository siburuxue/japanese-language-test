# symfony 后台模板项目

## 初始化：
```shell
# init.sh
composer install
php bin/console assets:install --symlink public
npm install
npm run dev
```

## 生成CURD：
```shell
# src/Command/CreateCurdCommand.php
# 新建表之后执行 ./sync.sh 同步数据库，生成Entity(getter/setter), 与对应的Repository.php文件
# --title=测试 页面功能名字
# --table=test_dict 为指定表创建CURD
# --service=TestDict 生成/指定Service文件
# --controller=TestDict 生成/指定Controller文件
# --extra=test_dict.json 扩展属性 比如每一列对应的中文描述，每个列对应的html控件 
# --group="" 父菜单名字
# --icon="" 父菜单图标
# --readonly 只生成查询
# --export 添加导出功能
# test_dict.json 结构：
#   table: 出现在列表查询中的列，同样也会作为查询条件出现在列表页
#   columnText: 在数据库中，除去id, is_del, create_user, create_time, update_user, update_time之外的列，与其对应的文言
#   type: 指定某个列为指定的控件类型，默认text，目前支持date, datetime, radio, select(multiple), checkbox四种类型
#   source: 指多选的数据来源，目前只支持从dict表取数据 type: dict表中的type字段
#   在数据来源这里，从原则上可以指定多处来源，比如常量数组，但是为了规范开发过程，所以去掉其他情况，只支持dict，如果需要添加多个选项，请更新数据库
{
    "table": {
        "type": "类型",
        "d_key": "键",
        "d_value": "值",
        "u_key": "唯一标识",
        "remark": "备注"
    },
    "columnText": {
        "type": "类型",
        "d_key": "键",
        "d_key1": "键1",
        "d_key2": "键2",
        "d_value": "值",
        "u_key": "唯一标识",
        "remark": "备注"
    },
    "type": {
        "d_value": "date",
        "u_key": "datetime",
        "d_key1": {
            "source": "dict",
            "type": "course_type",
            "element": "radio"
        },
        "d_key2": {
            "source": "dict",
            "type": "course_type",
            "element": "checkbox"
        },
        "d_key3": {
            "source": "dict",
            "type": "course_type",
            "element": "checkbox"
        },
        "d_key4": {
            "source": "dict",
            "type": "course_type",
            "element": "select",
            "option": "multiple"
        },
        "d_key": {
            "source": "dict",
            "type": "course_type",
            "element": "select"
        }
    }
}
php bin/console create:curd --title=测试 --table=test_dict --service=TestDict --controller=TestDict --extra=test_dict.json --group="" --icon=""
php bin/console create:curd --title=测试 --table=test_dict --service=TestDict --controller=TestDict --extra=test_dict.json --group="" --icon="" --readonly
php bin/console create:curd --title=测试 --table=test_dict --service=TestDict --controller=TestDict --extra=test_dict.json --group="" --icon="" --readonly --export
```

## 手动添加路由：
```shell
# src/Command/AddRouteCommand.php
# test-menu 路由名字
# 测试菜单 功能名字
# /test/menu 路由地址
# App\\Controller\\TestController::menu 路由对应的文件名和方法名
# --group="" 父菜单名字
# --icon="" 父菜单图标
# --menu=1 是否是左侧菜单中的页面 0否1是 如果是0 代表页面中的某个按钮功能
# --parent=0 父节点ID 0表示为左侧菜单树中的页面 不为0表是某个页面的按钮功能 
# --default=0 是否为默认权限 0否1是 即新建用户没有配置任何权限登陆系统可以使用的功能，比如首页，加载左侧树菜单
php bin/console add:route test-menu 测试菜单 /test/menu App\\Controller\\TestController::menu --group="" --icon="" --menu=1 --parent=0 --default=0
```

## 同步配置文件中的路由与数据库中的路由：
```shell
# src/Command/SyncRouteCommand.php
php bin/console sync:route
```

## 去掉表前缀:
```shell
# src/Command/SyncTableWithoutPrefixCommand.php
# 为文件添加表前缀以达到表前缀的目的
php bin/console sync:table:without:prefix
# 强制修改文件名 EqDict.php => Dict.php
php bin/console sync:table:without:prefix --force
# 指定表名忽略表前缀
php bin/console sync:table:without:prefix --table=eq_dict
```

## 生成Service文件：
```shell
# src/Command/MakeServiceCommand.php
# Test 生成TestService文件
# --entities="Dict User" 需要依赖注入的Entity App\Entity\Dict, App\Entity\User
# --di="Logger Mailer" 需要依赖注入的类名，不同namespace下提供选项
php bin/console make:service Test --entities="Dict User" --di="Logger Mailer"
php bin/console make:service Test -en="Dict User" -d="Logger Mailer"
```

## 同步数据库信息：
```shell
# sync.sh
php bin/console doctrine:mapping:import  
```

## ali-oss 配置：
详见：[oss-upload.md](https://gitee.com/siburuxue/symfony7-example/blob/master/oss-upload.md)
### 常量文件：
```php
# 详见 src/Lib/Constant/Oss.php
```
### js上传文件：
```
public/assets/js/logic/extend.js
public/assets/js/logic/logic.js
public/assets/js/tool.js
```
### html标签
- 控件属性要同时有id和name
- data-upload: 已经上传文件
- data-uploading: 正在上传文件
- data-oss-upload: 是否用ali-oss上传文件 
  - true: 自动上传, 并且该控件与其他[data-oss-upload=true]的控件使用统一回到函数: `logic.js:83`
  - 如果未指定属性，单独设置上传后的回调函数: `extend.js:22`
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