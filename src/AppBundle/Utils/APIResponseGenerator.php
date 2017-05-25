<?php

namespace AppBundle\Utils;


use Symfony\Component\HttpFoundation\JsonResponse;

class APIResponseGenerator
{
    private $responseList = [
        'response_200' => ['code' => APIResponseCode::CODE_SUCCESS, 'msg' => '成功'],
        'response_400' => ['code' => APIResponseCode::CODE_BAD_REQUEST, 'msg' => '请求缺失参数'],
        'response_400.88' => ['code' => APIResponseCode::CODE_AUTH_INFO_INVALID, 'msg' => '用户名或密码错误'],
        'response_401' => ['code' => APIResponseCode::CODE_NEED_UNAUTHORIZED, 'msg' => '需要授权'],
        'response_403' => ['code' => APIResponseCode::CODE_NEED_FORBIDDEN, 'msg' => '无权访问'],
        'response_404' => ['code' => APIResponseCode::CODE_NOT_FOUND, 'msg' => '不存在的资源'],
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

    /**
     * @param $code
     * @param array $data
     * @return JsonResponse
     */
    public function generate($code, $data = [])
    {
        $defaultData = [];

        $responseType = 'response_' . $code;
        if (isset($this->responseList[$responseType])) {
            $defaultData = $this->responseList[$responseType];
            $defaultData['code'] = $defaultData['code'] * 100;
        }

        $responseData = array_merge($defaultData, $data);

        return new JsonResponse($responseData);
    }
}