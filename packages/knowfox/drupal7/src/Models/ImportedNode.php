<?php

namespace Knowfox\Drupal7\Models;

use Illuminate\Database\Eloquent\Model;

class ImportedNode extends Model
{
    protected $connection = 'd7';
    protected $table = 'node';
}
