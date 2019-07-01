<?php

namespace App\Models;

use The\Model;

class Package extends Model
{
    use LookupTrait;

    protected static $table_name  = 'packages';
    protected static $primary_key = 'package_id';
    protected static $lookup_column = 'name';

    protected $data = [
        'package_id' => Model::DEFAULT,
        'name'       => null,
        'created_at' => Model::DEFAULT,
        'updated_at' => Model::DEFAULT,
    ];

    public function findVersion($version_name)
    {
        return Version::findWhere(
            'package_id = $1 AND name = $2',
            [$this->getId(), $version_name]
        );
    }
}
