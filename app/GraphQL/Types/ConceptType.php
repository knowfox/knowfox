<?php

namespace Knowfox\GraphQL\Types;

use GraphQL;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Type as GraphQLType;

class ConceptType extends GraphQLType
{
    private $concept;

    protected $attributes = [
        'name' => 'Concept',
        'description' => 'A concept',
    ];

    public function fields()
    {
        $fields = [
            'id' => [
                'type' => Type::nonNull(Type::id()),
                'description' => 'The id of the concept',
            ],
            'type' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'The type of the concept',
            ],
            'parent' => [
                'type' => GraphQL::type('Concept'),
                'description' => 'The parent_id of the concept',
            ],
            'source_url' => [
                'type' => Type::string(),
                'description' => 'The source_url of the concept',
            ],
            'title' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'The title of the concept',
            ],
            'summary' => [
                'type' => Type::string(),
                'description' => 'The summary of the concept',
            ],
            'body' => [
                'type' => Type::string(),
                'description' => 'The body of the concept',
            ],
            'todoist_id' => [
                'type' => Type::id(),
                'description' => 'The todoist_id of the concept',
            ],
            'slug' => [
                'type' => Type::string(),
                'description' => 'The slug of the concept',
            ],
            'is_flagged' => [
                'type' => Type::boolean(),
                'description' => 'The is_flagged of the concept',
            ],
            'status' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'The status of the concept',
            ],
            'language' => [
                'type' => Type::string(),
                'description' => 'The language of the concept',
            ],
            'uuid' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'The uuid of the concept',
            ],
            /*
            'relations' => [
                'type' => Type::listOf(GraphQL::type('Relationship')),
                'description' => 'The relations of the concept',
            ],
            'config' => [
                'description' => 'The relations of the concept',
            ],
            */
            'owner' => [
                'type' => Type::nonNull(GraphQL::type('User')),
                'description' => 'The relations of the concept',
            ],
        ];

        return $fields;
    }
}
