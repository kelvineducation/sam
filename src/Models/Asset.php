<?php

namespace App\Models;

use The\Model;

class Asset extends Model
{
    protected static $table_name  = 'assets';
    protected static $primary_key = 'asset_id';

    private static $content_types;

    protected $data = [
        'asset_id'        => Model::DEFAULT,
        'version_id'      => null,
        'path'            => null,
        'content_blob_id' => null,
        'content_type'    => null,
        'created_at'      => Model::DEFAULT,
        'updated_at'      => Model::DEFAULT,
    ];

    public static function findByRequestPath($package, $version, $asset_path)
    {
        $where = <<<'SQL'
asset_id = (
    SELECT asset_id
    FROM assets
    WHERE version_id = (
            SELECT version_id
            FROM versions
            WHERE package_id = (
                    SELECT package_id
                    FROM packages
                    WHERE packages.name = $1
                )
                AND versions.name = $2
        )
        AND assets.path = $3
)
SQL;

        return self::findWhere($where, [$package, $version, $asset_path]);
    }

    public function beforeCreate()
    {
        if (null === $this->getData('content_type')) {
            $this->determineContentType();
        }
    }

    public function getBlob()
    {
        return ContentBlob::find($this->getData('content_blob_id'));
    }

    private function determineContentType()
    {
        if (!self::$content_types) {
            self::$content_types = require_once __DIR__ . '/content_types.php';
        }

        $extension = pathinfo($this->getData('path'), PATHINFO_EXTENSION);

        $this->setData(['content_type' => self::$content_types[$extension]] ?? null);
    }
}
