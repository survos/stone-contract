<?php

declare(strict_types=1);

namespace Survos\StoneContract;

final readonly class PerspectiveCorrection implements \JsonSerializable
{
    use JsonValue;

    /** @param list<PixelPoint> $sourceCorners Top-left, top-right, bottom-right, bottom-left in the parent frame. */
    public function __construct(public string $parentFrameId, public array $sourceCorners)
    {
        if ('' === trim($parentFrameId) || !array_is_list($sourceCorners) || 4 !== count($sourceCorners)) {
            throw new \InvalidArgumentException('Perspective correction requires a parent frame and four ordered corners.');
        }
        foreach ($sourceCorners as $point) {
            if (!$point instanceof PixelPoint) {
                throw new \InvalidArgumentException('Corners must be PixelPoint values.');
            }
        }
        $sign = null;
        for ($i = 0; $i < 4; ++$i) {
            $a = $sourceCorners[$i];
            $b = $sourceCorners[($i + 1) % 4];
            $c = $sourceCorners[($i + 2) % 4];
            $cross = ($b->x - $a->x) * ($c->y - $b->y) - ($b->y - $a->y) * ($c->x - $b->x);
            if (0.0 === $cross || (null !== $sign && ($cross > 0) !== $sign)) {
                throw new \InvalidArgumentException('Corners must form a convex, nondegenerate quadrilateral in perimeter order.');
            }
            $sign = $cross > 0;
        }
    }
}
