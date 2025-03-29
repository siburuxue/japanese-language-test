<?php

namespace App\Twig;

class ToolExtension extends \Twig\Extension\AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new \Twig\TwigFunction("formatTime",[$this,'formatTime'])
        ];
    }

    public function formatTime(int $number):mixed
    {
        $number = (int)($number);
        $second = $number % 60;
        $minute = ($number - $second) / 60;
        if($second < 10){
            $second = "0" . $second;
        }
        if($minute >= 60){
            $tmp = $minute % 60;
            $hour = ($minute - $tmp) / 60;
            if($tmp < 10){
                $tmp = "0" . $tmp;
            }
            $minute = $tmp;
            return $hour . ":" . $minute . ":" . $second;
        }
        return $minute . ':' . $second;
    }

    public function getName(): string
    {
        return 'app_extension';
    }
}