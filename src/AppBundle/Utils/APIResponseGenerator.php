<?php

namespace AppBundle\Utils;


use Symfony\Component\HttpFoundation\JsonResponse;

class APIResponseGenerator
{
    private $responseList = [
        'response_200' => ['code' => 200, 'msg' => '成功'],
        'response_400' => ['code' => 400, 'msg' => '请求缺失参数'],
        'response_401' => ['code' => 401, 'msg' => '用户名或密码错误'],
        'response_403' => ['code' => 403, 'msg' => '无权访问'],
        'response_403.17' => ['code' => 403.17, 'msg' => '授权已过期'],
        'response_404' => ['code' => 404, 'msg' => '不存在的资源'],
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
            $body['code'] = $body['code'] * 100;
        }

        return $onlyBody ? $body : new JsonResponse($body);
    }
}