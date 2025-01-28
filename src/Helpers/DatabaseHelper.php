<?php

namespace Helpers;

use Database\MemcachedWrapper;
use Database\MySQLWrapper;
use Exception;

class DatabaseHelper
{
    private static ?MySQLWrapper $db = null;

    private static function getDbConnection(): MySQLWrapper
    {
        if (!self::$db) {
            self::$db = new MySQLWrapper();
        }

        return self::$db;
    }

    /**
     * @throws Exception When failed to prepare statement
     * @throws Exception When failed to execute statement
     */
    public static function createPost(
        string $title,
        string $description,
        string $extension,
        int $size,
        string $s3Key,
        string $deleteKey
    ): int {
        $db = self::getDbConnection();

        $stmt = $db->prepare(
            'INSERT INTO posts (title, description, extension, size, s3_key, delete_key)
            VALUES (?, ?, ?, ?, ?, ?)'
        );
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $db->error);
        }

        $stmt->bind_param(
            'sssiss',
            $title,
            $description,
            $extension,
            $size,
            $s3Key,
            $deleteKey
        );
        if (!$stmt->execute()) {
            throw new Exception('Failed to execute statement: ' . $stmt->error);
        }

        return $db->insert_id;
    }

    /**
     * @throws Exception When failed to prepare statement
     * @throws Exception When failed to execute statement
     */
    public static function findAllPosts(string $sort, int $page, int $limit): array
    {
        $db = self::getDbConnection();
        $offset = ($page - 1) * $limit;

        $orderBy = match($sort) {
            'popular' => 'view_count DESC',
            default => 'created_at DESC'
        };

        $stmt = $db->prepare(
            "SELECT id, title, extension, s3_key, size, view_count, created_at
             FROM posts
             WHERE deleted_at IS NULL
             ORDER BY {$orderBy}
             LIMIT ? OFFSET ?"
        );
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $db->error);
        }

        $stmt->bind_param('ii', $limit, $offset);
        if (!$stmt->execute()) {
            throw new Exception('Failed to execute statement: ' . $stmt->error);
        }

        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * @throws Exception When failed to prepare statement
     * @throws Exception When failed to execute statement
     */
    public static function findPostsByTag(int $tagId, string $sort, int $page, int $limit): array
    {
        $db = self::getDbConnection();
        $offset = ($page - 1) * $limit;

        $orderBy = match($sort) {
            'popular' => 'view_count DESC',
            default => 'created_at DESC'
        };

        $stmt = $db->prepare(
            "SELECT p.id, p.title, p.extension, p.s3_key, p.size, p.view_count, p.created_at
            FROM posts p
            JOIN post_tag pt ON p.id = pt.post_id
            JOIN tags t ON pt.tag_id = t.id
            WHERE t.id = ? AND p.deleted_at IS NULL
            ORDER BY $orderBy
            LIMIT ? OFFSET ?"
        );
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $db->error);
        }

        $stmt->bind_param('iii', $tagId, $limit, $offset);
        if (!$stmt->execute()) {
            throw new Exception('Failed to execute statement: ' . $stmt->error);
        }

        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * @throws Exception When failed to prepare statement
     * @throws Exception When failed to execute statement
     * @throws Exception When post not found
     * @throws Exception When multiple posts found with the same s3Key
     */
    public static function findPostByFileName(string $extension, string $uniqueString): array
    {
        $db = self::getDbConnection();

        $stmt = $db->prepare(
            'SELECT id, title, description, extension, size, s3_key, delete_key, view_count, created_at
            FROM posts
            WHERE s3_key LIKE CONCAT("%", ?, ".", ?)
              AND deleted_at IS NULL'
        );
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $db->error);
        }

        $stmt->bind_param('ss', $uniqueString, $extension);
        if (!$stmt->execute()) {
            throw new Exception('Failed to execute statement: ' . $stmt->error);
        }

        $result = $stmt->get_result();
        $post = $result->fetch_assoc();
        if (!$post) {
            throw new Exception('Post not found');
        }

        return $post;
    }

    /**
     * @throws Exception When failed to prepare statement
     * @throws Exception When failed to execute statement
     * @throws Exception When post not found
     * @throws Exception When multiple posts found with the same s3Key
     */
    public static function findPostByDeleteKey(string $deleteKey): array
    {
        $db = self::getDbConnection();

        $stmt = $db->prepare(
            'SELECT id, title, s3_key
            FROM posts
            WHERE delete_key = ?
              AND deleted_at IS NULL'
        );
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $db->error);
        }

        $stmt->bind_param('s', $deleteKey);
        if (!$stmt->execute()) {
            throw new Exception('Failed to execute statement: ' . $stmt->error);
        }

        $result = $stmt->get_result();
        $post = $result->fetch_assoc();
        if (!$post) {
            throw new Exception('Post not found');
        }

        return $post;
    }

    /**
     * @throws Exception When failed to prepare statement
     * @throws Exception When failed to execute statement
     */
    public static function findAllTags(): array
    {
        $memcached = MemcachedWrapper::getInstance();
        $cacheKey = 'all_tags';

        $cachedValue = $memcached->get($cacheKey);
        if ($cachedValue !== false) {
            return $cachedValue;
        }

        $db = self::getDbConnection();

        $stmt = $db->prepare('SELECT id, name, s3_key FROM tags WHERE deleted_at IS NULL ORDER BY name ASC');
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $db->error);
        }

        if (!$stmt->execute()) {
            throw new Exception('Failed to execute statement: ' . $stmt->error);
        }

        $result = $stmt->get_result();
        $allTags = $result->fetch_all(MYSQLI_ASSOC);

        $memcached->set($cacheKey, $allTags, 300);

        return $allTags;
    }

    /**
     * @throws Exception When failed to prepare statement
     * @throws Exception When failed to execute statement
     * @throws Exception When tag not found
     */
    public static function findTagByName(string $tagName): array
    {
        $memcached = MemcachedWrapper::getInstance();
        $cacheKey = 'tag_name_' . $tagName;

        $cachedValue = $memcached->get($cacheKey);
        if ($cachedValue !== false) {
            return $cachedValue;
        }

        $db = self::getDbConnection();

        $stmt = $db->prepare('SELECT id, name, description, s3_key FROM tags WHERE name = ? AND deleted_at IS NULL');
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $db->error);
        }

        $stmt->bind_param('s', $tagName);
        if (!$stmt->execute()) {
            throw new Exception('Failed to execute statement: ' . $stmt->error);
        }

        $result = $stmt->get_result();
        $tag = $result->fetch_assoc();
        if (!$tag) {
            throw new Exception('Tag not found');
        }

        $memcached->set($cacheKey, $tag, 300);

        return $tag;
    }

    /**
     * @throws Exception When failed to prepare statement
     * @throws Exception When failed to execute statement
     */
    public static function deletePost(int $id): void
    {
        $db = self::getDbConnection();

        $stmt = $db->prepare(
            'UPDATE posts 
            SET deleted_at = CURRENT_TIMESTAMP
            WHERE id = ?'
        );
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $db->error);
        }

        $stmt->bind_param('i', $id);
        if (!$stmt->execute()) {
            throw new Exception('Failed to execute statement: ' . $stmt->error);
        }
    }

    /**
     * @throws Exception When failed to prepare statement
     * @throws Exception When failed to execute statement
     */
    public static function countAllPosts(): int
    {
        $memcached = MemcachedWrapper::getInstance();
        $cacheKey = 'countAllPosts';

        $cachedValue = $memcached->get($cacheKey);
        if ($cachedValue !== false) {
            return (int) $cachedValue;
        }

        $db = self::getDbConnection();

        $stmt = $db->prepare('SELECT COUNT(*) AS totalCount FROM posts WHERE deleted_at IS NULL');
        if (!$stmt) {
            throw new \Exception('Failed to prepare statement: ' . $db->error);
        }

        if (!$stmt->execute()) {
            throw new \Exception('Failed to execute statement: ' . $stmt->error);
        }

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $count = (int) $row['totalCount'];

        $memcached->set($cacheKey, $count, 300);

        return $count;
    }

    /**
     * @throws Exception When failed to prepare statement
     * @throws Exception When failed to execute statement
     */
    public static function countPostsByTag(int $tagId): int
    {
        $memcached = MemcachedWrapper::getInstance();
        $cacheKey = "countPostsByTag_{$tagId}";

        $cachedValue = $memcached->get($cacheKey);
        if ($cachedValue !== false) {
            return (int) $cachedValue;
        }

        $db = self::getDbConnection();

        $stmt = $db->prepare(
            'SELECT COUNT(*) AS totalCount
            FROM posts p
            JOIN post_tag pt ON p.id = pt.post_id
            JOIN tags t ON pt.tag_id = t.id
            WHERE t.id = ?
              AND p.deleted_at IS NULL'
        );
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $db->error);
        }

        $stmt->bind_param('i', $tagId);
        if (!$stmt->execute()) {
            throw new Exception('Failed to execute statement: ' . $stmt->error);
        }

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $count = (int) $row['totalCount'];

        $memcached->set($cacheKey, $count, 300);

        return $count;
    }

    /**
     * @throws Exception When failed to prepare statement
     * @throws Exception When failed to execute statement
     */
    public static function incrementViewCount(int $postId): void
    {
        $db = self::getDbConnection();

        $stmt = $db->prepare(
            'UPDATE posts 
             SET view_count = view_count + 1
             WHERE id = ?'
        );
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $db->error);
        }

        $stmt->bind_param('i', $postId);
        if (!$stmt->execute()) {
            throw new Exception('Failed to execute statement: ' . $stmt->error);
        }
    }

    /**
     * @throws Exception When failed to prepare statement
     * @throws Exception When failed to execute statement
     */
    public static function attachTagsToPost(int $postId, array $tagIds): void
    {
        $db = self::getDbConnection();

        $stmt = $db->prepare('INSERT IGNORE INTO post_tag (post_id, tag_id) VALUES (?, ?)');
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $db->error);
        }

        foreach ($tagIds as $tagId) {
            $stmt->bind_param('ii', $postId, $tagId);
            if (!$stmt->execute()) {
                throw new Exception('Failed to attach tag: ' . $stmt->error);
            }
        }
    }
}
