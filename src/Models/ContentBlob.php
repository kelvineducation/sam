<?php

namespace App\Models;

use The\Model;

class ContentBlob extends Model
{
    protected static $table_name  = 'content_blobs';
    protected static $primary_key = 'content_blob_id';

    protected $data = [
        'content_blob_id' => Model::DEFAULT,
        'content_hash'    => null,
        'content_base64'  => null,
        'created_at'      => Model::DEFAULT,
        'updated_at'      => Model::DEFAULT,
    ];

    private $content;

    public static function dedupe(string $content)
    {
        $hash = hash('sha256', $content);
        foreach (self::fetchAllWhere('content_hash = $1', [$hash]) as $blob) {
            /** @var $blob ContentBlob */
            if ($blob->getContent() === $content) {
                return $blob;
            }
        }

        return ContentBlob::create([
            'content_hash'   => $hash,
            'content_base64' => base64_encode($content),
        ]);
    }

    /**
     * @return string
     */
    public function getContent()
    {
        if (null !== $this->content) {
            return $this->content;
        }

        $this->content = base64_decode($this->getData('content_base64'));

        return $this->content;
    }
}
