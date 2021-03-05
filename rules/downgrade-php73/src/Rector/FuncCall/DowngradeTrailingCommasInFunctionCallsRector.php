<?php

declare(strict_types=1);

namespace Rector\DowngradePhp73\Rector\FuncCall;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\Core\Application\TokensByFilePathStorage;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see \Rector\DowngradePhp73\Tests\Rector\FuncCall\DowngradeTrailingCommasInFunctionCallsRector\DowngradeTrailingCommasInFunctionCallsRectorTest
 */
final class DowngradeTrailingCommasInFunctionCallsRector extends AbstractRector
{
    /**
     * @var TokensByFilePathStorage
     */
    private $tokensByFilePathStorage;

    public function __construct(TokensByFilePathStorage $tokensByFilePathStorage)
    {
        $this->tokensByFilePathStorage = $tokensByFilePathStorage;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove trailing commas in function calls', [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(string $value)
    {
        $compacted = compact(
            'posts',
            'units',
        );
    }
}
CODE_SAMPLE
                    , <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(string $value)
    {
        $compacted = compact(
            'posts',
            'units'
        );
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class, MethodCall::class, StaticCall::class];
    }

    /**
     * @param FuncCall|MethodCall|StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->args) {
            $lastArgumentPosition = array_key_last($node->args);

            $last = $node->args[$lastArgumentPosition];
            if (! $this->isNodeFollowedByComma($last)) {
                return null;
            }

            // remove comma
            $last->setAttribute(AttributeKey::FUNC_ARGS_TRAILING_COMMA, false);
            $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        }

        return $node;
    }

    private function isNodeFollowedByComma(Arg $arg): bool
    {
        $smartFileInfo = $arg->getAttribute(AttributeKey::FILE_INFO);
        if (! $smartFileInfo instanceof SmartFileInfo) {
            return false;
        }

        if (! $this->tokensByFilePathStorage->hasForFileInfo($smartFileInfo)) {
            return false;
        }

        $parsedStmtsAndTokens = $this->tokensByFilePathStorage->getForFileInfo($smartFileInfo);
        $oldTokens = $parsedStmtsAndTokens->getOldTokens();

        $nextTokenPosition = $arg->getEndTokenPos() + 1;
        while (isset($oldTokens[$nextTokenPosition])) {
            $currentToken = $oldTokens[$nextTokenPosition];

            // only space
            if (is_array($currentToken) || Strings::match($currentToken, '#\s+#')) {
                ++$nextTokenPosition;
                continue;
            }

            // without comma
            if ($currentToken === ')') {
                return false;
            }

            break;
        }

        return true;
    }
}
