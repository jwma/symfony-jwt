<?php

namespace AppBundle\EventListener;


use AppBundle\Exception\JWTExpiredException;
use AppBundle\Exception\JWTInvalidSignatureException;
use AppBundle\Exception\JWTNotFoundException;
use AppBundle\Utils\APIResponseCode;
use AppBundle\Utils\APIResponseGenerator;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class JWTExceptionListener
{
    /**
     * @var APIResponseGenerator
     */
    private $apiResponseGenerator;

    /**
     * JWTListener constructor.
     * @param $apiResponseGenerator
     */
    public function __construct(APIResponseGenerator $apiResponseGenerator)
    {
        $this->apiResponseGenerator = $apiResponseGenerator;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if ($exception instanceof JWTNotFoundException ||
            $exception instanceof JWTInvalidSignatureException ||
            $exception instanceof JWTExpiredException
        ) {
            $response = $this->apiResponseGenerator->
                generateByCode(APIResponseCode::CODE_NEED_UNAUTHORIZED);

            $event->setResponse($response);
        }
    }
}