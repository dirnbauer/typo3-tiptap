<?php

declare(strict_types=1);

namespace In2code\Typo3TipTap\Editor;

use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use function dirname;
use function is_array;
use function is_file;
use function is_scalar;
use function is_string;
use function reset;
use function trim;

final class ConfigurationService
{
    private const DEFAULT_CONFIGURATION_FILE = '/Configuration/RTE/Full.yaml';

    public function __construct(
        private readonly UriBuilder $uriBuilder,
    ) {}

    /**
     * @param array<string, mixed> $fieldConfiguration
     * @param array<string, mixed> $elementData
     * @return array<string, mixed>
     */
    public function getConfiguration(array $fieldConfiguration, array $elementData): array
    {
        $editorConfiguration = $this->extractEditorConfiguration($fieldConfiguration);

        $editorConfiguration['contentCss'] = $this->resolveStylePaths($editorConfiguration['contentCss'] ?? []);
        $editorConfiguration['linkBrowserUrl'] = $this->buildWizardUrl($elementData);
        return $editorConfiguration;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function buildWizardUrl(array $data): string
    {
        $urlParameters = [
            'P' => [
                'table' => $this->normalizeString($data['tableName'] ?? ''),
                'uid' => $this->extractDatabaseRowUid($data),
                'fieldName' => $this->normalizeString($data['fieldName'] ?? ''),
                'recordType' => $this->normalizeString($data['recordTypeValue'] ?? ''),
                'pid' => $this->normalizeInt($data['effectivePid'] ?? 0),
            ],
        ];

        $richtextConfigurationName = $this->extractRichtextConfigurationName($data);
        if ($richtextConfigurationName !== null) {
            $urlParameters['P']['richtextConfigurationName'] = $richtextConfigurationName;
        }

        return (string)$this->uriBuilder->buildUriFromRoute('typo3-tiptap_wizard_browse_links', $urlParameters);
    }

    /**
     * @param mixed $styles
     * @return list<string>
     */
    private function resolveStylePaths(mixed $styles): array
    {
        if (!is_array($styles)) {
            return [];
        }

        $resolvedStyles = [];

        foreach ($styles as $style) {
            if (is_string($style) === false) {
                continue;
            }

            $absoluteFilePath = GeneralUtility::getFileAbsFileName($style);
            if ($absoluteFilePath === '') {
                continue;
            }

            $resolvedStyles[] = PathUtility::getAbsoluteWebPath($absoluteFilePath);
        }

        return array_values(array_filter($resolvedStyles, static fn(string $stylePath): bool => $stylePath !== ''));
    }

    /**
     * @param array<string, mixed> $fieldConfiguration
     * @return array<string, mixed>
     */
    private function extractEditorConfiguration(array $fieldConfiguration): array
    {
        $richtextConfiguration = $fieldConfiguration['richtextConfiguration'] ?? null;
        if (!is_array($richtextConfiguration)) {
            return $this->getDefaultEditorConfiguration();
        }

        $editorConfiguration = $richtextConfiguration['editor'] ?? null;
        if (!is_array($editorConfiguration)) {
            return $this->getDefaultEditorConfiguration();
        }

        $tiptapConfiguration = $editorConfiguration['tiptap'] ?? null;
        if (!is_array($tiptapConfiguration)) {
            return $this->getDefaultEditorConfiguration();
        }

        $config = $tiptapConfiguration['config'] ?? null;
        if (!is_array($config)) {
            return $this->getDefaultEditorConfiguration();
        }

        return $config;
    }

    /**
     * @return array<string, mixed>
     */
    private function getDefaultEditorConfiguration(): array
    {
        $configurationFile = dirname(__DIR__, 2) . self::DEFAULT_CONFIGURATION_FILE;
        if (!is_file($configurationFile)) {
            return [
                'uiMode' => 'toolbar',
                'enableContentDragAndDrop' => true,
                'plugins' => [],
            ];
        }

        $configuration = Yaml::parseFile($configurationFile);
        $config = is_array($configuration)
            ? ($configuration['editor']['tiptap']['config'] ?? null)
            : null;

        if (!is_array($config)) {
            return [
                'uiMode' => 'toolbar',
                'enableContentDragAndDrop' => true,
                'plugins' => [],
            ];
        }

        return $config;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function extractDatabaseRowUid(array $data): int
    {
        $databaseRow = $data['databaseRow'] ?? null;
        if (!is_array($databaseRow)) {
            return 0;
        }

        return $this->normalizeInt($databaseRow['uid'] ?? 0);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function extractRichtextConfigurationName(array $data): ?string
    {
        $parameterArray = $data['parameterArray'] ?? null;
        if (!is_array($parameterArray)) {
            return null;
        }

        $fieldConf = $parameterArray['fieldConf'] ?? null;
        if (!is_array($fieldConf)) {
            return null;
        }

        $config = $fieldConf['config'] ?? null;
        if (!is_array($config)) {
            return null;
        }

        $richtextConfigurationName = $this->normalizeString($config['richtextConfigurationName'] ?? '');
        return $richtextConfigurationName !== '' ? $richtextConfigurationName : null;
    }

    private function normalizeInt(mixed $value): int
    {
        if (is_array($value)) {
            $value = reset($value);
        }

        return is_scalar($value) ? (int)$value : 0;
    }

    private function normalizeString(mixed $value): string
    {
        if (is_array($value)) {
            $value = reset($value);
        }

        return is_scalar($value) ? trim((string)$value) : '';
    }
}
