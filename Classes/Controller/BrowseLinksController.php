<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace In2code\Typo3TipTap\Controller;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Backend\Controller\AbstractLinkBrowserController;
use TYPO3\CMS\Core\Configuration\Richtext;
use TYPO3\CMS\Core\LinkHandling\Exception\UnknownLinkHandlerException;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewInterface;

/**
 * Extended controller for link browser
 * @internal This is a specific Backend Controller implementation and is not considered part of the Public TYPO3 API.
 */
#[Autoconfigure(public: true, shared: false)]
final class BrowseLinksController extends AbstractLinkBrowserController
{
    protected string $editorId = '';

    /**
     * TYPO3 language code of the content language
     */
    protected string $contentsLanguage = '';

    /**
     * @var array<string, mixed>
     */
    protected array $buttonConfig = [];

    /**
     * @var array<string, mixed>
     */
    protected array $thisConfig = [];

    /**
     * @var array<string, string>
     */
    protected array $classesAnchorDefault = [];

    /**
     * @var array<string, string>
     */
    protected array $classesAnchorDefaultTarget = [];

    /**
     * @var array<string, string>
     */
    protected array $classesAnchorJSOptions = [];

    protected string $defaultLinkTarget = '';
    protected string $siteUrl = '';

    public function __construct(
        protected readonly LinkService $linkService,
        protected readonly Richtext $richtext,
        protected readonly FlashMessageService $flashMessageService,
    ) {}

    /**
     * This is only used by RTE currently.
     *
     * @return array<string, mixed>
     */
    public function getConfiguration(): array
    {
        return $this->buttonConfig;
    }

    /**
     * @param array<string, mixed>|null $overrides
     * @return array{act: string, P: array<string, mixed>, editorId: string, contentsLanguage: string}
     */
    public function getUrlParameters(?array $overrides = null): array
    {
        $act = is_string($overrides['act'] ?? null) ? $overrides['act'] : $this->displayedLinkHandlerId;
        $parameters = is_array($overrides['P'] ?? null) ? $overrides['P'] : $this->parameters;

        return [
            'act' => $act,
            'P' => $parameters,
            'editorId' => $this->editorId,
            'contentsLanguage' => $this->contentsLanguage,
        ];
    }

    protected function initDocumentTemplate(): void
    {
        $this->pageRenderer->getJavaScriptRenderer()->addJavaScriptModuleInstruction(
            JavaScriptModuleInstruction::create('@typo3-tiptap/tiptap/rte-link-browser.js')
                ->invoke('initialize', $this->editorId)
        );
    }

    protected function getCurrentPageId(): int
    {
        return (int)$this->parameters['pid'];
    }

    protected function initVariables(ServerRequestInterface $request): void
    {
        parent::initVariables($request);

        /** @var array<string, mixed> $queryParameters */
        $queryParameters = $request->getQueryParams();
        $normalizedParams = $request->getAttribute('normalizedParams');
        $this->siteUrl = is_object($normalizedParams) && method_exists($normalizedParams, 'getSiteUrl')
            ? (string)$normalizedParams->getSiteUrl()
            : '';
        $queryParameterPayload = is_array($queryParameters['P'] ?? null) ? $queryParameters['P'] : [];
        $currentLinkParts = $queryParameterPayload['curUrl'] ?? [];
        $this->currentLinkParts = is_array($currentLinkParts) ? $currentLinkParts : [];
        $this->editorId = is_string($queryParameters['editorId'] ?? null) ? $queryParameters['editorId'] : '';
        $this->contentsLanguage = is_string($queryParameters['contentsLanguage'] ?? null) ? $queryParameters['contentsLanguage'] : '';
        $tcaFieldConf = [
            'enableRichtext' => true,
            'richtextConfiguration' => is_string($this->parameters['richtextConfigurationName'] ?? null)
                ? $this->parameters['richtextConfigurationName']
                : null,
        ];
        $this->thisConfig = $this->richtext->getConfiguration(
            (string)($this->parameters['table'] ?? ''),
            (string)($this->parameters['fieldName'] ?? ''),
            (int)$this->parameters['pid'],
            (string)($this->parameters['recordType'] ?? ''),
            $tcaFieldConf
        );
        $buttonConfig = $this->thisConfig['buttons']['link'] ?? [];
        $this->buttonConfig = is_array($buttonConfig) ? $buttonConfig : [];
    }

