<?php

namespace CTDNikiru\DtoRequestResolver\Resolvers\Mappers;

use CTDNikiru\DtoRequestResolver\Resolvers\PostRequestResolver;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Validator\Constraints\GroupSequence;
use Attribute;

/**
 * @Annotation
 */
#[Attribute]
class PostRequestMapper extends MapRequestPayload
{
    public function __construct(
        array|string $acceptFormat = null,
        ?array $serializationContext = [],
        GroupSequence|array|string $validationGroups = null
    ) {
        parent::__construct($acceptFormat, $serializationContext, $validationGroups, PostRequestResolver::class);
    }
}
