import { n as e } from "../configuration-BT9xaJ2A.js";
import { Gt as t, c as n } from "../dist-BppL3qHu.js";
//#region src/plugins/abbreviation.ts
var r = n.create({
	name: "abbreviation",
	inclusive: !1,
	addAttributes() {
		return {
			title: {
				default: null,
				parseHTML: (e) => e.getAttribute("title") || e.getAttribute("data-tooltip"),
				renderHTML: (e) => !e.title || typeof e.title != "string" ? {} : { title: e.title }
			},
			dataTooltip: {
				default: null,
				parseHTML: (e) => e.getAttribute("data-tooltip") || e.getAttribute("title"),
				renderHTML: (e) => typeof e.dataTooltip == "string" && e.dataTooltip !== "" ? { "data-tooltip": e.dataTooltip } : typeof e.title == "string" && e.title !== "" ? { "data-tooltip": e.title } : {}
			}
		};
	},
	parseHTML() {
		return [{ tag: "abbr" }];
	},
	renderHTML({ HTMLAttributes: e }) {
		return [
			"abbr",
			t(e),
			0
		];
	}
});
function i(e) {
	let { from: t, to: n } = e.state.selection;
	return e.state.doc.textBetween(t, n, " ");
}
function a(e, t) {
	return {
		type: "text",
		text: e,
		marks: [{
			type: "abbreviation",
			attrs: {
				title: t,
				dataTooltip: t
			}
		}]
	};
}
function o() {
	e({
		extensions: [r],
		commands: [{
			id: "abbreviation",
			label: "Abbreviation",
			iconIdentifier: "abbreviation",
			position: {
				toolbarGroupId: "formatting",
				bubbleMenuGroupId: !1
			},
			status: {
				isActive: ({ editor: e }) => e.isActive("abbreviation"),
				isDisabled: ({ editor: e }) => e.state.selection.empty && !e.isActive("abbreviation")
			},
			onExecute: ({ editor: e }) => {
				e.state.selection.empty && e.isActive("abbreviation") && e.chain().focus().extendMarkRange("abbreviation").run();
				let t = e.getAttributes("abbreviation"), n = i(e), r = typeof t.title == "string" && t.title !== "" ? t.title : typeof t.dataTooltip == "string" ? t.dataTooltip : "", o = window.prompt("Abbreviation", n);
				if (o === null) return;
				let s = window.prompt("Explanation", r);
				if (s === null) return;
				let c = o.trim(), l = s.trim();
				if (c === "") return;
				let { from: u, to: d } = e.state.selection, f = l === "" ? {
					type: "text",
					text: c
				} : a(c, l);
				e.chain().focus().insertContentAt({
					from: u,
					to: d
				}, f).run();
			}
		}]
	});
}
//#endregion
export { o as default };
