<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Controller\JWTAuthenticatedController;
use AppBundle\Utils\APIResponseCode;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SecurityController
 * @package AppBundle\Controller
 * @Route("/api/security")
 */
class SecurityAPIController extends Controller implements JWTAuthenticatedController
{
    /**
     * 后台登录接口
     * @Route("/login")
     * @Method("POST")
     * @param Request $request
     * @return array|JsonResponse
     */
    public function loginAction(Request $request)
    {
        // 获取参数
        $username = $request->get('username');
        $password = $request->get('password');

        // 检查参数
        $apiResponseGenerator = $this->get('app.api_response_generator');
        if (is_null($username) || is_null($password)) {
            return $apiResponseGenerator->generateByCode(APIResponseCode::CODE_BAD_REQUEST);
        }

        // 根据用户名获取用户
        $em = $this->getDoctrine()->getManager();
        $checkUser = $em->getRepository('AppBundle:AdminUser')
            ->findOneBy(['username' => $username]);

        // 检查用户名对应的用户是否存在
        if (!$checkUser) {
            return $apiResponseGenerator->generateByCode(APIResponseCode::CODE_AUTH_INFO_INVALID);
        }

        // 检查密码是否正确
        $passwordEncoder = $this->get('security.password_encoder');
        if (!$passwordEncoder->isPasswordValid($checkUser, $password)) {
            return $apiResponseGenerator->generateByCode(APIResponseCode::CODE_AUTH_INFO_INVALID);
        }

        // 生成 JWT
        $signer = new Sha256();
        $tokenBuilder = new Builder();

        $token = $tokenBuilder
            ->setIssuedAt(time())
            ->setNotBefore(time() + 1)
            ->setExpiration(time() + $this->getParameter('jwt_ttl'))
            ->set('username', $checkUser->getUsername())
            ->sign($signer, $this->getParameter('secret'))
            ->getToken();

        $responseData = $apiResponseGenerator->generateByCode(APIResponseCode::CODE_SUCCESS, true);
        $responseData['token'] = (string)$token;

        // 更新最后一次登录时间
        $checkUser->setLastLoginAt(time());
        $em->flush();
        $em->clear();

        return new JsonResponse($responseData);
    }

    /**
     * 后台登出接口
     * @Route("/logout")
     * @Method("POST")
     * @param Request $request
     * @return array|JsonResponse
     */
    public function logoutAction(Request $request)
    {
        $username = $request->attributes->get('username');

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:AdminUser')
            ->findOneBy(['username' => $username]);

        $apiResponseGenerator = $this->get('app.api_response_generator');

        if ($user) {
            $user->setLastLogoutAt(time());
            $em->flush();
            $em->clear();
        }

        return $apiResponseGenerator->generateByCode(APIResponseCode::CODE_SUCCESS);
    }

    /**
     * @Route("/check-status")
     * @Method("POST")
     * @return array|JsonResponse
     */
    public function checkStatusAction()
    {
        $apiResponseGenerator = $this->get('app.api_response_generator');

        return $apiResponseGenerator->generateByCode(APIResponseCode::CODE_SUCCESS);
    }

    /**
     * 获取用户信息接口
     * @Route("/get-user-info")
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserInfoAction(Request $request)
    {
        $username = $request->attributes->get('username');

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:AdminUser')
            ->findOneBy(['username' => $username]);

        $apiResponseGenerator = $this->get('app.api_response_generator');

        $respBody = $apiResponseGenerator->generateByCode(APIResponseCode::CODE_SUCCESS, true);
        $respBody['userInfo'] = [
            'username' => $username,
            'createdAt' => $user->getCreatedAt(),
        ];

        return new JsonResponse($respBody);
    }
}
