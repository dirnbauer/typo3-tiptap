import { n as e } from "../configuration-BT9xaJ2A.js";
import { Gt as t, c as n } from "../dist-BppL3qHu.js";
//#region src/plugins/language.ts
var r = n.create({
	name: "language",
	inclusive: !1,
	addAttributes() {
		return {
			lang: {
				default: null,
				parseHTML: (e) => e.getAttribute("lang") || e.getAttribute("xml:lang"),
				renderHTML: (e) => typeof e.lang != "string" || e.lang === "" ? {} : { lang: e.lang }
			},
			xmlLang: {
				default: null,
				parseHTML: (e) => e.getAttribute("xml:lang") || e.getAttribute("lang"),
				renderHTML: (e) => typeof e.xmlLang == "string" && e.xmlLang !== "" ? { "xml:lang": e.xmlLang } : typeof e.lang == "string" && e.lang !== "" ? { "xml:lang": e.lang } : {}
			}
		};
	},
	parseHTML() {
		return [{ tag: "span[lang]" }, { tag: "span[xml\\:lang]" }];
	},
	renderHTML({ HTMLAttributes: e }) {
		return [
			"span",
			t(e),
			0
		];
	}
});
function i() {
	e({
		extensions: [r],
		commands: [{
			id: "language",
			label: "Language",
			iconIdentifier: "language",
			position: {
				toolbarGroupId: "formatting",
				bubbleMenuGroupId: !1
			},
			status: {
				isActive: ({ editor: e }) => e.isActive("language"),
				isDisabled: ({ editor: e }) => e.state.selection.empty && !e.isActive("language")
			},
			onExecute: ({ editor: e }) => {
				e.state.selection.empty && e.isActive("language") && e.chain().focus().extendMarkRange("language").run();
				let t = e.getAttributes("language"), n = typeof t.lang == "string" && t.lang !== "" ? t.lang : typeof t.xmlLang == "string" ? t.xmlLang : "", r = window.prompt("Language tag (for example en, fr, de-AT)", n);
				if (r === null) return;
				let i = r.trim();
				if (i === "") {
					e.chain().focus().unsetMark("language").run();
					return;
				}
				e.chain().focus().setMark("language", {
					lang: i,
					xmlLang: i
				}).run();
			}
		}]
	});
}
//#endregion
export { i as default };
