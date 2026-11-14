<?php

declare(strict_types=1);

namespace Survos\StoneContract;

final readonly class ImageFrame implements \JsonSerializable
{
    use JsonValue;

    public function __construct(
        public string $id,
        public PageReference $page,
        public string $source,
        public int $width,
        public int $height,
        public ?string $sha256 = null,
        public ?PerspectiveCorrection $perspective = null,
    ) {
        if ('' === trim($id) || '' === trim($source) || $width < 1 || $height < 1) {
            throw new \InvalidArgumentException('An image frame requires identity, a durable source and positive pixel dimensions.');
        }
        if (null !== $sha256 && !preg_match('/^[a-f0-9]{64}$/D', $sha256)) {
            throw new \InvalidArgumentException('SHA-256 must be lowercase hexadecimal.');
        }
        if ($perspective?->parentFrameId === $id) {
            throw new \InvalidArgumentException('A corrected frame cannot be its own parent.');
        }
    }

    public function contains(PixelPoint $point): bool
    {
        return $point->x <= $this->width && $point->y <= $this->height;
    }
}
