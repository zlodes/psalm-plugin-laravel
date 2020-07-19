<?php declare(strict_types=1);

namespace Psalm\LaravelPlugin\ReturnTypeProvider;

use PhpParser\Node\Expr\MethodCall;
use Psalm\CodeLocation;
use Psalm\Context;
use Psalm\LaravelPlugin\ApplicationHelper;
use Psalm\Plugin\Hook\FunctionReturnTypeProviderInterface;
use Psalm\StatementsSource;
use Psalm\Type\Atomic\TLiteralString;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Union;

final class PathHelpersReturnTypeProvider implements FunctionReturnTypeProviderInterface
{
    private const MAP = [
        'base_path' => 'basePath'
    ];

    public static function getFunctionIds(): array
    {
        return ['base_path'];
        return array_keys(self::MAP);
    }

    public static function getFunctionReturnType(StatementsSource $statements_source, string $function_id, array $call_args, Context $context, CodeLocation $code_location)
    {
        if (!array_key_exists($function_id, self::MAP)) {
            return null;
        }

        $appMethod = self::MAP[$function_id];

        // we're going to do some dynamic analysis here. Let's just resolve the actual path from the app instance
        $argument = '';

        if (isset($call_args[0])) {
            $argumentType = $call_args[0]->value;
            if (isset($argumentType->value)) {
                $argument = $argumentType->value;
            }
        }
        $path = ApplicationHelper::getApp()->{$appMethod}($argument);

        if (!$path) {
            return null;
        }

        return new Union([
            new TLiteralString($path),
        ]);
    }
}
