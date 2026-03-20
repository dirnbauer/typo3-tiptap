import type { JSONContent } from '@tiptap/core'
import { Mark, mergeAttributes } from '@tiptap/core'
import { defineTipTapPlugin } from '../configuration.ts'

const Abbreviation = Mark.create({
  name: 'abbreviation',
  inclusive: false,

  addAttributes() {
    return {
      title: {
        default: null,
        parseHTML: (element: HTMLElement) => element.getAttribute('title') || element.getAttribute('data-tooltip'),
        renderHTML: (attributes: Record<string, unknown>) => {
          if (!attributes.title || typeof attributes.title !== 'string') {
            return {}
          }

          return {
            title: attributes.title,
          }
        },
      },
      dataTooltip: {
        default: null,
        parseHTML: (element: HTMLElement) => element.getAttribute('data-tooltip') || element.getAttribute('title'),
        renderHTML: (attributes: Record<string, unknown>) => {
          if (typeof attributes.dataTooltip === 'string' && attributes.dataTooltip !== '') {
            return {
              'data-tooltip': attributes.dataTooltip,
            }
          }

          if (typeof attributes.title === 'string' && attributes.title !== '') {
            return {
              'data-tooltip': attributes.title,
            }
          }

          return {}
        },
      },
    }
  },

  parseHTML() {
    return [
      { tag: 'abbr' },
    ]
  },

  renderHTML({ HTMLAttributes }) {
    return ['abbr', mergeAttributes(HTMLAttributes), 0]
  },
})

function getSelectedText(editor: { state: { doc: { textBetween: (from: number, to: number, separator: string) => string }, selection: { from: number, to: number } } }): string {
  const { from, to } = editor.state.selection
  return editor.state.doc.textBetween(from, to, ' ')
}

function buildAbbreviationContent(text: string, title: string): JSONContent {
  return {
    type: 'text',
    text,
    marks: [
      {
        type: 'abbreviation',
        attrs: {
          title,
          dataTooltip: title,
        },
      },
    ],
  }
}

/**
 * This plugin adds support for semantic abbreviations with tooltip text.
 */
export default function () {
  defineTipTapPlugin({
    extensions: [Abbreviation],
    commands: [
      {
        id: 'abbreviation',
        label: 'Abbreviation',
        iconIdentifier: 'abbreviation',
        position: {
          toolbarGroupId: 'formatting',
          bubbleMenuGroupId: false,
        },
        status: {
          isActive: ({ editor }) => editor.isActive('abbreviation'),
          isDisabled: ({ editor }) => editor.state.selection.empty && !editor.isActive('abbreviation'),
        },
        onExecute: ({ editor }) => {
          if (editor.state.selection.empty && editor.isActive('abbreviation')) {
            editor.chain().focus().extendMarkRange('abbreviation').run()
          }

          const currentAttributes = editor.getAttributes('abbreviation')
          const currentText = getSelectedText(editor)
          const defaultTitle = typeof currentAttributes.title === 'string' && currentAttributes.title !== ''
            ? currentAttributes.title
            : (typeof currentAttributes.dataTooltip === 'string' ? currentAttributes.dataTooltip : '')

          const abbreviationText = window.prompt('Abbreviation', currentText)
          if (abbreviationText === null) {
            return
          }

          const abbreviationTitle = window.prompt('Explanation', defaultTitle)
          if (abbreviationTitle === null) {
            return
          }

          const trimmedText = abbreviationText.trim()
          const trimmedTitle = abbreviationTitle.trim()

          if (trimmedText === '') {
            return
          }

          const { from, to } = editor.state.selection
          const nextContent: JSONContent = trimmedTitle === ''
            ? { type: 'text', text: trimmedText }
            : buildAbbreviationContent(trimmedText, trimmedTitle)

          editor
            .chain()
            .focus()
            .insertContentAt({ from, to }, nextContent)
            .run()
        },
      },
    ],
  })
}
