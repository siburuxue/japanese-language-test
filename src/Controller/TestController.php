<?php

namespace App\Controller;

use App\Lib\Tool\OssTool;
use App\Service\CourseInfoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TestController extends AbstractController
{
    public function stsTokenRefresh(): Response
    {
        $ossTokenConfig = OssTool::getDefaultOssSTSToken();
        return $this->json($ossTokenConfig);
    }

    public function index(CourseInfoService $courseInfoService): Response
    {
        $list = $courseInfoService->getCourseInfoList(14);
        return $this->render("admin/test/index.html.twig", ['item' => $list[0]]);
    }

    public function meituan(Request $request)
    {
        // https://m.dianping.com/merchant/im/user/search?pageNum=1&pageSize=10&fromLastContact=2023-04-05&toLastContact=2023-04-12
        // _lxsdk_cuid=187745d1a59c8-0de23e3852587e-1d525634-1fa400-187745d1a5ac8; _lxsdk=187745d1a59c8-0de23e3852587e-1d525634-1fa400-187745d1a5ac8; _hc.v=0cf11d93-5f05-b06c-4f41-f0af3ae4382d.1681284472; logan_session_token=njls1ur0xekr9gw82f8f; _lxsdk_s=187754d70d1-60a-811-c29%7Cuser-id%7C80
        $url = $request->request->get("url","");
//        $cookie = $request->request->get("cookie", "_lxsdk_cuid=187745d1a59c8-0de23e3852587e-1d525634-1fa400-187745d1a5ac8; _lxsdk=187745d1a59c8-0de23e3852587e-1d525634-1fa400-187745d1a5ac8; _hc.v=0cf11d93-5f05-b06c-4f41-f0af3ae4382d.1681284472; logan_session_token=gzqulota8tsq2352k20t; _lxsdk_s=187754d70d1-60a-811-c29%7Cuser-id%7C72");
        $cookie = "_lxsdk_cuid=187745d1a59c8-0de23e3852587e-1d525634-1fa400-187745d1a5ac8; _lxsdk=187745d1a59c8-0de23e3852587e-1d525634-1fa400-187745d1a5ac8; _hc.v=0cf11d93-5f05-b06c-4f41-f0af3ae4382d.1681284472; edper=cZXsKxj4VUKKt-RGg8ySpw8PpUm4HIubhzxA4l-7pM1FhGHr5kc_AsPyxyKrMFmYoJxcwG8G21WlbNjOIrgxdA; _lxsdk_s=187745d1a5b-e3f-d57-070%7C%7C14";
        $header = [
            "Cookie: " . $cookie,
            "Connection: keep-alive",
            "Accept: */*",
            "Cache-Control: no-cache",
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        $output = json_decode($output, 1);
        return $this->json($output);
    }
}