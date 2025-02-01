<?php

namespace Response\Render;

use Response\HTTPRenderer;

class RedirectRenderer implements HTTPRenderer
{

    private string $url;
    private int $statusCode;

    public function __construct(string $url, int $statusCode = 301)
    {
        $this->url = $url;
        $this->statusCode = $statusCode;
    }


    public function getFields(): array
    {
        http_response_code($this->statusCode);

        return [
            'Location' => $this->url,
        ];
    }

    public function getContent(): string
    {
        return '';
    }
}