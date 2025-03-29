<?php

namespace App\Logger;

use App\Lib\Tool\StringTool;
use Monolog\LogRecord;
use Symfony\Component\HttpFoundation\Request;

class SessionRequestProcessor
{
    private string $token = "";

    public function __invoke(LogRecord $record):LogRecord
    {
        if(!$this->token){
           $this->token =  StringTool::random(32);
        }
        $record->extra['token'] = $this->token;
        $record->extra['post'] = Request::createFromGlobals()->request->all();
        $record->extra['get'] = Request::createFromGlobals()->query->all();
        $record->extra['header'] = Request::createFromGlobals()->headers->all();
        $record->extra['route_params'] = Request::createFromGlobals()->attributes->get('_route_params', []);
        return $record;
    }

    public function getToken(): string
    {
        if(!$this->token){
            $this->token =  StringTool::random(32);
        }
        return $this->token;
    }
}