<?php declare(strict_types=1);

namespace Psalm\LaravelPlugin\ReturnTypeProvider;

use Illuminate\Database\Eloquent\Builder;
use PhpParser\Node\Expr\MethodCall;
use Psalm\CodeLocation;
use Psalm\Context;
use Psalm\LaravelPlugin\ApplicationHelper;
use Psalm\Plugin\Hook\FunctionReturnTypeProviderInterface;
use Psalm\StatementsSource;
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
        dd('big gulps eh?');
        if (!array_key_exists($function_id, self::MAP)) {
            return null;
        }

        $appMethod = self::MAP[$function_id];

        $fake_method_call = new MethodCall(
            new \PhpParser\Node\Expr\Variable('app'),
            $appMethod,
            $call_args
        );

        // proxy to app method to get the actual path
        $returnType = self::executeFakeCall($statements_source, $fake_method_call, $context);

        if (!$returnType) {
            return null;
        }

        return $returnType;
    }

    private static function executeFakeCall(
        \Psalm\Internal\Analyzer\StatementsAnalyzer $statements_analyzer,
        \PhpParser\Node\Expr\MethodCall $fake_method_call,
        Context $context
    ) : ?Union {
        $old_data_provider = $statements_analyzer->node_data;
        $statements_analyzer->node_data = clone $statements_analyzer->node_data;

        $context = clone $context;
        $context->inside_call = true;

        $context->vars_in_scope['$app'] = new Union([
            new TNamedObject(get_class(ApplicationHelper::getApp()))
        ]);

        $suppressed_issues = $statements_analyzer->getSuppressedIssues();

        if (!in_array('PossiblyInvalidMethodCall', $suppressed_issues, true)) {
            $statements_analyzer->addSuppressedIssues(['PossiblyInvalidMethodCall']);
        }

        if (\Psalm\Internal\Analyzer\Statements\Expression\Call\MethodCallAnalyzer::analyze(
                $statements_analyzer,
                $fake_method_call,
                $context,
                false
            ) === false) {
            return null;
        }

        if (!in_array('PossiblyInvalidMethodCall', $suppressed_issues, true)) {
            $statements_analyzer->removeSuppressedIssues(['PossiblyInvalidMethodCall']);
        }

        $returnType = $statements_analyzer->node_data->getType($fake_method_call);

        $statements_analyzer->node_data = $old_data_provider;

        return $returnType;
    }
}
