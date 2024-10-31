<?php

namespace CTDNikiru\DtoRequestResolver\Resolvers;

use Exception;
use Symfony\Component\HttpFoundation\Request;

class DeleteRequestResolver extends BaseRequestResolver
{
    /**
     * @throws Exception
     */
    public function checkRequestMethod(Request $request): void
    {
        if (Request::METHOD_DELETE !== $request->getMethod()) {
            throw new Exception('Неподдерживаемый тип запроса, допустим только DELETE');
        }
    }

    public function getParamsFromRequest(Request $request): array
    {
        if (empty($request->getContent())) {
            return $this->getRequestRouteParams($request);
        }

        return $this->strictConvert(array_merge($this->getRequestRouteParams($request), $request->toArray()));
    }
}
