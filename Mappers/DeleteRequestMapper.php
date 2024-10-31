<?php

namespace CTDNikiru\DtoRequestResolver\Resolvers\Mappers;

use Attribute;
use CTDNikiru\DtoRequestResolver\Resolvers\DeleteRequestResolver;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Validator\Constraints\GroupSequence;

/**
 * @Annotation
 */
#[Attribute]
class DeleteRequestMapper extends MapRequestPayload
{
    public function __construct(
        array|string $acceptFormat = null,
        ?array $serializationContext = [],
        GroupSequence|array|string $validationGroups = null
    ) {
        parent::__construct($acceptFormat, $serializationContext, $validationGroups, DeleteRequestResolver::class);
    }
}
