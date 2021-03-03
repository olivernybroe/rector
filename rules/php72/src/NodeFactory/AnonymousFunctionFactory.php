<?php

declare(strict_types=1);

namespace Rector\Php72\NodeFactory;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ClosureUse;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;
use PhpParser\Node\UnionType;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class AnonymousFunctionFactory
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    public function __construct(NodeNameResolver $nodeNameResolver, BetterNodeFinder $betterNodeFinder)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
    }

    /**
     * @param Param[] $params
     * @param Stmt[] $stmts
     * @param Identifier|Name|NullableType|UnionType|null $returnType
     */
    public function create(array $params, array $stmts, ?Node $returnType): Closure
    {
        $useVariables = $this->createUseVariablesFromParams($stmts, $params);

        $anonymousFunctionNode = new Closure();
        $anonymousFunctionNode->params = $params;

        foreach ($useVariables as $useVariable) {
            $anonymousFunctionNode->uses[] = new ClosureUse($useVariable);
        }

        if ($returnType instanceof Node) {
            $anonymousFunctionNode->returnType = $returnType;
        }

        $anonymousFunctionNode->stmts = $stmts;
        return $anonymousFunctionNode;
    }

    /**
     * @param Node[] $nodes
     * @param Param[] $paramNodes
     * @return Variable[]
     */
    private function createUseVariablesFromParams(array $nodes, array $paramNodes): array
    {
        $paramNames = [];
        foreach ($paramNodes as $paramNode) {
            $paramNames[] = $this->nodeNameResolver->getName($paramNode);
        }

        $variableNodes = $this->betterNodeFinder->findInstanceOf($nodes, Variable::class);

        /** @var Variable[] $filteredVariables */
        $filteredVariables = [];
        $alreadyAssignedVariables = [];
        foreach ($variableNodes as $variableNode) {
            // "$this" is allowed
            if ($this->nodeNameResolver-> isName($variableNode, 'this')) {
                continue;
            }

            $variableName = $this->nodeNameResolver->getName($variableNode);
            if ($variableName === null) {
                continue;
            }

            if (in_array($variableName, $paramNames, true)) {
                continue;
            }

            $parentNode = $variableNode->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentNode instanceof Assign) {
                $alreadyAssignedVariables[] = $variableName;
            }

            if ($this->nodeNameResolver->isNames($variableNode, $alreadyAssignedVariables)) {
                continue;
            }

            $filteredVariables[$variableName] = $variableNode;
        }

        return $filteredVariables;
    }
}