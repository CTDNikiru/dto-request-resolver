<?php

namespace CTDNikiru\DtoRequestResolver\Resolvers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Exception\PartialDenormalizationException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use ReflectionClass;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use CTDNikiru\DtoRequestResolver\Resolvers\Exceptions\ConstraintViolationException;
use CTDNikiru\DtoRequestResolver\Resolvers\NameConverters\SnakeCaseToCamelCaseConverter;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

abstract class BaseRequestResolver implements ValueResolverInterface
{
    private const ROUTE_PARAMS_KEY = '_route_params';

    abstract public function checkRequestMethod(Request $request): void;

    abstract public function getParamsFromRequest(Request $request): array;

    private Serializer $serializer;

    public function __construct(
        private readonly ValidatorInterface $validator
    ) {
        $reflectionExtractor = new ReflectionExtractor();
        $phpDocExtractor = new PhpDocExtractor();
        $propertyInfoExtractor = new PropertyInfoExtractor(
            [$reflectionExtractor],
            [$phpDocExtractor],
            [$phpDocExtractor],
            [$reflectionExtractor],
            [$reflectionExtractor]
        );
        $this->serializer = new Serializer(
            [
                new ObjectNormalizer(
                    nameConverter: new SnakeCaseToCamelCaseConverter(),
                    propertyTypeExtractor: $propertyInfoExtractor
                ),
                new ArrayDenormalizer(),
            ],
            []
        );
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $dtoClass = $argument->getType();
        $this->checkRequestMethod($request);
        $params = $this->getParamsFromRequest($request);

        try {
            $dto = $this->serializer->denormalize(
                $params,
                $dtoClass,
                'array',
                [DenormalizerInterface::COLLECT_DENORMALIZATION_ERRORS => true]
            );
        } catch (PartialDenormalizationException $e) {
            $constraintViolationList = new ConstraintViolationList();
            $reflectionClass = new ReflectionClass($argument->getType());
            foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                if (!$reflectionProperty->isInitialized($e->getData())) {
                    $propertyType = $reflectionProperty->getType();
                    if (str_contains($propertyType, '?')) {
                        $message = 'Неверный тип параметра. Ожидался ' . ltrim($reflectionProperty->getType(), '?') . ' или null';
                    } else {
                        $message = 'Неверный тип параметра. Ожидался ' . $reflectionProperty->getType();
                    }

                    $constraintViolationList->add(new ConstraintViolation($message, '', [], null, $reflectionProperty->getName(), null));
                }
            }
            throw new ConstraintViolationException($constraintViolationList);
        }

        $validationErrors = $this->validator->validate($dto);
        if ($validationErrors->count() > 0) {
            throw new ConstraintViolationException($validationErrors);
        }

        return [$dto];
    }

    public function getRequestRouteParams(Request $request): array
    {
        return $request->attributes->get(self::ROUTE_PARAMS_KEY);
    }

    public function strictConvert(array $data): array
    {
        $data = array_map(fn ($value) => is_array($value) ? $this->emptyValuesFilter($value) : $value, $data);
        $data = $this->emptyValuesFilter($data);

        if (empty($data)) {
            return [];
        }
        $json = json_encode($data, JSON_NUMERIC_CHECK);
        $json = str_replace(['"false"', '"true"'], ['false', 'true'], $json);

        return json_decode($json, true);
    }

    private function emptyValuesFilter(array $values): array
    {
        return array_filter($values, function ($value) {
            return is_numeric($value) || false === is_null($value);
        }, ARRAY_FILTER_USE_BOTH);
    }
}
