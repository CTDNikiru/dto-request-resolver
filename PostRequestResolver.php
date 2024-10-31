<?php

namespace CTDNikiru\DtoRequestResolver;

use Symfony\Component\HttpFoundation\Request;
use Exception;

class PostRequestResolver extends BaseRequestResolver
{
    public function checkRequestMethod(Request $request): void
    {
        if (!in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT, Request::METHOD_PATCH])) {
            throw new Exception('Неподдерживаемый тип запроса, допустимы POST, PUT, PATCH');
        }
    }

    public function getParamsFromRequest(Request $request): array
    {
        $result = $this->getRequestRouteParams($request);

        return match ($request->getContentTypeFormat()) {
            'json' => !empty($request->getContent())
                ? $this->strictConvert(array_merge($result, $request->toArray()))
                : $result,
            'form' => array_merge($result, $request->request->all(), $request->files->all()),
            default => $result,
        };
    }
}
