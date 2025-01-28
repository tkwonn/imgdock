<?php

namespace Response\Render;

use Response\HTTPRenderer;

class JSONRenderer implements HTTPRenderer
{
    private array $data;
    private int $statusCode;

    public function __construct(array $data, int $statusCode = 200)
    {
        $this->data = $data;
        $this->statusCode = $statusCode;
    }

    public function getFields(): array
    {
        http_response_code($this->statusCode);

        return [
            'Content-Type' => 'application/json; charset=UTF-8',
        ];
    }

    public function getContent(): string
    {
        return json_encode($this->data, JSON_THROW_ON_ERROR);
    }
}
