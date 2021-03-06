<?php

declare(strict_types=1);

namespace Rector\Php70\Tests\Rector\Assign\ListSplitStringRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php70\Rector\Assign\ListSplitStringRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ListSplitStringRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return ListSplitStringRector::class;
    }
}
