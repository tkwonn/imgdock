<?php

namespace Controllers;

use Exception;
use Helpers\DatabaseHelper;
use Helpers\Formatter;
use Helpers\StorageHelper;
use Helpers\UrlSafeString;
use Response\HTTPRenderer;
use Response\Render\JSONRenderer;

class ApiPostController
{
    private const PAGE_LIMIT = 12;
    private const UNIQUE_STRING_LENGTH = 8;
    private const DELETE_KEY_LENGTH = 16;

    /**
     * GET /api/posts
     * @throws Exception If failed to retrieve posts
     */
    public function index(): HTTPRenderer
    {
        try {
            $sort = $_GET['sort'] ?? 'newest';
            $page = (int) ($_GET['page'] ?? 1);
            $tagId = isset($_GET['tag']) ? (int) $_GET['tag'] : null;

            $posts = $tagId
                ? DatabaseHelper::findPostsByTag($tagId, $sort, $page, self::PAGE_LIMIT)
                : DatabaseHelper::findAllPosts($sort, $page, self::PAGE_LIMIT);

            $total = $tagId
                ? DatabaseHelper::countPostsByTag($tagId)
                : DatabaseHelper::countAllPosts();

            return new JSONRenderer([
                'posts' => Formatter::formatPosts($posts),
                'total' => $total,
            ]);
        } catch (Exception $e) {
            throw new Exception('Failed to retrieve posts: ' . $e->getMessage());
        }
    }

    /**
     * POST /api/posts
     * @throws Exception If the file upload or db insert fails
     */
    public function store(): HTTPRenderer
    {
        try {
            $file = $_FILES['userfile'];
            $title = $_POST['title'];
            $description = $_POST['description'] ?? '';
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $size = (int) $file['size'];
            $uniqueString = UrlSafeString::random(self::UNIQUE_STRING_LENGTH);
            $deleteKey = UrlSafeString::random(self::DELETE_KEY_LENGTH);

            $s3Key = StorageHelper::putObject($file, $uniqueString, $extension);
            $postId = DatabaseHelper::createPost($title, $description, $extension, $size, $s3Key, $deleteKey);

            if (!empty($_POST['tags'])) {
                $tags = array_map('intval', explode(',', $_POST['tags']));
                DatabaseHelper::attachTagsToPost($postId, $tags);
            }

            return new JSONRenderer([
                'id' => $postId,
                'post_url' => "/$extension/$uniqueString",
                'delete_url' => "/posts/delete/$deleteKey",
            ], 201);
        } catch (Exception $e) {
            throw new Exception('Failed to store post: ' . $e->getMessage());
        }
    }
}
