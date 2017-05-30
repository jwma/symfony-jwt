<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Controller\BaseAPIController;
use AppBundle\Controller\JWTAuthenticatedController;
use AppBundle\Utils\APIResponseCode;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SecurityController
 * @package AppBundle\Controller
 * @Route("/api/security")
 */
class SecurityAPIController extends BaseAPIController implements JWTAuthenticatedController
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
        if (is_null($username) || is_null($password)) {
            return $this->response(APIResponseCode::CODE_BAD_REQUEST);
        }

        // 根据用户名获取用户
        $em = $this->getDoctrine()->getManager();
        $checkUser = $em->getRepository('AppBundle:AdminUser')
            ->findOneBy(['username' => $username]);

        // 检查用户名对应的用户是否存在
        if (!$checkUser) {
            return $this->response(APIResponseCode::CODE_AUTH_INFO_INVALID);
        }

        // 检查密码是否正确
        $passwordEncoder = $this->get('security.password_encoder');
        if (!$passwordEncoder->isPasswordValid($checkUser, $password)) {
            return $this->response(APIResponseCode::CODE_AUTH_INFO_INVALID);
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

        // 更新最后一次登录时间
        $checkUser->setLastLoginAt(time());
        $em->flush();
        $em->clear();

        return $this->response(APIResponseCode::CODE_SUCCESS, [
            'token' => (string)$token,
            'username' => $checkUser->getUsername(),
        ]);
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

        if ($user) {
            $user->setLastLogoutAt(time());
            $em->flush();
            $em->clear();
        }

        return $this->response(APIResponseCode::CODE_SUCCESS);
    }

    /**
     * @Route("/check-status")
     * @Method("POST")
     * @return array|JsonResponse
     */
    public function checkStatusAction()
    {
        return $this->response(APIResponseCode::CODE_SUCCESS);
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

        return $this->response(APIResponseCode::CODE_SUCCESS, [
            'userInfo' => [
                'username' => $username,
                'createdAt' => $user->getCreatedAt(),
            ]
        ]);
    }
}
