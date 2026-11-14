<?php

declare(strict_types=1);

namespace Survos\StoneContract;

final readonly class OcrLine implements \JsonSerializable
{
    use JsonValue;

    /** @param list<PixelPoint>|null $polygon Null means the provider supplied text without geometry. */
    public function __construct(public string $id, public string $text, public ?array $polygon = null, public ?float $confidence = null)
    {
        if ('' === trim($id)) {
            throw new \InvalidArgumentException('An OCR line requires an ID within its reading.');
        }
        if (null !== $confidence && (!is_finite($confidence) || $confidence < 0 || $confidence > 1)) {
            throw new \InvalidArgumentException('OCR confidence must be between zero and one.');
        }
        if (null !== $polygon) {
            if (!array_is_list($polygon) || count($polygon) < 3) {
                throw new \InvalidArgumentException('A polygon needs at least three ordered points.');
            }
            foreach ($polygon as $point) {
                if (!$point instanceof PixelPoint) {
                    throw new \InvalidArgumentException('Polygon vertices must be PixelPoint values.');
                }
            }
        }
    }
}