    protected function initCurrentUrl(): void
    {
        if (empty($this->currentLinkParts)) {
            return;
        }
        $currentUrl = $this->currentLinkParts['url'] ?? null;
        if (is_string($currentUrl) && $currentUrl !== '') {
            try {
                $data = $this->linkService->resolve($currentUrl);
                $this->currentLinkParts['type'] = $data['type'];
                unset($data['type']);
                $this->currentLinkParts['url'] = $data;
                $resolvedUrl = $data;
                if (is_string($resolvedUrl['parameters'] ?? null) && $resolvedUrl['parameters'] !== '') {
                    $this->currentLinkParts['params'] = '&' . $resolvedUrl['parameters'];
                }
            } catch (UnknownLinkHandlerException $e) {
                $this->flashMessageService->getMessageQueueByIdentifier()->enqueue(
                    new FlashMessage(message: $e->getMessage(), severity: ContextualFeedbackSeverity::ERROR)
                );
            }
        }
        parent::initCurrentUrl();
    }

    protected function renderLinkAttributeFields(ViewInterface $view): string
    {
        $buttonProperties = $this->arrayValue($this->buttonConfig, 'properties');
        $classProperties = $this->arrayValue($buttonProperties, 'class');
        $allowedClasses = $classProperties['allowedClasses'] ?? null;

        // Processing the classes configuration
        if (!empty($allowedClasses)) {
            $classesAnchorArray = is_array($allowedClasses)
                ? array_values(array_filter($allowedClasses, static fn(mixed $class): bool => is_string($class) && $class !== ''))
                : (is_string($allowedClasses) ? GeneralUtility::trimExplode(',', $allowedClasses, true) : []);
            // Collecting allowed classes and configured default values
            $classesAnchor = [
                'all' => [],
            ];

            if (is_array($this->thisConfig['classesAnchor'] ?? null)) {
                foreach ($this->thisConfig['classesAnchor'] as $conf) {
                    if (!is_array($conf)) {
                        continue;
                    }

                    $configuredClass = is_string($conf['class'] ?? null) ? $conf['class'] : null;
                    $configuredType = is_string($conf['type'] ?? null) ? $conf['type'] : null;

                    if ($configuredClass !== null && in_array($configuredClass, $classesAnchorArray, true)) {
                        $classesAnchor['all'][] = $configuredClass;
                        if ($configuredType === $this->displayedLinkHandlerId) {
                            $classesAnchor[$configuredType][] = $configuredClass;
                            $linkHandlerClassProperties = $this->getLinkHandlerClassProperties($configuredType);
                            if (($linkHandlerClassProperties['default'] ?? null) === $configuredClass) {
                                $this->classesAnchorDefault[$configuredType] = $configuredClass;
                                if (isset($conf['target'])) {
                                    $this->classesAnchorDefaultTarget[$configuredType] = trim((string)$conf['target']);
                                }
                            }
                        }
                    }
                }
            }

            $linkClass = $this->linkAttributeValues['class'] ?? '';
            if ($linkClass !== '') {
                $currentLinkClassIsAllowed = true;
                if (!in_array($linkClass, $classesAnchorArray, true)) {
                    // Current class is not a globally allowed class
                    $currentLinkClassIsAllowed = false;
                }
                if (
                    isset($classesAnchor[$this->displayedLinkHandlerId]) &&
                    in_array($linkClass, $classesAnchor['all'], true) &&
                    !in_array($linkClass, $classesAnchor[$this->displayedLinkHandlerId], true)
                ) {
                    // Current class is limited to specific link types but not available in current link type
                    $currentLinkClassIsAllowed = false;
                }

                if (!$currentLinkClassIsAllowed) {
                    $this->classesAnchorJSOptions[$this->displayedLinkHandlerId] ??= '';
                    // Add a dummy option that preserved the current class value (despite being invalid)
                    // in order to prevent unintentional modification of assigned classes.
                    $this->classesAnchorJSOptions[$this->displayedLinkHandlerId] .= sprintf(
                        '<option selected="selected" value="%s">%s</option>',
                        htmlspecialchars($linkClass),
                        htmlspecialchars(
                            @sprintf(
                                '[ ' . $this->getLanguageService()->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.noMatchingValue') . ' ]',
                                $linkClass
                            )
                        )
                    );
                }
            }

            // Constructing the class selector options
            foreach ($classesAnchorArray as $class) {
                if (
                    !in_array($class, $classesAnchor['all'], true)
                    || (
                        in_array($class, $classesAnchor['all'], true)
                        && is_array($classesAnchor[$this->displayedLinkHandlerId] ?? null)
                        && in_array($class, $classesAnchor[$this->displayedLinkHandlerId])
                    )
                ) {
                    $selected = '';
                    if (
                        (($this->linkAttributeValues['class'] ?? false) === $class)
                        || ($this->classesAnchorDefault[$this->displayedLinkHandlerId] ?? false) === $class
                    ) {
                        $selected = 'selected="selected"';
                    }
                    $configuredClass = $this->arrayValue($this->arrayValue($this->thisConfig, 'classes'), $class);
                    $className = $this->stringValue($configuredClass, 'name');
                    $classLabel = $className !== ''
                        ? $this->getPageConfigLabel($className, false)
                        : $class;
                    $classStyle = $this->stringValue($configuredClass, 'value');

                    $this->classesAnchorJSOptions[$this->displayedLinkHandlerId] ??= '';
                    $this->classesAnchorJSOptions[$this->displayedLinkHandlerId] .= '<option ' . $selected . ' value="' . htmlspecialchars($class) . '"'
                        . ($classStyle ? ' style="' . htmlspecialchars($classStyle) . '"' : '')
                        . '>' . htmlspecialchars($classLabel)
                        . '</option>';
                }
            }
            if (
                ($this->classesAnchorJSOptions[$this->displayedLinkHandlerId] ?? false)
                && !(
                    (bool)($classProperties['required'] ?? false)
                    || (bool)($this->getLinkHandlerClassProperties($this->displayedLinkHandlerId)['required'] ?? false)
                )
            ) {
                $selected = '';
                if (!($this->linkAttributeValues['class'] ?? false) && !($this->classesAnchorDefault[$this->displayedLinkHandlerId] ?? false)) {
                    $selected = 'selected="selected"';
                }
                $this->classesAnchorJSOptions[$this->displayedLinkHandlerId] = '<option ' . $selected . ' value=""></option>' . $this->classesAnchorJSOptions[$this->displayedLinkHandlerId];
            }
        }
        // Default target
        $this->defaultLinkTarget = ($this->classesAnchorDefault[$this->displayedLinkHandlerId] ?? false) && ($this->classesAnchorDefaultTarget[$this->displayedLinkHandlerId] ?? false)
            ? $this->classesAnchorDefaultTarget[$this->displayedLinkHandlerId]
            : $this->resolveDefaultLinkTarget();

