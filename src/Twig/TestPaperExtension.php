<?php

namespace App\Twig;

use App\Service\TestPaperService;

class TestPaperExtension extends \Twig\Extension\AbstractExtension
{
    public function __construct(
        private TestPaperService $testPaperService,
    ) {}

    public function getFunctions(): array
    {
        return [
            new \Twig\TwigFunction("getPaperCreator",[$this,'getPaperCreator']),
            new \Twig\TwigFunction("setAnswers",[$this,'setAnswers']),
            new \Twig\TwigFunction("set_array_column",[$this,'set_array_column']),
        ];
    }

    public function getPaperCreator(int $id): string
    {
        $rs = $this->testPaperService->getCreator($id);
        return $rs['name'];
    }

    public function setAnswers(string $content, array $answers): string
    {
        $answers = array_column($answers,'answer', 'q_no');
        $num = range(51,60);
        for ($i = 0; $i < count($num); $i++) {
            $content = str_replace("＿{$num[$i]}＿","<label class='question strong'>＿{$num[$i]}＿</label><label class='answer hidden strong text-success'>{$answers[$num[$i]]}</label>", $content);
        }
        return $content;
    }

    public function set_array_column(array $item, string $v, string $k): array
    {
        return array_column($item, $v, $k);
    }
    
    public function getName(): string
    {
        return 'app_extension';
    }
}