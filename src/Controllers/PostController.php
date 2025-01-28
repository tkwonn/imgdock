<?php

namespace Controllers;

use Exception;
use Helpers\DatabaseHelper;
use Helpers\Formatter;
use Helpers\StorageHelper;
use Response\HTTPRenderer;
use Response\Render\HTMLRenderer;

class PostController
{
    private const PAGE_LIMIT = 12;

    /**
     * GET /
     * @throws Exception If failed to retrieve posts
     */
    public function index(): HTTPRenderer
    {
        try {
            $sort = $_GET['sort'] ?? 'newest';
            $page = (int) ($_GET['page'] ?? 1);

            $posts = DatabaseHelper::findAllPosts($sort, $page, self::PAGE_LIMIT);
            $tags = DatabaseHelper::findAllTags();

            foreach ($tags as &$tag) {
                $tag['page_url'] = "/tags/{$tag['name']}";
                $tag['post_count'] = DatabaseHelper::countPostsByTag($tag['id']);
            }
            unset($tag);

            return new HTMLRenderer('home', [
                'posts' => Formatter::formatPosts($posts),
                'tags' => $tags,
            ]);
        } catch (Exception $e) {
            throw new Exception('Failed to retrieve posts: ' . $e->getMessage());
        }
    }

    /**
     * GET /tags/{tagName}
     * @throws Exception If the tag is not found
     */
    public function tagIndex(string $tagName): HTTPRenderer
    {
        try {
            $tag = DatabaseHelper::findTagByName($tagName);
            if (!$tag) {
                throw new Exception("Tag '$tagName' not found");
            }
            $tagId = $tag['id'];

            $sort = $_GET['sort'] ?? 'newest';
            $page = (int) ($_GET['page'] ?? 1);

            $posts = DatabaseHelper::findPostsByTag($tagId, $sort, $page, self::PAGE_LIMIT);

            return new HTMLRenderer('tag', [
                'tag' => $tag,
                'posts' => Formatter::formatPosts($posts),
            ]);
        } catch (Exception $e) {
            throw new Exception('Failed to retrieve tag: ' . $e->getMessage());
        }
    }

    /**
     * GET /upload
     * @throws Exception If failed to retrieve tags
     */
    public function create(): HTTPRenderer
    {
        try {
            $tags = DatabaseHelper::findAllTags();

            return new HTMLRenderer('upload', [
                'tags' => $tags,
            ]);
        } catch (Exception $e) {
            throw new Exception('Failed to retrieve tags: ' . $e->getMessage());
        }
    }

    /**
     * GET /{extension}/{uniqueString}
     * @throws Exception If the post is not found or failed to increment view count
     */
    public function show(string $extension, string $uniqueString): HTTPRenderer
    {
        try {
            $post = DatabaseHelper::findPostByFileName($extension, $uniqueString);

            DatabaseHelper::incrementViewCount($post['id']);
            $post['size'] = Formatter::bytesToHumanReadable($post['size']);
            $post['created_at'] = Formatter::toRelativeTime($post['created_at']);

            return new HTMLRenderer('post', [
                'post' => $post,
            ]);
        } catch (Exception $e) {
            throw new Exception('Failed to retrieve post: ' . $e->getMessage());
        }
    }

    /**
     * GET /posts/delete/{deleteKey}
     * @throws Exception If the post is not found or failed to delete
     */
    public function destroy(string $deleteKey): HTTPRenderer
    {
        try {
            $post = DatabaseHelper::findPostByDeleteKey($deleteKey);

            StorageHelper::deleteObject($post['s3_key']);
            DatabaseHelper::deletePost($post['id']);

            return new HTMLRenderer('delete', [
                'title' => $post['title'],
            ]);
        } catch (Exception $e) {
            throw new Exception('Failed to delete post: ' . $e->getMessage());
        }
    }
}
