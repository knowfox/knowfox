<?php

namespace Knowfox\Models;

use Illuminate\Database\Eloquent\Model;

class ImportedEbook extends Model
{
    protected $connection = 'sqlite';
    protected $table = 'items';
}
