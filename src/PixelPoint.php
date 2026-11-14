<?php

declare(strict_types=1);

namespace Survos\StoneContract;

final readonly class PixelPoint implements \JsonSerializable
{
    use JsonValue;

    public function __construct(public float $x, public float $y)
    {
        if (!is_finite($x) || !is_finite($y) || $x < 0 || $y < 0) {
            throw new \InvalidArgumentException('Pixel coordinates must be finite and nonnegative.');
        }
    }
}
