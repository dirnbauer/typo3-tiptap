<?php

declare(strict_types=1);

namespace In2code\Typo3TipTap\Form\Element;

use In2code\Typo3TipTap\Editor\ConfigurationService;
use In2code\Typo3TipTap\Exception\MissingEditorConfigurationException;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class TipTapTextElement extends AbstractFormElement
{
    /**
     * @var array<string, array<string, mixed>>
     */
    protected $defaultFieldInformation = [
        'tcaDescription' => [
            'renderType' => 'tcaDescription',
        ],
    ];

    /**
     * @var array<string, array<string, mixed>>
     */
    protected $defaultFieldWizard = [
        'localizationStateSelector' => [
            'renderType' => 'localizationStateSelector',
        ],
        'otherLanguageContent' => [
            'renderType' => 'otherLanguageContent',
            'after' => [
                'localizationStateSelector',
            ],
        ],
        'defaultLanguageDifferences' => [
            'renderType' => 'defaultLanguageDifferences',
            'after' => [
                'otherLanguageContent',
            ],
        ],
    ];

    public function __construct(
        protected readonly ConfigurationService $configurationService,
    ) {}

    /**
     * Renders the ckeditor element
     *
     * @return array<string, mixed>
     * @throws MissingEditorConfigurationException
     * @throws RouteNotFoundException
     */
    public function render(): array
    {
        $resultArray = $this->initializeResultArray();
        $parameterArray = is_array($this->data['parameterArray'] ?? null) ? $this->data['parameterArray'] : [];
        $fieldConfiguration = is_array($parameterArray['fieldConf'] ?? null) ? $parameterArray['fieldConf'] : [];
        $config = is_array($fieldConfiguration['config'] ?? null) ? $fieldConfiguration['config'] : [];

        $fieldId = $this->sanitizeFieldId((string)$parameterArray['itemFormElName']);
        $itemFormElementName = (string)$parameterArray['itemFormElName'];

        $value = (string)($parameterArray['itemFormElValue'] ?? '');

        $fieldInformationResult = $this->renderFieldInformation();
        $fieldInformationHtml = $fieldInformationResult['html'];
        $resultArray = $this->mergeChildReturnIntoExistingResult($resultArray, $fieldInformationResult, false);

        $fieldControlResult = $this->renderFieldControl();
        $fieldControlHtml = $fieldControlResult['html'];
        $resultArray = $this->mergeChildReturnIntoExistingResult($resultArray, $fieldControlResult, false);

        $fieldWizardResult = $this->renderFieldWizard();
        $fieldWizardHtml = $fieldWizardResult['html'];
        $resultArray = $this->mergeChildReturnIntoExistingResult($resultArray, $fieldWizardResult, false);

        $editorOptions = $this->configurationService->getConfiguration($config, $this->data);
        $editorOptions['enableDebugMode'] = false;
        $editorOptions['id'] = $fieldId . 'tiptap';
        $editorAttributes = GeneralUtility::implodeAttributes([
            'options' => GeneralUtility::jsonEncodeForHtmlAttribute($editorOptions, false),
        ], true);

        $textareaAttributes = GeneralUtility::implodeAttributes([
            'id' => $fieldId,
            'name' => $itemFormElementName,
            'rows' => '18',
            'class' => 'form-control t3js-formengine-input',
            'data-formengine-validation-rules' => $this->getValidationDataAsJsonString($config),
            'style' => 'display:none;',
        ], true);

        $html = [];
        $html[] = $fieldInformationHtml;
        $html[] = '<div class="form-control-wrap">';
        $html[] = '<div class="form-wizards-wrap">';
        $html[] = '<div class="form-wizards-item-element">';
        $html[] = '<editor-tiptap ' . $editorAttributes . '>';
        $html[] = '<textarea ' . $textareaAttributes . '>';
        $html[] = htmlspecialchars($value);
        $html[] = '</textarea>';
        $html[] = '</editor-tiptap>';
        $html[] = '</div>';
        if (!empty($fieldControlHtml)) {
            $html[] = '<div class="form-wizards-item-aside form-wizards-item-aside--field-control">';
            $html[] = '<div class="btn-group">';
            $html[] = $fieldControlHtml;
            $html[] = '</div>';
            $html[] = '</div>';
        }
        if (!empty($fieldWizardHtml)) {
            $html[] = '<div class="form-wizards-item-bottom">';
            $html[] = $fieldWizardHtml;
            $html[] = '</div>';
        }
        $html[] = '</div>';
        $html[] = '</div>';

        $resultArray['html'] = $this->wrapWithFieldsetAndLegend(
            '<div class="formengine-field-item t3js-formengine-field-item">' . implode("\n", $html) . '</div>'
        );
        $resultArray['javaScriptModules'][] = JavaScriptModuleInstruction::create('@typo3-tiptap/tiptap/index.js');

        return $resultArray;
    }

    private function sanitizeFieldId(string $itemFormElementName): string
    {
        $fieldId = (string)preg_replace('/[^a-zA-Z0-9_:-]/', '_', $itemFormElementName);
        return htmlspecialchars((string)preg_replace('/^[^a-zA-Z]/', 'x', $fieldId));
    }
}
