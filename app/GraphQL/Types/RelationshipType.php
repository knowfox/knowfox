<?php

namespace Knowfox\GraphQL\Types;

use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Type as GraphQLType;

class RelationshipType extends GraphQLType
{
    private $concept;

    protected $attributes = [
        'name' => 'Relationship',
        'description' => 'A relationship',
    ];

    public function fields()
    {
        $fields = [
            'type' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'The type of the relationship',
            ],
            'target_id' => [
                'type' => Type::nonNull(Type::id()),
                'description' => 'The target_id of the relationship',
            ],
        ];

        return $fields;
    }
}
