<?php

namespace Controllers;

use Exception;
use Helpers\StorageHelper;
use Response\HTTPRenderer;
use Response\Render\ImageRenderer;

class ApiImageController
{
    /**
     * GET api/images/tags/([A-Za-z0-9_-]+)\.(jpg|jpeg|png)
     * @throws Exception If the file download fails
     */
    public function downloadTagImage(string $uniqueString, string $extension): HTTPRenderer
    {
        try {
            $stream = StorageHelper::getObject("$uniqueString.$extension");

            return new ImageRenderer($stream, $extension);
        } catch (Exception $e) {
            throw new Exception('Failed to download file: ' . $e->getMessage());
        }
    }

    /**
     * GET api/images/(\d{4})/(\d{2})/([A-Za-z0-9_-]+)\.(jpg|jpeg|png|gif)
     * @throws Exception If the file download fails
     */
    public function downloadPostImage(string $year, string $month, string $uniqueString, string $extension): HTTPRenderer
    {
        try {
            $stream = StorageHelper::getObject("$year/$month/$uniqueString.$extension");

            return new ImageRenderer($stream, $extension);
        } catch (Exception $e) {
            throw new Exception('Failed to download file: ' . $e->getMessage());
        }
    }
}
