<?php

namespace App\Lib\Tool;

use JetBrains\PhpStorm\ArrayShape;
use ReflectionException;
use ReflectionExtension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;

class ProjectDependenciesTool
{
    public static function getJsonConfig($file, $key): array
    {
        $filePath = dirname(__DIR__, 3) . "/" . $file;
        if (!file_exists($filePath)) {
            return [];
        }
        $content = file_get_contents($filePath);
        $content = json_decode($content, true);
        $content = $content[$key];
        $contentArray = [];
        foreach ($content as $key => $version) {
            if ($key === 'php' || str_starts_with($key, 'ext-')) {
                continue;
            }
            $contentArray[] = [
                'name' => $key,
                'version' => $version
            ];
        }
        return $contentArray;
    }

    /**
     * @throws ReflectionException
     */
    #[ArrayShape(['base' => "array", 'ext' => "array"])]
    public static function getPHPConfig(): array
    {
        $server = Request::createFromGlobals()->server;
        $extensionPath = ini_get("extension_dir");
        $ext = [];
        if(file_exists($extensionPath)){
            $fn = dir($extensionPath);
            while ($f = $fn->read()) {
                if ($f == "." || $f == "..") {
                    continue;
                }
                $f = str_replace('.so', '', $f);
                if($f !== 'opcache'){
                    $reflect = new ReflectionExtension($f);
                    $ext[$f] = $reflect->getVersion();
                }
            }
        }
        return [
            'base' => [
                ['name' => 'php version', 'href' => 'https://www.php.net/ChangeLog-8.php#'. phpversion(), 'val' => phpversion()],
                ['name' => 'symfony version', 'href' => 'https://symfony.com/doc/current/index.html', 'val' => Kernel::VERSION],
                ['name' => 'server', 'href' => '', 'val' => $server->get('SERVER_SOFTWARE')],
                [
                    'name' => 'URL',
                    'href' => '',
                    'val' => $server->get('REQUEST_SCHEME') . "://" . $server->get('SERVER_NAME') . "/admin"
                ],
                ['name' => 'project root', 'href' => '', 'val' => dirname($server->get('DOCUMENT_ROOT'))],
            ],
            'ext' => $ext,
        ];
    }

    public static function getReadme($root, $dir): string
    {
        if (empty($root) || empty($dir)) {
            return "";
        }
        $content = "";
        $filePath1 = dirname(__DIR__, 3) . "/" . $root . "/" . $dir . "/" . "README.md";
        $filePath2 = dirname(__DIR__, 3) . "/" . $root . "/" . $dir . "/" . "README.markdown";
        if (file_exists($filePath1)) {
            $content = file_get_contents($filePath1);
        } else {
            if (file_exists($filePath2)) {
                $content = file_get_contents($filePath2);
            }
        }
        $fileUpgrade = dirname(__DIR__, 3) . "/" . $root . "/" . $dir . "/" . "UPGRADE.md";
        if (file_exists($fileUpgrade)) {
            $content .= file_get_contents($fileUpgrade);
        }
        return $content;
    }
}