<?php

namespace AppBundle\Utils;


use Symfony\Component\HttpFoundation\JsonResponse;

class APIResponse
{
    private $responseList = [
        'response_200' => ['code' => 200, 'msg' => '成功'],
        'response_400' => ['code' => 400, 'msg' => '请求缺失参数'],
        'response_401' => ['code' => 401, 'msg' => '登录失败'],
    ];

    /**
     * @param $code
     * @param bool $onlyBody
     * @return array|JsonResponse
     */
    public function generateByCode($code, $onlyBody = false)
    {
        $body = [];

        $responseType = 'response_' . $code;
        if (isset($this->responseList[$responseType])) {
            $body = $this->responseList[$responseType];
        }

        return $onlyBody ? $body : new JsonResponse($body);
    }
}