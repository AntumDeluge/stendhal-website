
// Stendhal's repo URL
const repoPrefix = "https://raw.githubusercontent.com/arianne/stendhal/";

// weapon classes that will be parsed
const includes = ["all", "axes", "clubs", "ranged", "swords", "whips"];

const main = {
	// fetched data
	data: {},

	odd: false,

	// FIXME: need to wait for all item categories to load before sorting
	getSorted: function() {
		const sortBy = this.data["sort"] || "name";
		const data = this.data["weapons"];
		const weapons = [];
		for (const name in data) {
			const def = data[name];
			def["name"] = name;
			weapons.push(def);
		}

		const descending = this.data["descending"];
		weapons.sort((objA, objB) => {
			const valueA = objA[sortBy];
			const valueB = objB[sortBy];
			if (valueA < valueB) {
				return descending ? 1 : -1;
			}
			if (valueA > valueB) {
				return descending ? -1 : 1;
			}
			return 0;
		});

		return weapons;
	},

	loadWeapons: function() {
		const weapons = this.getSorted();

		for (const properties of weapons) {
			const classList = ["cell"];
			if (this.odd) {
				classList.push("odd-cell");
			}
			for (const prop in properties) {
				let value = properties[prop];
				if (prop === "image") {
					const src = repoPrefix + "master/data/sprites/items/" + properties["class"] + "/" + value + ".png";
					const image = new Image();
					image.src = src;

					// container element to restrict image dimensions
					const container = document.createElement("div");
					container.classList.add("icon-container");
					container.appendChild(image);

					element = document.createElement("div");
					element.classList.add(...classList, "left-cell");
					element.appendChild(container);
					document.getElementById("image").appendChild(element);
					continue;
				}
				if (Array.isArray(value)) {
					value = value.join(", ");
				} else {
					value = "" + value;
				}
				element = document.createElement("div");
				element.classList.add(...classList);
				if (prop === "special") {
					element.classList.add("right-cell");
				}
				if (value.length === 0) {
					element.innerHTML = "&nbsp;";
				} else {
					element.innerHTML = "&nbsp;&nbsp;&nbsp;&nbsp;" + value;
				}
				document.getElementById(prop).appendChild(element);
			}
			this.odd = !this.odd;
		}
	}
};

/**
 * Shows a message for debugging.
 *
 * @param {string} level
 *   Debugging message level. One of "error", "warn", "info", or "debug".
 * @param {string=} msg
 *   Text content. If omitted, `msg` is value of `level` & `level` defaults to "info".
 */
function debug(level, msg) {
	if (typeof(msg) === "undefined") {
		msg = level
		level = "info";
	}
	if (["error", "warn", "info", "debug"].indexOf(level) < 0) {
		level = "info";
	}
	level = level.toLowerCase();
	let color = "black";
	if (level === "error") {
		color = "red";
	} else if (level === "warn") {
		color = "orange";
	} else if (level === "debug") {
		color = "green";
	}
	const element = document.getElementById("debug");
	element.style.color = color;
	element.innerText = level.toUpperCase() + ": " + msg;
}

/**
 * Fetches contents of a file from Stendhal Git repo.
 *
 * TODO: use cache
 *
 * @param {string} path
 *   Path to file relative to repo root.
 * @param {Function} callback
 *   Function called when data is ready.
 * @param {string} [branch="master"]
 *   Branch on which desired version is located.
 * @param {string} [mime="text/plain"]
 *   Target file MIME type.
 */
async function fetchText(path, callback, branch="master", mime="text/plain") {
	const url = repoPrefix + branch + "/" + path;
	try {
		const res = await fetch(url, {
			method: "GET",
			headers: {
				"Content-Type": mime
			}
		});
		const text = await res.text();
		callback(text);
	} catch (e) {
		console.error(e);
		debug("error", e);
	}
}

function parseNumberDefault(value, def) {
	const res = Number.parseFloat(value);
	if (Number.isNaN(res) || !Number.isFinite(res)) {
		return def;
	}
	return res;
}

function parseAttributeValue(attributes, tag, def=0) {
	const element = attributes.getElementsByTagName(tag)[0];
	if (!element) {
		return def;
	}
	const value = element.getAttribute("value");
	return parseNumberDefault(value, def);
}

function parseAttributeString(attributes, tag) {
	const element = attributes.getElementsByTagName(tag)[0];
	if (!element) {
		return undefined;
	}
	return element.getAttribute("value");
}

function parseWeapons(content) {
	const sortBy = main.data["sort"];
	const weapons = {};

	const xml = new DOMParser().parseFromString(content, "text/xml");
	const items = xml.getElementsByTagName("item");
	for (let idx = 0; idx < items.length; idx++) {
		const item = items[idx];
		const name = item.getAttribute("name");

		const typeInfo = item.getElementsByTagName("type")[0];
		const properties = {
			class: typeInfo.getAttribute("class"),
			image: typeInfo.getAttribute("subclass")
		};
		const attributes = item.getElementsByTagName("attributes")[0];
		properties.level = parseAttributeValue(attributes, "min_level");
		properties.rate = parseAttributeValue(attributes, "rate");
		properties.atk = parseAttributeValue(attributes, "atk");
		properties.dpt = Math.round((properties.atk / properties.rate) * 100) / 100;
		properties.special = [];
		const nature = parseAttributeString(attributes, "damagetype");
		if (typeof(nature) !== "undefined") {
			properties.special.push(nature);
		}
		const statusAttack = parseAttributeString(attributes, "statusattack");
		if (typeof(statusAttack) !== "undefined") {
			if (statusAttack.includes("poison") || statusAttack.includes("venom")) {
				properties.special.push("poison");
			} else if (statusAttack.includes(",")) {
				properties.special.push(statusAttack.split(",")[1]);
			} else {
				properties.special.push(statusAttack);
			}
		}
		const lifesteal = parseAttributeValue(attributes, "lifesteal");
		if (lifesteal !== 0) {
			properties.special.push("lifesteal=" + lifesteal);
		}
		const def = parseAttributeValue(attributes, "def");
		if (def !== 0) {
			properties.special.push("def=" + def);
		}
		const range = parseAttributeValue(attributes, "range");
		if (range !== 0) {
			properties.special.push("range=" + range);
		}

		weapons[name] = properties;
	}

	main.data["weapons"] = weapons;
	main.loadWeapons();
}

