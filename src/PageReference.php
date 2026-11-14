<?php

declare(strict_types=1);

namespace Survos\StoneContract;

final readonly class PageReference implements \JsonSerializable
{
    use JsonValue;

    public function __construct(
        public RecordReference $record,
        public int $seq,
        public int $pageIndex,
        public ?string $mediaId = null,
    ) {
        if ($seq < 1 || $pageIndex < 0 || (null !== $mediaId && '' === trim($mediaId))) {
            throw new \InvalidArgumentException('Page seq is one-based; pageIndex is zero-based; a supplied mediaId cannot be empty.');
        }
    }
}