        return parent::renderLinkAttributeFields($view);
    }

    /**
     * Localize a label obtained from Page TSConfig
     *
     * @param string $string The label to be localized
     * @param bool $JScharCode If it needs to be converted to an array of char numbers
     * @return string Localized string
     */
    protected function getPageConfigLabel(string $string, bool $JScharCode = true): string
    {
        $label = $this->getLanguageService()->sL(trim($string));
        $label = str_replace(['\\\'', '"'], ['\'', '\\"'], $label);
        return $JScharCode ? GeneralUtility::quoteJSvalue($label) : $label;
    }

    protected function renderCurrentUrl(ViewInterface $view): void
    {
        $view->assign('removeCurrentLink', true);
        parent::renderCurrentUrl($view);
    }

    /**
     * @return string[]
     */
    protected function getAllowedItems(): array
    {
        $allowedItems = parent::getAllowedItems();

        if (is_string($this->thisConfig['allowedTypes'] ?? null)) {
            $allowedItems = array_intersect($allowedItems, GeneralUtility::trimExplode(',', $this->thisConfig['allowedTypes'], true));
        } elseif (is_string($this->thisConfig['blindLinkOptions'] ?? null)) {
            // @todo Deprecate this option
            $allowedItems = array_diff($allowedItems, GeneralUtility::trimExplode(',', $this->thisConfig['blindLinkOptions'], true));
        }

        $options = $this->arrayValue($this->buttonConfig, 'options');
        if (is_string($options['removeItems'] ?? null) && $options['removeItems'] !== '') {
            $allowedItems = array_diff($allowedItems, GeneralUtility::trimExplode(',', $options['removeItems'], true));
        }

        return $allowedItems;
    }

    /**
     * @return string[]
     */
    protected function getAllowedLinkAttributes(): array
    {
        $allowedLinkAttributes = parent::getAllowedLinkAttributes();

        if (is_string($this->thisConfig['allowedOptions'] ?? null)) {
            $allowedLinkAttributes = array_intersect($allowedLinkAttributes, GeneralUtility::trimExplode(',', $this->thisConfig['allowedOptions'], true));
        } elseif (is_string($this->thisConfig['blindLinkFields'] ?? null)) {
            // @todo Deprecate this option
            $allowedLinkAttributes = array_diff($allowedLinkAttributes, GeneralUtility::trimExplode(',', $this->thisConfig['blindLinkFields'], true));
        }

        return $allowedLinkAttributes;
    }

    /**
     * Create an array of link attribute field rendering definitions
     *
     * @return string[]
     */
    protected function getLinkAttributeFieldDefinitions(): array
    {
        $fieldRenderingDefinitions = parent::getLinkAttributeFieldDefinitions();
        $fieldRenderingDefinitions['class'] = $this->getClassField();
        $fieldRenderingDefinitions['target'] = $this->getTargetField();
        $fieldRenderingDefinitions['rel'] = $this->getRelField();
        $queryParameterSelector = $this->arrayValue($this->buttonConfig, 'queryParametersSelector');
        if (empty($queryParameterSelector['enabled'])) {
            unset($fieldRenderingDefinitions['params']);
        }
        return $fieldRenderingDefinitions;
    }

    protected function getRelField(): string
    {
        $relAttributeConfiguration = $this->arrayValue($this->buttonConfig, 'relAttribute');
        if (empty($relAttributeConfiguration['enabled'])) {
            return '';
        }

        $currentRel = '';
        if ($this->displayedLinkHandler === $this->currentLinkHandler
            && !empty($this->currentLinkParts)
            && is_string($this->linkAttributeValues['rel'] ?? null)
        ) {
            $currentRel = $this->linkAttributeValues['rel'];
        }

        return '
            <div class="element-browser-form-group">
                <label for="lrel" class="form-label">' .
                    htmlspecialchars($this->getLanguageService()->sL('LLL:EXT:backend/Resources/Private/Language/locallang_browse_links.xlf:linkRelationship')) .
                '</label>
                <input type="text" name="lrel" class="form-control" value="' . htmlspecialchars($currentRel) . '" />
            </div>
            ';
    }

    protected function getTargetField(): string
    {
        $targetSelectorConfig = [];
        if (is_array($this->buttonConfig['targetSelector'] ?? null)) {
            $targetSelectorConfig = $this->buttonConfig['targetSelector'];
        }
        $target = !empty($this->linkAttributeValues['target']) ? $this->linkAttributeValues['target'] : $this->defaultLinkTarget;
        $lang = $this->getLanguageService();

        $disabled = $targetSelectorConfig['disabled'] ?? false;
        if ($disabled) {
            return '';
        }

        return '
            <div class="element-browser-form-group">
                <label for="ltarget" class="form-label">
                    ' . htmlspecialchars($lang->sL('LLL:EXT:backend/Resources/Private/Language/locallang_browse_links.xlf:target')) . '
                </label>
                <span class="input-group">
                    <input id="ltarget" type="text" name="ltarget" class="t3js-linkTarget form-control"
                        value="' . htmlspecialchars($target) . '" />
                    <select name="ltarget_type" class="t3js-targetPreselect form-select">
                        <option value=""></option>
                        <option value="_top">' . htmlspecialchars($lang->sL('LLL:EXT:backend/Resources/Private/Language/locallang_browse_links.xlf:top')) . '</option>
                        <option value="_blank">' . htmlspecialchars($lang->sL('LLL:EXT:backend/Resources/Private/Language/locallang_browse_links.xlf:newWindow')) . '</option>
                    </select>
                </span>
            </div>';
    }

    /**
     * Return html code for the class selector
     *
     * @return string the html code to be added to the form
     */
    protected function getClassField(): string
    {
        if (!isset($this->classesAnchorJSOptions[$this->displayedLinkHandlerId])) {
            return '';
        }

        return '
            <div class="element-browser-form-group">
                <label for="lclass" class="form-label">
                    ' . htmlspecialchars($this->getLanguageService()->sL('LLL:EXT:backend/Resources/Private/Language/locallang_browse_links.xlf:class')) . '
                </label>
                <select id="lclass" name="lclass" class="t3js-class-selector form-select">
                    ' . $this->classesAnchorJSOptions[$this->displayedLinkHandlerId] . '
                </select>
            </div>
        ';
    }

    /**
     * @return string[] Array of body-tag attributes
     */
    protected function getBodyTagAttributes(): array
    {
        $parameters = parent::getBodyTagAttributes();
        $parameters['data-site-url'] = $this->siteUrl;
        $parameters['data-default-link-target'] = $this->defaultLinkTarget;
        return $parameters;
    }

    /**
     * @param array<string, mixed> $source
     * @return array<string, mixed>
     */
    private function arrayValue(array $source, string $key): array
    {
        $value = $source[$key] ?? null;
        return is_array($value) ? $value : [];
    }

    /**
     * @param array<string, mixed> $source
     */
    private function stringValue(array $source, string $key): string
    {
        $value = $source[$key] ?? null;
        return is_string($value) ? $value : '';
    }

    /**
     * @return array<string, mixed>
     */
    private function getLinkHandlerClassProperties(string $handlerIdentifier): array
    {
        $linkHandlerConfiguration = $this->arrayValue($this->buttonConfig, $handlerIdentifier);
        $linkHandlerProperties = $this->arrayValue($linkHandlerConfiguration, 'properties');
        return $this->arrayValue($linkHandlerProperties, 'class');
    }

    private function resolveDefaultLinkTarget(): string
    {
        $linkHandlerConfiguration = $this->arrayValue($this->buttonConfig, $this->displayedLinkHandlerId);
        $linkHandlerProperties = $this->arrayValue($linkHandlerConfiguration, 'properties');
        $linkHandlerTargetProperties = $this->arrayValue($linkHandlerProperties, 'target');
        if (is_string($linkHandlerTargetProperties['default'] ?? null)) {
            return $linkHandlerTargetProperties['default'];
        }

        $buttonProperties = $this->arrayValue($this->buttonConfig, 'properties');
        $buttonTargetProperties = $this->arrayValue($buttonProperties, 'target');
        return is_string($buttonTargetProperties['default'] ?? null) ? $buttonTargetProperties['default'] : '';
    }
}
