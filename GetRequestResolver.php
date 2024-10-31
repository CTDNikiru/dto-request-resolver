<?php

namespace CTDNikiru\DtoRequestResolver;

use Symfony\Component\HttpFoundation\Request;
use Exception;

class GetRequestResolver extends BaseRequestResolver
{
    /**
     * @throws Exception
     */
    public function checkRequestMethod(Request $request): void
    {
        if (!in_array($request->getMethod(), [Request::METHOD_GET, Request::METHOD_DELETE])) {
            throw new Exception('Неподдерживаемый тип запроса, допустимы только GET, DELETE');
        }
    }

    public function getParamsFromRequest(Request $request): array
    {
        $params = $request->query->all();
        $routeParams = $this->getRequestRouteParams($request);

        return $this->strictConvert(array_merge($params, $routeParams));
    }
}
