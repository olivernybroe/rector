<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;

class SkipCallbackUse
{
    public function run(ClassMethod $node, array $items)
    {
        array_walk($node->stmts, function (Node $stmt) use ($node) {
            return $stmt;
        });

        function someFunction($node)
        {
        }
    }
}

?>
