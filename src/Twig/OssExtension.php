<?php

namespace App\Twig;

use App\Lib\Tool\OssTool;

class OssExtension extends \Twig\Extension\AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new \Twig\TwigFunction("get_sign_url",[$this,'getSignUrl'])
        ];
    }

    public function getSignUrl(string $url):string
    {
        $ossTokenConfig = OssTool::getDefaultOssSTSToken();
        return OssTool::getSignUrl($url, $ossTokenConfig);
    }

    public function getName(): string
    {
        return 'app_extension';
    }
}