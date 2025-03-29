<?php

namespace App\Twig;

use App\Service\AdminUserService;

class PageExtension extends \Twig\Extension\AbstractExtension
{

    public function getFunctions(): array
    {
        return [
            new \Twig\TwigFunction("pagination",[$this,'pagination'])
        ];
    }

    public function pagination(int $current, int $total, string $route): string
    {
        if($total <= 1) return "";
        $page = "<div class='pagination'>";
        $page .= "共{$total}页 ";
        if($total <= 5){
            for ($i = 1; $i <= $total; $i++) {
                $page .= "<a href='javascript:;' data-page='{$i}'>{$i}</a> ";
            }
        }else{
            if($current > 1){
                $page .= "<a href='javascript:;' data-page='prev'>上一页</a> ";
            }
            if($current <= 5){
                for ($i = 1; $i <= $total; $i++) {
                    $page .= "<a href='javascript:;' data-page='{$i}'>{$i}</a> ";
                }
                $page .= "<a href='javascript:;' data-page=''>...</a> ";
            }
            if($current > 5 && $current < $total - 4){
                $page .= "<a href='javascript:;' data-page='1'>1</a> ";
                $page .= "<a href='javascript:;' data-page=''>...</a> ";
                for ($i = $current - 2; $i <= $current + 2; $i++) {
                    $page .= "<a href='javascript:;' data-page='{$i}'>{$i}</a> ";
                }
                $page .= "<a href='javascript:;' data-page=''>...</a> ";
            }
            if($current >= $total - 4){
                $page .= "<a href='javascript:;' data-page='1'>1</a> ";
                $page .= "<a href='javascript:;' data-page=''>...</a> ";
                for ($i = $current; $i <= $total; $i++) {
                    $page .= "<a href='javascript:;' data-page='{$i}'>{$i}</a> ";
                }
            }
        }
        $page .= "<a href='javascript:;' data-page='next'>下一页</a> ";
        $page .= "</div>";
        return $page;
    }

    public function getName(): string
    {
        return 'app_extension';
    }
}