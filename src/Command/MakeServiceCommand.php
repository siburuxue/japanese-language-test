<?php

namespace App\Command;

use App\Lib\Tool\FileTool;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'make:service',
    description: 'make Service File and DI repository in it [php bin/console make:service TestPaper --entities="TestPaper"]',
)]
class MakeServiceCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private FileTool $file,
    )
    {
        parent::__construct();
    }

    private function getFullClass($file): array
    {
        $srcPath = dirname(__DIR__);
        $vendorPath = dirname(__DIR__, 2) . "/" . "vendor";
        $vendorRS = $this->file->find($vendorPath, $file, true);
        $srcRS = $this->file->find($srcPath, $file, true);
        $rs = array_merge($vendorRS, $srcRS);
        $fullClass = [];
        foreach ($rs as $r) {
            $fullClass[] = $this->file->getFullClassName($r);
        }
        return $fullClass;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::OPTIONAL, 'service name for example Dict => DictService.php')
            ->addOption('entities', "-en", InputOption::VALUE_OPTIONAL,
                'DI entities to the service split by " "  for example "Dict User"')
            ->addOption('di', "-d", InputOption::VALUE_OPTIONAL,
                'DI Classes to the service split by " "  for example "Mailer Logger"');
    }

    private function ask(string $question, mixed $answerLimit)
    {
        $answer = $this->io->ask($question);
        if (!isset($answerLimit)) return $answer;
        $accepted = true;
        if (is_array($answerLimit)) {
            $accepted = in_array($answer, $answerLimit);
        }else if (is_callable($answerLimit)) {
            $accepted = call_user_func($answerLimit, $answer);
        }
        if ($accepted) return $answer;
        $this->io->warning("the answer is illegal");
        return $this->ask($question, $answerLimit);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $stringParam = "";
        $string = "";
        $stringDI = "";
        $stringUse = "";
        $this->io = new SymfonyStyle($input, $output);
        $name = $input->getArgument('name');
        $entities = $input->getOption('entities');
        $di = $input->getOption('di');
        if ($entities) {
            $stringParam = "ManagerRegistry \$doctrine";
            $stringUse .= "\r\nuse Doctrine\\Persistence\\ManagerRegistry;";
            $stringUse .= "\r\nuse Doctrine\\Persistence\\ObjectRepository;";
            $entities = explode(" ", $entities);
            foreach ($entities as $entity) {
                $stringUse .= "\r\nuse App\\Entity\\{$entity};";
                $string .= "\tprivate ObjectRepository \$" . lcfirst($entity) . "Repository;\r\n";
                $stringDI .= "\r\n\t\t\$this->" . lcfirst($entity) . "Repository = \$doctrine->getRepository({$entity}::class);";
            }
        }
        if ($di) {
            $di = explode(" ", $di);
            if (!empty($stringParam)) {
                $stringParam .= ", ";
            }
            foreach ($di as $k => $d) {
                $var = lcfirst(str_replace("Interface", "", $d));
                $stringParam .= "$d \$" . $var;
                if ($k != count($di) - 1) {
                    $stringParam .= ", ";
                }
                $string .= "\tprivate " . $d . " \$" . $var . ";\r\n";
                $stringDI .= "\r\n\t\t\$this->" . $var . " = \$" . $var . ";";

                $fullClass = $this->getFullClass($d . ".php");
                if (count($fullClass) > 0) {
                    if (count($fullClass) > 1) {
                        $askString = "Do `$d` is in the list? [default 0]\r\n";
                        foreach ($fullClass as $index => $className) {
                            $askString .= "[{$index}]:{$className}.\r\n";
                        }
                        $answer = $this->ask($askString, array_keys($fullClass));
                        $stringUse .= "\r\nuse " . $fullClass[(int)$answer] . ";";
                        $this->io->note($d . ' will use ' . $fullClass[(int)$answer]);
                    } else {
                        $this->io->note($d . ' will use ' . $fullClass[0]);
                        $stringUse .= "\r\nuse " . $fullClass[0] . ";";
                    }
                } else {
                    $this->io->note("{$d} is not exist in the [src/,vendor/]");
                }
            }
        }
        if (empty($entities) && empty($di)) {
            $stringDI = "\r\n\t\t";
        }
        if ($name) {
            $fileDir = dirname(__DIR__) . "/Service/";
            if (!file_exists($fileDir)) {
                mkdir($fileDir);
            }
            $filePath = $fileDir . $name . "Service.php";
            $content = <<<EOF
<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
{$stringUse}

class {$name}Service extends CommonService
{
{$string}
    public function __construct(RequestStack \$requestStack, {$stringParam})
    {
        parent::__construct(\$requestStack);
        {$stringDI}
    }
}
EOF;
            file_put_contents($filePath, $content);
            $this->io->success("{$name}Service has been complete!");
            return Command::SUCCESS;
        } else {
            $this->io->error("Service Name is empty.");
            return Command::FAILURE;
        }
    }
}
