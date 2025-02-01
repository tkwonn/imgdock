<?php

use Controllers\ApiImageController;
use Controllers\ApiPostController;
use Controllers\PostController;
use Exceptions\HttpException;
use Response\HTTPRenderer;

return [
    // HTML pages
    '' => function (): HTTPRenderer {
        $controller = new PostController();

        return $controller->index();
    },
    'upload' => function (): HTTPRenderer {
        $controller = new PostController();

        return $controller->create();
    },
    '(jpg|jpeg|png|gif)/([A-Za-z0-9_-]{8})' => function (string $extension, string $hashId): HTTPRenderer {
        $controller = new PostController();

        return $controller->show($extension, $hashId);
    },
    'tags/([A-Za-z0-9_]+)' => function (string $tagName): HTTPRenderer {
        $controller = new PostController();

        return $controller->tagIndex($tagName);
    },
    'posts/delete/([A-Za-z0-9_-]{16})' => function (string $deleteKey): HTTPRenderer {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw new HttpException(405, 'It must be GET.');
        }
        $controller = new PostController();

        return $controller->destroy($deleteKey);
    },
    // API endpoints
    'api/posts' => function (): HTTPRenderer {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw new HttpException(405, 'It must be GET.');
        }

        $controller = new ApiPostController();

        return $controller->index();
    },
    'api/posts/create' => function (): HTTPRenderer {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new HttpException(405, 'It must be POST.');
        }
        // Basic validation (file size, type, etc.) is handled by Uppy on the frontend
        if (!isset($_FILES['userfile']) || $_FILES['userfile']['error'] !== UPLOAD_ERR_OK) {
            throw new HttpException(400, 'Upload failed.');
        }
        if (!isset($_POST['title']) || empty(trim($_POST['title']))) {
            throw new HttpException(400, 'Title is required');
        }

        $controller = new ApiPostController();

        return $controller->store();
    },
    'api/images/(tags/[A-Za-z0-9_-]{8})\.(jpg|jpeg|png)' => function (string $uniqueString, string $extension): HTTPRenderer {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw new HttpException(405, 'It must be GET.');
        }

        $controller = new ApiImageController();

        return $controller->downloadTagImage($uniqueString, $extension);
    },
    'api/images/(\d{4})/(\d{2})/([A-Za-z0-9_-]{8})\.(jpg|jpeg|png|gif)' => function (string $year, string $month, string $uniqueString, string $extension): HTTPRenderer {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw new HttpException(405, 'It must be GET.');
        }

        $controller = new ApiImageController();

        return $controller->downloadPostImage($year, $month, $uniqueString, $extension);
    },
];
