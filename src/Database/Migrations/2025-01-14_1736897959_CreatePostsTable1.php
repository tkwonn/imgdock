<?php

namespace Database\Migrations;

use Database\SchemaMigration;

class CreatePostsTable1 implements SchemaMigration
{
    public function up(): array
    {
        return [
            'CREATE TABLE posts (
                id BIGINT PRIMARY KEY AUTO_INCREMENT,
                title VARCHAR(100) NOT NULL,
                description TEXT,
                extension ENUM("jpg", "jpeg", "png", "gif") NOT NULL,
                size INT UNSIGNED NOT NULL,
                s3_key CHAR(21) NOT NULL UNIQUE,
                delete_key CHAR(16) NOT NULL UNIQUE,
                view_count INT UNSIGNED DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP DEFAULT NULL,
                INDEX idx_posts_deleted_created (deleted_at, created_at)
            );',
        ];
    }

    public function down(): array
    {
        return [
            'DROP TABLE IF EXISTS posts;',
        ];
    }
}
