<?php

declare(strict_types=1);

namespace In2code\Typo3TipTap\Tests\Unit\Form\Resolver;

use In2code\Typo3TipTap\Form\Element\TipTapTextElement;
use In2code\Typo3TipTap\Form\Resolver\RteNodeResolver;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RteNodeResolverTest extends TestCase
{
    #[Test]
    public function resolveReturnsTipTapElementForEnabledRichtextFields(): void
    {
        $subject = new RteNodeResolver();
        $subject->setData([
            'parameterArray' => [
                'fieldConf' => [
                    'config' => [
                        'enableRichtext' => true,
                        'richtextConfiguration' => [
                            'disabled' => false,
                        ],
                    ],
                ],
            ],
        ]);

        self::assertSame(TipTapTextElement::class, $subject->resolve());
    }

    #[Test]
    public function resolveReturnsNullForDisabledRichtextFields(): void
    {
        $subject = new RteNodeResolver();
        $subject->setData([
            'parameterArray' => [
                'fieldConf' => [
                    'config' => [
                        'enableRichtext' => false,
                    ],
                ],
            ],
        ]);

        self::assertNull($subject->resolve());
    }
}
