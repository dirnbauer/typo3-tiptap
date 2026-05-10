import { Mark, mergeAttributes } from '@tiptap/core'
import { defineTipTapPlugin } from '../configuration.ts'

const Language = Mark.create({
  name: 'language',
  inclusive: false,

  addAttributes() {
    return {
      lang: {
        default: null,
        parseHTML: (element: HTMLElement) => element.getAttribute('lang') || element.getAttribute('xml:lang'),
        renderHTML: (attributes: Record<string, unknown>) => {
          if (typeof attributes.lang !== 'string' || attributes.lang === '') {
            return {}
          }

          return {
            lang: attributes.lang,
          }
        },
      },
      xmlLang: {
        default: null,
        parseHTML: (element: HTMLElement) => element.getAttribute('xml:lang') || element.getAttribute('lang'),
        renderHTML: (attributes: Record<string, unknown>) => {
          if (typeof attributes.xmlLang === 'string' && attributes.xmlLang !== '') {
            return {
              'xml:lang': attributes.xmlLang,
            }
          }

          if (typeof attributes.lang === 'string' && attributes.lang !== '') {
            return {
              'xml:lang': attributes.lang,
            }
          }

          return {}
        },
      },
    }
  },

  parseHTML() {
    return [
      { tag: 'span[lang]' },
      { tag: 'span[xml\\:lang]' },
    ]
  },

  renderHTML({ HTMLAttributes }) {
    return ['span', mergeAttributes(HTMLAttributes), 0]
  },
})

/**
 * This plugin adds support for language spans on inline text selections.
 */
export default function () {
  return defineTipTapPlugin({
    extensions: [Language],
    commands: [
      {
        id: 'language',
        label: 'Language',
        iconIdentifier: 'language',
        position: {
          toolbarGroupId: 'formatting',
          bubbleMenuGroupId: false,
        },
        status: {
          isActive: ({ editor }) => editor.isActive('language'),
          isDisabled: ({ editor }) => editor.state.selection.empty && !editor.isActive('language'),
        },
        onExecute: ({ editor }) => {
          if (editor.state.selection.empty && editor.isActive('language')) {
            editor.chain().focus().extendMarkRange('language').run()
          }

          const currentAttributes = editor.getAttributes('language')
          const defaultLanguage = typeof currentAttributes.lang === 'string' && currentAttributes.lang !== ''
            ? currentAttributes.lang
            : (typeof currentAttributes.xmlLang === 'string' ? currentAttributes.xmlLang : '')

          const languageTag = window.prompt('Language tag (for example en, fr, de-AT)', defaultLanguage)
          if (languageTag === null) {
            return
          }

          const trimmedLanguageTag = languageTag.trim()
          if (trimmedLanguageTag === '') {
            editor
              .chain()
              .focus()
              .unsetMark('language')
              .run()
            return
          }

          editor
            .chain()
            .focus()
            .setMark('language', {
              lang: trimmedLanguageTag,
              xmlLang: trimmedLanguageTag,
            })
            .run()
        },
      },
    ],
  })
}
