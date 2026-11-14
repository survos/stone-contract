<?php

declare(strict_types=1);

namespace Survos\StoneContract\Tests;

use PHPUnit\Framework\TestCase;
use Survos\DataContracts\Vocabulary\Core;
use Survos\StoneContract\ImageFrame;
use Survos\StoneContract\OcrLine;
use Survos\StoneContract\OcrReading;
use Survos\StoneContract\PageReference;
use Survos\StoneContract\PerspectiveCorrection;
use Survos\StoneContract\PixelPoint;
use Survos\StoneContract\RecordKind;
use Survos\StoneContract\RecordReference;
use Survos\StoneContract\StoneEvidence;

final class ContractTest extends TestCase
{
    private function frame(string $folio = 'mus/rapp'): ImageFrame
    {
        return new ImageFrame('photo-original', new PageReference(new RecordReference($folio, Core::OBJECT, '111'), 1, 0), 's3://example/original.jpg', 4000, 2252);
    }

    public function testFolioIdentityCannotCollide(): void
    {
        self::assertFalse($this->frame()->page->record->equals($this->frame('mus/tombreader')->page->record));
        self::assertFalse((new RecordReference('mus/rapp', Core::PERSON, '111'))->equals($this->frame()->page->record));
    }

    public function testReadingPreservesGeometryTextAndUnknownValues(): void
    {
        $frame = $this->frame();
        $polygon = [new PixelPoint(977.05, 874.98), new PixelPoint(2492.12, 852.34), new PixelPoint(2495.62, 1087.92), new PixelPoint(980.54, 1110.56)];
        $line = new OcrLine('line-1', 'JOHN ROBERT', $polygon, .99394);
        $reading = new OcrReading('tiny-run-1', $frame, 'imagor', "JOHN ROBERT\nJENKINS", [$line], 'tiny');
        $data = json_decode(json_encode(new StoneEvidence($frame->page->record, RecordKind::Marker, [], [$reading]), JSON_THROW_ON_ERROR), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame('1.0', $data['schemaVersion']);
        self::assertSame([], $data['cemeteries']);
        self::assertSame('marker', $data['kind']);
        self::assertSame(977.05, $data['readings'][0]['lines'][0]['polygon'][0]['x']);
        self::assertSame("JOHN ROBERT\nJENKINS", $data['readings'][0]['text']);
        self::assertArrayNotHasKey('sha256', $data['readings'][0]['frame']);
    }

    public function testRejectsOtherFoliosReading(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new StoneEvidence($this->frame()->page->record, RecordKind::Marker, [], [new OcrReading('run', $this->frame('mus/tombreader'), 'imagor', '', [])]);
    }

    public function testRejectsCoordinatesFromWrongSizedRaster(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new OcrReading('run', $this->frame(), 'imagor', 'text', [new OcrLine('line', 'text', [new PixelPoint(0, 0), new PixelPoint(4001, 0), new PixelPoint(0, 10)])]);
    }

    public function testRejectsDuplicateLineIds(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new OcrReading('run', $this->frame(), 'imagor', 'text', [new OcrLine('line', 'a'), new OcrLine('line', 'b')]);
    }

    public function testTextOnlyReadingDoesNotInventBoxes(): void
    {
        $data = (new OcrLine('line', 'Uncertain inscription'))->jsonSerialize();
        self::assertArrayNotHasKey('polygon', $data);
        self::assertArrayNotHasKey('confidence', $data);
    }

    public function testCorrectionPreservesParentAndSelectedCorners(): void
    {
        $corners = [new PixelPoint(100, 200), new PixelPoint(3000, 100), new PixelPoint(2900, 2000), new PixelPoint(200, 2000)];
        $correction = new PerspectiveCorrection('photo-original', $corners);
        $frame = new ImageFrame('photo-corrected-v1', $this->frame()->page, 's3://example/corrected.png', 1600, 1200, perspective: $correction);
        self::assertSame('photo-original', $frame->perspective->parentFrameId);
        self::assertSame($corners, $frame->perspective->sourceCorners);
        self::assertSame(1600, $frame->width);
    }

    public function testRejectsCrossedCorrectionCorners(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new PerspectiveCorrection('parent', [new PixelPoint(0, 0), new PixelPoint(10, 10), new PixelPoint(0, 10), new PixelPoint(10, 0)]);
    }

    public function testRejectsInvalidConfidence(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new OcrLine('line', 'text', confidence: NAN);
    }

    public function testRejectsNegativePageIndex(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new PageReference($this->frame()->page->record, 1, -1);
    }
}
