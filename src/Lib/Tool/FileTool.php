<?php

namespace App\Lib\Tool;

class FileTool
{
    /**
     * 在文件夹中查找文件
     * @param string $dir 文件夹绝对路径
     * @param string $fileName 文件文件名 example.php
     * @param bool $recursion 是否递归，默认false
     * @return array 目标文件绝对路径
     */
    public function find(string $dir, string $fileName, bool $recursion = false): array
    {
        $dir = rtrim($dir, DIRECTORY_SEPARATOR);
        $rs = [];
        if (empty($dir) || empty($fileName) || !is_dir($dir)) {
            return $rs;
        }
        $fn = dir($dir);
        while ($f = $fn->read()) {
            if ($f == "." || $f == "..") {
                continue;
            }
            $fPath = $dir . DIRECTORY_SEPARATOR . $f;
            if (is_file($fPath)) {
                if ($f === $fileName) {
                    $rs[] = $fPath;
                }
            }
            if ($recursion) {
                if (is_dir($fPath)) {
                    $tmp = $this->find($fPath, $fileName, $recursion);
                    array_push($rs, ...$tmp);
                }
            }
        }
        return $rs;
    }

    public function getNamespace(string $file): string
    {
        if (empty($file)) {
            return "";
        }
        $namespace = "";
        $fn = fopen($file, "r");
        while ($line = fgets($fn)) {
            if (str_starts_with($line, "namespace")) {
                $namespace = trim(str_replace(["namespace", ";", PHP_EOL], '', $line));
                break;
            }
        }
        fclose($fn);
        return $namespace;
    }

    public function getFullClassName(string $file): string
    {
        $namespace = $this->getNamespace($file);
        return $namespace . "\\" . str_replace(".php", "", basename($file));
    }
}