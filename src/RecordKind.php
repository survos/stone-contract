<?php

declare(strict_types=1);

namespace Survos\StoneContract;

enum RecordKind: string
{
    case Marker = 'marker';
    case BurialRecord = 'burialRecord';
    case Capture = 'capture';
}
