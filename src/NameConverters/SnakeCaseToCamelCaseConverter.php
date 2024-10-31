<?php

namespace CTDNikiru\DtoRequestResolver\NameConverters;

use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class SnakeCaseToCamelCaseConverter implements NameConverterInterface
{
    public function normalize(string $propertyName): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $propertyName));
    }

    public function denormalize(string $propertyName): string
    {
        $result = $propertyName;
        if (str_contains($result, '_')) {
            $result = str_replace(' ', '', ucwords(str_replace('_', ' ', $propertyName)));
            $result[0] = strtolower($result[0]);
        }

        return $result;
    }
}
