<?php

declare(strict_types=1);

namespace In2code\Typo3TipTap\Form\Resolver;

use In2code\Typo3TipTap\Form\Element\TipTapTextElement;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Backend\Form\NodeResolverInterface;

#[Autoconfigure(public: true)]
final class RteNodeResolver implements NodeResolverInterface
{
    /**
     * @var array<string, mixed>
     */
    protected array $data = [];

    /**
     * @param array<string, mixed> $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * Returns RichTextElement as class name if RTE widget should be rendered.
     *
     * @return class-string<TipTapTextElement>|null
     */
    public function resolve(): ?string
    {
        $parameterArray = $this->data['parameterArray'] ?? null;
        if (!is_array($parameterArray)) {
            return null;
        }

        if ($this->shouldRenderRichtext($parameterArray) === false) {
            return null;
        }

        return TipTapTextElement::class;
    }

    /**
     * @param array<string, mixed> $parameterArray
     */
    private function shouldRenderRichtext(array $parameterArray): bool
    {
        $fieldConfiguration = $parameterArray['fieldConf'] ?? null;
        if (!is_array($fieldConfiguration)) {
            return false;
        }

        $config = $fieldConfiguration['config'] ?? null;
        if (!is_array($config)) {
            return false;
        }

        // If RTE is enabled for field
        return ((bool)($config['enableRichtext'] ?? false) === true)
            // If RTE config is found (prepared by TcaText data provider)
            && is_array($config['richtextConfiguration'] ?? null)
            // If RTE is not disabled on configuration level
            && !($config['richtextConfiguration']['disabled'] ?? false);
    }
}
