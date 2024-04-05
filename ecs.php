<?php

declare(strict_types=1);

use Elasticr\CodingStandard\Fixer\FinalXmlEntityAwareFixer;
use Elasticr\CodingStandard\Fixer\RemoveSuperfluousSpacesAroundModifiersFixer;
use PhpCsFixer\Fixer\ClassNotation\ClassAttributesSeparationFixer;
use PhpCsFixer\Fixer\ClassNotation\ProtectedToPrivateFixer;
use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;
use PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitMethodCasingFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitStrictFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitTestAnnotationFixer;
use PhpCsFixer\Fixer\ReturnNotation\ReturnAssignmentFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayOpenerAndCloserNewlineFixer;
use Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer;
use Symplify\CodingStandard\Fixer\Spacing\MethodChainingNewlineFixer;
use Symplify\CodingStandard\Fixer\Strict\BlankLineAfterStrictTypesFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $config): void {
    $config->import(SetList::CLEAN_CODE);
    $config->import(SetList::SYMPLIFY);
    $config->import(SetList::COMMON);
    $config->import(SetList::PSR_12);
    $config->import(SetList::ARRAY);
    $config->import(SetList::DOCBLOCK);
    $config->import(SetList::NAMESPACES);
    $config->import(SetList::PHPUNIT);

    $config->rule(RemoveSuperfluousSpacesAroundModifiersFixer::class);
    $config->rule(FinalXmlEntityAwareFixer::class);
    $config->rule(ProtectedToPrivateFixer::class);
    $config->rule(BlankLineAfterStrictTypesFixer::class);
    $config->rule(ClassAttributesSeparationFixer::class);
    $config->rule(DeclareStrictTypesFixer::class);

    $config->ruleWithConfiguration(PhpUnitMethodCasingFixer::class, ['case' => 'snake_case']);
    $config->ruleWithConfiguration(PhpUnitTestAnnotationFixer::class, ['style' => 'annotation']);
    $config->ruleWithConfiguration(LineLengthFixer::class, [
        'line_length' => 180,
        'inline_short_lines' => false,
    ]);

    $config->ruleWithConfiguration(NoSuperfluousPhpdocTagsFixer::class, [
        'allow_mixed' => true,
    ]);

    $config->skip([
        ArrayOpenerAndCloserNewlineFixer::class,
        MethodChainingNewlineFixer::class,
        ReturnAssignmentFixer::class,
        NotOperatorWithSuccessorSpaceFixer::class,
        PhpUnitStrictFixer::class,
        '*/var/*',
    ]);
};
