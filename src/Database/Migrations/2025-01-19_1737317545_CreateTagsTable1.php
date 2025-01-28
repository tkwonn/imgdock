<?php

namespace Database\Migrations;

use Database\SchemaMigration;

class CreateTagsTable1 implements SchemaMigration
{
    public function up(): array
    {
        return [
            'CREATE TABLE tags (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(50) NOT NULL,
                description TEXT DEFAULT NULL,
                s3_key CHAR(18) DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL DEFAULT NULL
            );',
        ];
    }

    public function down(): array
    {
        return [
            'DROP TABLE IF EXISTS tags;',
        ];
    }
}
