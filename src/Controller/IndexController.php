<?php

namespace App\Controller;

use App\Lib\Constant\Tool;
use App\Lib\Tool\ProjectDependenciesTool;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class IndexController extends CommonController
{
    public function index(): Response
    {
        $composer = ProjectDependenciesTool::getJsonConfig("composer.json",'require');
        $package = ProjectDependenciesTool::getJsonConfig("package.json",'dependencies');
        $php = ProjectDependenciesTool::getPHPConfig();
        return $this->render('admin/home/index.html.twig',[
            'composer' => $composer,
            'package' => $package,
            'php' => $php,
        ]);
    }

    public function getReadme(Request $request):Response
    {
        if (!$this->csrfValid()) {
            return $this->error(Tool::CSRF_ERROR);
        }
        $root = $request->request->get('root');
        $dir = $request->request->get('dir');
        $string = ProjectDependenciesTool::getReadme($root,$dir);
        return new Response($string);
    }
}