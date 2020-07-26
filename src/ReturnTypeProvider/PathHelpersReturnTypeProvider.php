<?php declare(strict_types=1);

namespace Psalm\LaravelPlugin\ReturnTypeProvider;

use Psalm\CodeLocation;
use Psalm\Context;
use Psalm\LaravelPlugin\ApplicationHelper;
use Psalm\Plugin\Hook\FunctionReturnTypeProviderInterface;
use Psalm\Plugin\Hook\MethodReturnTypeProviderInterface;
use Psalm\StatementsSource;
use Psalm\Type\Atomic\TLiteralString;
use Psalm\Type\Union;

final class PathHelpersReturnTypeProvider implements FunctionReturnTypeProviderInterface, MethodReturnTypeProviderInterface
{
    private const GLOBAL_FUNCTION_TO_APP_INSTANCE_METHOD_MAP = [
        'base_path' => 'basepath',
    ];

    public static function getFunctionIds(): array
    {
        return array_keys(self::GLOBAL_FUNCTION_TO_APP_INSTANCE_METHOD_MAP);
    }

    public static function getFunctionReturnType(StatementsSource $statements_source, string $function_id, array $call_args, Context $context, CodeLocation $code_location)
    {
        if (!array_key_exists($function_id, self::GLOBAL_FUNCTION_TO_APP_INSTANCE_METHOD_MAP)) {
            return null;
        }

        $appMethod = self::GLOBAL_FUNCTION_TO_APP_INSTANCE_METHOD_MAP[$function_id];
        $path = self::resolvePath($call_args, $appMethod);

        if (!$path) {
            return null;
        }

        return new Union([
            new TLiteralString($path),
        ]);
    }

    public static function getClassLikeNames(): array
    {
        return [
            \Illuminate\Contracts\Foundation\Application::class,
            get_class(ApplicationHelper::getApp()),
        ];
    }

    public static function getMethodReturnType(StatementsSource $source, string $fq_classlike_name, string $method_name_lowercase, array $call_args, Context $context, CodeLocation $code_location, array $template_type_parameters = null, string $called_fq_classlike_name = null, string $called_method_name_lowercase = null)
    {
        if (!in_array($method_name_lowercase, self::GLOBAL_FUNCTION_TO_APP_INSTANCE_METHOD_MAP)) {
            return null;
        }

        $path = self::resolvePath($call_args, $method_name_lowercase);

        if (!$path) {
            return null;
        }

        return new Union([
            new TLiteralString($path),
        ]);
    }

    private static function resolvePath(array $call_args, string $appMethod): string
    {
        // we're going to do some dynamic analysis here. Let's just resolve the actual path from the app instance
        $argument = '';

        if (isset($call_args[0])) {
            $argumentType = $call_args[0]->value;
            if (isset($argumentType->value)) {
                $argument = $argumentType->value;
            }
        }

        return ApplicationHelper::getApp()->{$appMethod}($argument);
    }
}
