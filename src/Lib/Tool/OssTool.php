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