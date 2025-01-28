<?php

namespace Response\Render;

use Psr\Http\Message\StreamInterface;
use Response\HTTPRenderer;

class ImageRenderer implements HTTPRenderer
{
    public function __construct(
        private StreamInterface $stream,
        private string $contentType,
    ) {
    }

    public function getFields(): array
    {
        $mimeType = match($this->contentType) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            default => 'application/octet-stream',
        };

        return [
            'Content-Type' => $mimeType,
            'Content-Length' => $this->stream->getSize(),
        ];
    }

    public function getContent(): string
    {
        $this->stream->rewind();

        return $this->stream->getContents();
    }
}
