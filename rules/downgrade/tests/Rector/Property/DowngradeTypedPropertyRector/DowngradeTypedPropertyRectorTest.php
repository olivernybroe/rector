<?php

declare(strict_types=1);

namespace Rector\Downgrade\Tests\Rector\Property\DowngradeTypedPropertyRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Downgrade\Rector\Property\DowngradeTypedPropertyRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class DowngradeTypedPropertyRectorTest extends AbstractRectorTestCase
{
    /**
     * @requires PHP >= 7.4
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
        return DowngradeTypedPropertyRector::class;
    }
}
