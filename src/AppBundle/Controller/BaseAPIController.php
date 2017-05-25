<?php

namespace AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BaseAPIController extends Controller
{
    /**
     * 根据响应码跟接口数据生成一个 JsonResponse
     *
     * @param $code
     * @param array $data
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function response($code, $data = [])
    {
        return $this->get('app.api_response_generator')->generate($code, $data);
    }
}