<?php

namespace App\Controller;

use App\Lib\Tool\OssTool;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class OssController extends AbstractController
{
    public function stsTokenRefresh(): Response
    {
        $ossTokenConfig = OssTool::getDefaultOssSTSToken();
        return $this->json($ossTokenConfig);
    }
}