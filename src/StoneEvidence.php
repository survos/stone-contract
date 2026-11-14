<?php

declare(strict_types=1);

namespace Survos\StoneContract;

final readonly class StoneEvidence implements \JsonSerializable
{
    use JsonValue;

    public const string VERSION = '1.0';
    public string $schemaVersion;

    /** @param list<RecordReference> $cemeteries Reviewed cemetery references; empty means unassigned
     * @param list<OcrReading> $readings independent OCR runs, never silently merged
     */
    public function __construct(
        public RecordReference $record,
        public RecordKind $kind,
        public array $cemeteries,
        public array $readings,
    ) {
        $this->schemaVersion = self::VERSION;
        if (!array_is_list($cemeteries) || !array_is_list($readings)) {
            throw new \InvalidArgumentException('Cemeteries and readings must be lists.');
        }
        foreach ($cemeteries as $cemetery) {
            if (!$cemetery instanceof RecordReference || \Survos\DataContracts\Vocabulary\Core::PLACE !== $cemetery->coreCode) {
                throw new \InvalidArgumentException('A cemetery reference must identify a place record.');
            }
        }
        $ids = [];
        foreach ($readings as $reading) {
            if (!$reading instanceof OcrReading || !$reading->frame->page->record->equals($record) || isset($ids[$reading->id])) {
                throw new \InvalidArgumentException('Readings require unique IDs and pages belonging to this record.');
            }
            $ids[$reading->id] = true;
        }
    }
}
