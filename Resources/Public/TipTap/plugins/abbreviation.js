import { f as p, i as c } from "../index-DYaQFPxE.js";
import { d as f } from "../configuration-CsNrhtrS.js";
const d = p.create({
  name: "abbreviation",
  inclusive: !1,
  addAttributes() {
    return {
      title: {
        default: null,
        parseHTML: (t) => t.getAttribute("title") || t.getAttribute("data-tooltip"),
        renderHTML: (t) => !t.title || typeof t.title != "string" ? {} : {
          title: t.title
        }
      },
      dataTooltip: {
        default: null,
        parseHTML: (t) => t.getAttribute("data-tooltip") || t.getAttribute("title"),
        renderHTML: (t) => typeof t.dataTooltip == "string" && t.dataTooltip !== "" ? {
          "data-tooltip": t.dataTooltip
        } : typeof t.title == "string" && t.title !== "" ? {
          "data-tooltip": t.title
        } : {}
      }
    };
  },
  parseHTML() {
    return [
      { tag: "abbr" }
    ];
  },
  renderHTML({ HTMLAttributes: t }) {
    return ["abbr", c(t), 0];
  }
});
function T(t) {
  const { from: e, to: i } = t.state.selection;
  return t.state.doc.textBetween(e, i, " ");
}
function v(t, e) {
  return {
    type: "text",
    text: t,
    marks: [
      {
        type: "abbreviation",
        attrs: {
          title: e,
          dataTooltip: e
        }
      }
    ]
  };
}
function g() {
  f({
    extensions: [d],
    commands: [
      {
        id: "abbreviation",
        label: "Abbreviation",
        iconIdentifier: "abbreviation",
        position: {
          toolbarGroupId: "formatting",
          bubbleMenuGroupId: !1
        },
        status: {
          isActive: ({ editor: t }) => t.isActive("abbreviation"),
          isDisabled: ({ editor: t }) => t.state.selection.empty && !t.isActive("abbreviation")
        },
        onExecute: ({ editor: t }) => {
          t.state.selection.empty && t.isActive("abbreviation") && t.chain().focus().extendMarkRange("abbreviation").run();
          const e = t.getAttributes("abbreviation"), i = T(t), l = typeof e.title == "string" && e.title !== "" ? e.title : typeof e.dataTooltip == "string" ? e.dataTooltip : "", o = window.prompt("Abbreviation", i);
          if (o === null)
            return;
          const a = window.prompt("Explanation", l);
          if (a === null)
            return;
          const n = o.trim(), r = a.trim();
          if (n === "")
            return;
          const { from: s, to: b } = t.state.selection, u = r === "" ? { type: "text", text: n } : v(n, r);
          t.chain().focus().insertContentAt({ from: s, to: b }, u).run();
        }
      }
    ]
  });
}
export {
  g as default
};
