<?php

namespace AppBundle\Utils;


class APIResponseCode
{
    const CODE_SUCCESS = 200;
    const CODE_BAD_REQUEST = 400;
    const CODE_AUTH_INFO_INVALID = 400.88;
    const CODE_NEED_UNAUTHORIZED = 401;
    const CODE_NEED_FORBIDDEN = 403;
    const CODE_NOT_FOUND = 404;
}