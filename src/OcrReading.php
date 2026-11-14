<?php

declare(strict_types=1);

namespace Survos\StoneContract;

final readonly class OcrReading implements \JsonSerializable
{
    use JsonValue;

    /** @param list<OcrLine> $lines Reading order, not inferred from position. */
    public function __construct(
        public string $id,
        public ImageFrame $frame,
        public string $provider,
        public string $text,
        public array $lines,
        public ?string $model = null,
        public ?string $receiptSource = null,
    ) {
        if ('' === trim($id) || '' === trim($provider) || !array_is_list($lines)) {
            throw new \InvalidArgumentException('OCR requires an ID, provider and ordered line list.');
        }
        $ids = [];
        foreach ($lines as $line) {
            if (!$line instanceof OcrLine || isset($ids[$line->id])) {
                throw new \InvalidArgumentException('OCR line IDs must be unique within a reading.');
            }
            $ids[$line->id] = true;
            if (null !== $line->polygon) {
                foreach ($line->polygon as $point) {
                    if (!$frame->contains($point)) {
                        throw new \InvalidArgumentException('OCR geometry must lie inside its declared image frame.');
                    }
                }
            }
        }
    }
}
