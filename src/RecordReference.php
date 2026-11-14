<?php

declare(strict_types=1);

namespace Survos\StoneContract;

final readonly class RecordReference implements \JsonSerializable
{
    use JsonValue;

    public function __construct(
        public string $folioCode,
        public string $coreCode,
        public string $localId,
    ) {
        if (!preg_match('~^[a-zA-Z0-9_.-]+/[a-zA-Z0-9_.-]+$~D', $folioCode)
            || !preg_match('/^[a-z][a-z0-9_-]*$/D', $coreCode) || '' === trim($localId)) {
            throw new \InvalidArgumentException('A reference needs a folio code, core code and nonempty local ID.');
        }
    }

    public function equals(self $other): bool
    {
        return $this->folioCode === $other->folioCode && $this->coreCode === $other->coreCode && $this->localId === $other->localId;
    }
}
