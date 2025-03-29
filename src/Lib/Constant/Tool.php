<?php

namespace App\Lib\Constant;

class Tool
{

    /** @var string 返回json结构 */
    const TYPE_JSON = 'json';

    /** @var string 返回索引数组 */
    const TYPE_ARRAY = 'array';

    /** @var string 返回关联数组 */
    const TYPE_ASSOC = 'assoc';

    /** @var string 数据文言 */
    const DATA_TEXT = "text";

    /** @var string 数据ID */
    const DATA_ID = "id";

    /** @var string 网站名字 */
    const PROJECT_NAME = "高中日语";

    const CSRF_NAME = 'csrf-token';

    const CSRF_ERROR = 'csrf 验证失败';

    public static function getCSRFHeaderName(): string
    {
        return ucwords(self::CSRF_NAME, '-');
    }
}