<?php

namespace App\Lib\Constant;

class Oss
{
    /** oss存储配置 */
    const OSS_ACCESS_KEY = "LTAI5tGqoVWjSWx3Q6C3ntL1";
    const OSS_ACCESS_SECRET = "U4lacLdh3pUyCtkrVgZ5PIKRbPiGGm";
    const OSS_BUCKET = "jinxueguwen";
    const OSS_ENDPOINT = "oss-cn-beijing.aliyuncs.com";
    const OSS_CDN_DOMAIN = "";
    const OSS_SSL = "true";
    const OSS_IS_CNAME = "false";
    const OSS_DEBUG = "false";
    const OSS_REGION_ID = "oss-cn-beijing";
    const DEFAULT_URL_PREFIX = "https://jinxueguwen.oss-cn-beijing.aliyuncs.com/";

    /** sts配置 */
    const STS_ACCESS_KEY = "LTAI5tLb6egdZ8TGPjGfpMko";
    const STS_ACCESS_SECRET = "s8e9JK55f9xgV3yuHCKGKiyXwr32Ur";
    const STS_REGION_ID = "oss-cn-beijing";
    const STS_ENDPOINT = "sts.cn-beijing.aliyuncs.com";
    const STS_ARM = "acs:ram::1734853687104348:role/jinxueguwen";
    /** @var string signurl 过期时间 60 * 60 * 24 */
    const STS_TIMEOUT = "86400";
}