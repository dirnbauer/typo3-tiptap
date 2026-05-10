<?php

declare(strict_types=1);

namespace In2code\Typo3TipTap\Tests\Unit\Editor;

use In2code\Typo3TipTap\Editor\ConfigurationService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Backend\Routing\UriBuilder;

final class ConfigurationServiceTest extends TestCase
{
    private UriBuilder&MockObject $uriBuilder;
    private ConfigurationService $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->uriBuilder = $this->createMock(UriBuilder::class);
        $this->subject = new ConfigurationService($this->uriBuilder);
    }

    #[Test]
    public function getConfigurationBuildsWorkspaceAwareLinkBrowserUrl(): void
    {
        $fieldConfiguration = [
            'richtextConfiguration' => [
                'editor' => [
                    'tiptap' => [
                        'config' => [
                            'plugins' => [],
                        ],
                    ],
                ],
            ],
        ];
        $elementData = [
            'tableName' => 'tt_content',
            'fieldName' => 'bodytext',
            'recordTypeValue' => 'textmedia',
            'effectivePid' => 42,
            'databaseRow' => [
                'uid' => 99,
            ],
            'parameterArray' => [
                'fieldConf' => [
                    'config' => [
                        'richtextConfigurationName' => 'custom_tiptap',
                    ],
                ],
            ],
        ];

        $this->uriBuilder
            ->expects(self::once())
            ->method('buildUriFromRoute')
            ->with(
                'typo3-tiptap_wizard_browse_links',
                [
                    'P' => [
                        'table' => 'tt_content',
                        'uid' => 99,
                        'fieldName' => 'bodytext',
                        'recordType' => 'textmedia',
                        'pid' => 42,
                        'richtextConfigurationName' => 'custom_tiptap',
                    ],
                ]
            )
            ->willReturn('/typo3/tiptap/wizard');

        $configuration = $this->subject->getConfiguration($fieldConfiguration, $elementData);

        self::assertSame('/typo3/tiptap/wizard', $configuration['linkBrowserUrl']);
    }

    #[Test]
    public function getConfigurationUsesFullPresetIfTipTapConfigIsMissing(): void
    {
        $this->uriBuilder
            ->expects(self::once())
            ->method('buildUriFromRoute')
            ->willReturn('/typo3/tiptap/wizard');

        $configuration = $this->subject->getConfiguration([], []);

        self::assertSame('toolbar', $configuration['uiMode']);
        self::assertNotEmpty($configuration['plugins']);
        self::assertSame('/typo3/tiptap/wizard', $configuration['linkBrowserUrl']);
    }
}
