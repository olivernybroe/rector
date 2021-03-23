<?php

declare(strict_types=1);

namespace Rector\Restoration\Rector\ClassLike;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use Rector\Core\Rector\AbstractRector;
use Rector\FileSystemRector\ValueObject\MovedFileWithContent;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see \Rector\Tests\Restoration\Rector\ClassLike\UpdateFileNameByClassNameFileSystemRector\UpdateFileNameByClassNameFileSystemRectorTest
 */
final class UpdateFileNameByClassNameFileSystemRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Rename file to respect class name', [
            new CodeSample(
                <<<'CODE_SAMPLE'
// app/SomeClass.php
class AnotherClass
{
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
// app/AnotherClass.php
class AnotherClass
{
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassLike::class];
    }

    /**
     * @param ClassLike $node
     */
    public function refactor(Node $node): ?Node
    {
        $className = $this->getName($node);
        if ($className === null) {
            return null;
        }

        $classShortName = $this->nodeNameResolver->getShortName($className);

        $smartFileInfo = $node->getAttribute(SmartFileInfo::class);
        if ($smartFileInfo === null) {
            return null;
        }

        // matches
        if ($classShortName === $smartFileInfo->getBasenameWithoutSuffix()) {
            return null;
        }

        // no match → rename file
        $newFileLocation = $smartFileInfo->getPath() . DIRECTORY_SEPARATOR . $classShortName . '.php';
        $this->removedAndAddedFilesCollector->addMovedFile(new MovedFileWithContent($smartFileInfo, $newFileLocation));

        return null;
    }
}
