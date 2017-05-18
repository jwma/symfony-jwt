<?php

namespace AppBundle\EventListener;


use AppBundle\Controller\JWTAuthenticatedController;
use AppBundle\Exception\JWTExpiredException;
use AppBundle\Exception\JWTInvalidSignatureException;
use AppBundle\Exception\JWTNotFoundException;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Token;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class JWTListener
{
    /**
     * @var string
     */
    private $secret;

    /**
     * @var int
     */
    private $jwtTTL;

    /**
     * JWTListener constructor.
     * @param $secret
     * @param $jwtTTL
     */
    public function __construct($secret, $jwtTTL)
    {
        $this->secret = $secret;
        $this->jwtTTL = $jwtTTL;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        if (!is_array($controller)) {
            return;
        }

        $request = $event->getRequest();

        // 如果是需要检查 JWT 的控制器且访问的路由不是登录路由，则需要运行检查 JWT 逻辑
        if ($controller[0] instanceof JWTAuthenticatedController
            && $request->getRequestUri() !== '/admin/api/security/login'
        ) {
            $jwt = $request->headers->get('Authorization');

            if (is_null($jwt)) {
                throw new JWTNotFoundException();
            }

            $token = (new Parser())->parse((string)$jwt);

            $signer = new Sha256();
            if (!$token->verify($signer, $this->secret)) {
                throw new JWTInvalidSignatureException();
            }

            if ($token->isExpired()) {
                throw new JWTExpiredException();
            }

            $request->attributes->set('admin-jwt', $token);
            $request->attributes->set('username', $token->getClaim('username'));
        }
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        $token = $request->attributes->get('admin-jwt');

        if ($token instanceof Token) {
            $expired = $token->getClaim('exp');
            $now = time();

            // 如果 Token 存活时间小于600秒，则刷新一个新 Token 并返回给前端
            if ($expired - $now < 600) {
                $username = $token->getClaim('username');
                // 生成 JWT
                $signer = new Sha256();
                $tokenBuilder = new Builder();

                $newToken = $tokenBuilder
                    ->setIssuedAt(time())
                    ->setNotBefore(time() + 1)
                    ->setExpiration(time() + 5)
                    ->set('username', $username)
                    ->sign($signer, $this->secret)
                    ->getToken();

                $response->headers->set('X-REFRESH-JWT', (string)$newToken);
            }
        }
    }
}