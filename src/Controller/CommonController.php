<?php

namespace App\Controller;

use App\Lib\Constant\Code;
use App\Lib\Constant\Message;
use App\Lib\Constant\Tool;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CommonController extends AbstractController
{
    protected function insertSuccess(array $data = []): JsonResponse
    {
        return $this->json([
            'status' => Code::RESPONSE_TRUE,
            'msg' => Message::INSERT_SUCCESS,
            'data' => $data,
        ]);
    }

    protected function error($msg = ""): JsonResponse
    {
        return $this->json([
            'status' => Code::RESPONSE_FALSE,
            'msg' => $msg,
        ]);
    }

    protected function success($msg = ""): JsonResponse
    {
        return $this->json([
            'status' => Code::RESPONSE_TRUE,
            'msg' => $msg,
        ]);
    }

    protected function insertFailed(array $data = []): JsonResponse
    {
        return $this->json([
            'status' => Code::RESPONSE_FALSE,
            'msg' => Message::INSERT_FAILED,
            'data' => $data,
        ]);
    }

    protected function updateSuccess(array $data = []): JsonResponse
    {
        return $this->json([
            'status' => Code::RESPONSE_TRUE,
            'msg' => Message::UPDATE_SUCCESS,
            'data' => $data,
        ]);
    }

    protected function updateFailed(array $data = []): JsonResponse
    {
        return $this->json([
            'status' => Code::RESPONSE_TRUE,
            'msg' => Message::UPDATE_FAILED,
            'data' => $data,
        ]);
    }

    protected function saveSuccess(array $data = []): JsonResponse
    {
        return $this->json([
            'status' => Code::RESPONSE_TRUE,
            'msg' => Message::SAVE_SUCCESS,
            'data' => $data,
        ]);
    }

    protected function saveFailed(array $data = []): JsonResponse
    {
        return $this->json([
            'status' => Code::RESPONSE_TRUE,
            'msg' => Message::SAVE_FAILED,
            'data' => $data,
        ]);
    }

    protected function deleteSuccess(array $data = []): JsonResponse
    {
        return $this->json([
            'status' => Code::RESPONSE_TRUE,
            'msg' => Message::DELETE_SUCCESS,
            'data' => $data,
        ]);
    }

    protected function deleteFailed(array $data = []): JsonResponse
    {
        return $this->json([
            'status' => Code::RESPONSE_TRUE,
            'msg' => Message::DELETE_FAILED,
            'data' => $data,
        ]);
    }

    protected function csrfValid(): bool
    {
        $submittedToken = Request::createFromGlobals()->headers->get(Tool::getCSRFHeaderName());
        return $this->isCsrfTokenValid(Tool::CSRF_NAME, $submittedToken);
    }
}