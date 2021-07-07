<?php

namespace Knowfox\GraphQL\Queries;

use GraphQL;
use GraphQL\Type\Definition\Type;
use Folklore\GraphQL\Support\Query;
use Knowfox\Models\Concept;

class ConceptsQuery extends Query
{
    protected $attributes = [
        'name' => 'concepts',
    ];

    public function type()
    {
        return Type::listOf(GraphQL::type('Concept'));
    }

    public function args()
    {
        return [
            'id' => ['name' => 'id', 'type' => Type::id()],
            'title' => ['name' => 'title', 'type' => Type::string()]
        ];
    }

    public function resolve($root, $args)
    {
        if (isset($args['id'])) {
            return Concept::where('id' , $args['id'])->get();
        }
        else
        if (isset($args['title'])) {
            return Concept::where('title', $args['title'])->get();
        }
        else {
            return Concept::paginate();
        }
    }
}