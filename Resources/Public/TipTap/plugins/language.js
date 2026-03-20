import { f as l, i as r } from "../index-DYaQFPxE.js";
import { d as u } from "../configuration-CsNrhtrS.js";
const i = l.create({
  name: "language",
  inclusive: !1,
  addAttributes() {
    return {
      lang: {
        default: null,
        parseHTML: (n) => n.getAttribute("lang") || n.getAttribute("xml:lang"),
        renderHTML: (n) => typeof n.lang != "string" || n.lang === "" ? {} : {
          lang: n.lang
        }
      },
      xmlLang: {
        default: null,
        parseHTML: (n) => n.getAttribute("xml:lang") || n.getAttribute("lang"),
        renderHTML: (n) => typeof n.xmlLang == "string" && n.xmlLang !== "" ? {
          "xml:lang": n.xmlLang
        } : typeof n.lang == "string" && n.lang !== "" ? {
          "xml:lang": n.lang
        } : {}
      }
    };
  },
  parseHTML() {
    return [
      { tag: "span[lang]" },
      { tag: "span[xml\\:lang]" }
    ];
  },
  renderHTML({ HTMLAttributes: n }) {
    return ["span", r(n), 0];
  }
});
function o() {
  u({
    extensions: [i],
    commands: [
      {
        id: "language",
        label: "Language",
        iconIdentifier: "language",
        position: {
          toolbarGroupId: "formatting",
          bubbleMenuGroupId: !1
        },
        status: {
          isActive: ({ editor: n }) => n.isActive("language"),
          isDisabled: ({ editor: n }) => n.state.selection.empty && !n.isActive("language")
        },
        onExecute: ({ editor: n }) => {
          n.state.selection.empty && n.isActive("language") && n.chain().focus().extendMarkRange("language").run();
          const a = n.getAttributes("language"), t = typeof a.lang == "string" && a.lang !== "" ? a.lang : typeof a.xmlLang == "string" ? a.xmlLang : "", g = window.prompt("Language tag (for example en, fr, de-AT)", t);
          if (g === null)
            return;
          const e = g.trim();
          if (e === "") {
            n.chain().focus().unsetMark("language").run();
            return;
          }
          n.chain().focus().setMark("language", {
            lang: e,
            xmlLang: e
          }).run();
        }
      }
    ]
  });
}
export {
  o as default
};
