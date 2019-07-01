<?php

namespace App\Models;

use The\Model;

class Version extends Model
{
    protected static $table_name  = 'versions';
    protected static $primary_key = 'version_id';

    protected $data = [
        'version_id' => Model::DEFAULT,
        'package_id' => null,
        'name'       => null,
        'created_at' => Model::DEFAULT,
        'updated_at' => Model::DEFAULT,
    ];

    public function addAsset($path, $content)
    {
        $blob = ContentBlob::dedupe($content);

        Asset::create([
            'version_id'      => $this->getId(),
            'path'            => $path,
            'content_blob_id' => $blob->getId(),
        ]);
    }
}