async function fetchWeaponsForClass() {
	let className = main.data["class"];
	if (typeof(className) === "undefined") {
		const msg = "No class selected";
		console.error(msg);
		debug("error", msg);
		return;
	}

	// TODO: use release branch
	if (className !== "all") {
		fetchText("data/conf/items/" + className + ".xml", parseWeapons);
		return;
	}

	const select = document.getElementById("classes");
	const options = select.options;
	// skip first index since "all" is not an actual weapon class
	for (let idx = 1; idx < options.length; idx++) {
		className = options[idx].value;
		fetchText("data/conf/items/" + className + ".xml", parseWeapons);
	}
}


function onClassSelected() {
	fetchWeaponsForClass();
}

function selectClass(className, sortBy=undefined) {
	const prevSelected = main.data["class"];
	const select = document.getElementById("classes");
	for (let idx = 0; idx < select.options.length; idx++) {
		if (select.options[idx].value === className) {
			main.data["class"] = className;
			select.selectedIndex = idx;
			break;
		}
	}
	if (main.data["class"] !== prevSelected) {
		onClassSelected();
	}
}

/**
 * Ensures using LF line endings.
 *
 * @param {string} content
 *   Fetched text content.
 * @returns {string}
 *   Normalized text content.
 */
function normalize(content) {
	return content.replaceAll("\r\n", "\n").replaceAll("\r", "\n");
}

/**
 * Parses version from fetched properties file.
 *
 * @param {string} content
 *   Properties file text contents.
 */
function parseVersion(content) {
	content = normalize(content);
	for (const li of content.split("\n")) {
		if (li.startsWith("version\.old")) {
			main.data["version"] = [];
			for (const v of li.split("=")[1].trim().split(".")) {
				main.data["version"].push(Number.parseInt(v, 10));
			}
			break;
		}
	}
	if (typeof(main.data["version"]) !== "undefined") {
		let versionString = "";
		for (const v of main.data["version"]) {
			if (versionString.length > 0) {
				versionString += ".";
			}
			versionString += v;
		}
		document.getElementById("title").innerText = "Stendhal " + versionString;
	}
}

/**
 * Parses weapon classes from fetched data.
 *
 * @param {string} content
 *   Weapons XML config data.
 */
function parseClasses(content) {
	content = normalize(content);

	const classNames = [];
	for (let li of content.split("\n")) {
		li = li.replace(/^\t/, "");
		if (li.startsWith("<group uri=\"items/")) {
			const className = li.replace(/<group uri="items\//, "").replace(/\.xml.*$/, "");
			if (includes.indexOf(className) > -1) {
				classNames.push(className);
			}
		}
	}

	const select = document.getElementById("classes");
	for (const className of classNames) {
		const opt = document.createElement("option");
		opt.value = className
		opt.innerText = className;
		select.appendChild(opt);
	}

	const params = new URLSearchParams(window.location.search);
	let className = params.get("class");
	if (includes.indexOf(className) < 0) {
		if (typeof(className) === "string") {
			const msg = "Unknown weapon class: " + className;
			console.error(msg);
			debug("error", msg);
		}
		// default to show all weapons
		className = "all";
	}
	selectClass(className);
}

/**
 * Fetches current release version from properties file.
 */
async function fetchVersion() {
	fetchText("build.ant.properties", parseVersion);
}

/**
 * Fetches configured weapons classes.
 */
async function fetchClasses() {
	// TODO: use release branch
	fetchText("data/conf/items.xml", parseClasses); //, "master", "application/xml");
}

main.populate = function() {
	const params = new URLSearchParams(window.location.search);
	const sortBy = params.get("sort");
	if (sortBy) {
		main.data["sort"] = sortBy;
		main.data["descending"] = params.get("descending") === "true";
	}
	fetchVersion();
	fetchClasses();
};

main.reload = function(query=undefined) {
	let target = window.location.href;
	if (query) {
		target = target.split("?")[0] + "?" + query;
	}
	window.location.href = target;
}

main.init = function() {
	document.getElementById("classes").addEventListener("change", (evt) => {
		const select = evt.target;
		const className = select.options[select.selectedIndex].value;
		// reload page to update for changes
		const params = new URLSearchParams("class=" + className);
		params.set("sort", this.data["sort"] || "name");
		params.set("descending", this.data["descending"] || "false");
		main.reload(params.toString());
	});

	for (const col of ["name", "class", "level", "rate", "atk", "dpt", "special"]) {
		const header = document.getElementById(col).firstElementChild;
		header.classList.add("sortable");
		header.addEventListener("click", (evt) => {
			const sortBy = evt.currentTarget.parentElement.id;
			const params = new URLSearchParams(window.location.search);
			if (sortBy) {
				params.set("sort", sortBy);
			}
			let descending = false;
			if (sortBy === this.data["sort"]) {
				descending = !this.data["descending"];
			}
			params.set("descending", ""+descending);
			main.reload(params.toString());
		});
	}

	this.populate();
};

// entry point
document.addEventListener("DOMContentLoaded", () => {
	main.init();
});
