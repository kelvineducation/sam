<?php

use function The\db;

// add initial schema

if ($rollback === true) {
    $sql = <<<SQL
DROP TABLE assets;
DROP TABLE versions;
DROP TABLE packages;
DROP TABLE content_blobs;
SQL;

    db()->query($sql);
    return true;
}

$sql = <<<SQL
CREATE TABLE content_blobs (
    content_blob_id serial PRIMARY KEY,
    content_hash varchar NOT NULL,
    content_base64 varchar NOT NULL,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);

CREATE INDEX ON content_blobs (content_hash);

CREATE TABLE packages (
    package_id serial PRIMARY KEY,
    name varchar NOT NULL UNIQUE,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);

CREATE TABLE versions (
    version_id serial PRIMARY KEY,
    package_id int NOT NULL REFERENCES packages,
    name varchar NOT NULL,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    UNIQUE (package_id, name)
);

CREATE TABLE assets (
    asset_id serial PRIMARY KEY,
    version_id int REFERENCES versions,
    path varchar NOT NULL,
    content_blob_id int NOT NULL REFERENCES content_blobs,
    content_type varchar NOT NULL,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    UNIQUE (version_id, path)
);
SQL;

db()->query($sql);
