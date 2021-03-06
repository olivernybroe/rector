<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\PropertyProperty;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\PropertyProperty;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use function strtolower;

/**
 * @see \Rector\DeadCode\Tests\Rector\PropertyProperty\RemoveNullPropertyInitializationRector\RemoveNullPropertyInitializationRectorTest
 */
final class RemoveNullPropertyInitializationRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove initialization with null value from property declarations', [
            new CodeSample(
                <<<'PHP'
class SunshineCommand extends ParentClassWithNewConstructor
{
    private $myVar = null;
}
PHP
                ,
                <<<'PHP'
class SunshineCommand extends ParentClassWithNewConstructor
{
    private $myVar;
}
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [PropertyProperty::class];
    }

    /**
     * @param PropertyProperty $node
     */
    public function refactor(Node $node): ?Node
    {
        $defaultValueNode = $node->default;
        if ($defaultValueNode === null) {
            return null;
        }

        if (! ($defaultValueNode instanceof ConstFetch)) {
            return null;
        }

        if (strtolower((string) $defaultValueNode->name) !== 'null') {
            return null;
        }

        if ($node->getAttribute(AttributeKey::PREVIOUS_NODE) instanceof NullableType) {
            return null;
        }

        $node->default = null;

        return $node;
    }
}
