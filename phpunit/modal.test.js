const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const { join } = require('node:path');
const { test } = require('node:test');
const vm = require('node:vm');

function element(attributes = {}) {
	const attrs = { ...attributes };
	const listeners = {};
	const classes = new Set((attrs.class || '').split(/\s+/).filter(Boolean));
	return {
		listeners,
		textContent: '',
		checked: false,
		markup: '',
		getAttribute: (name) => name === 'class' ? [...classes].join(' ') : (attrs[name] ?? null),
		hasAttribute: (name) => Object.hasOwn(attrs, name),
		setAttribute: (name, value) => { attrs[name] = String(value); },
		removeAttribute: (name) => { delete attrs[name]; },
		classList: {
			contains: (name) => classes.has(name),
			add: (...names) => names.forEach((name) => classes.add(name)),
			remove: (...names) => names.forEach((name) => classes.delete(name)),
		},
		addEventListener: (name, listener) => { listeners[name] = listener; },
		insertAdjacentHTML(_, html) { this.markup += html; },
		closest(selector) {
			if (selector.startsWith('a[')) {
				return this.hasAttribute('data-wzlw-external') || this.hasAttribute('data-wzlw-blank') || this.hasAttribute('data-wzlw-download') ? this : null;
			}
			return selector.startsWith('.') && classes.has(selector.slice(1)) ? this : null;
		},
		focus() {},
	};
}

function boot(script, attributes, overrides = {}) {
	const link = element(attributes);
	const modal = element({ hidden: '' });
	const title = element();
	const message = element();
	const displayUrl = element();
	const proceed = element();
	const cancel = element();
	const dismiss = element();
	const nodes = {
		'#wzlw-modal-title': title,
		'#wzlw-modal-message': message,
		'.wzlw-modal-url-value': displayUrl,
		'[data-wzlw-continue]': proceed,
		'.wzlw-modal-cancel': cancel,
		'[data-wzlw-dismiss]': dismiss,
	};
	modal.querySelector = (selector) => nodes[selector];
	modal.querySelectorAll = (selector) => selector === '[data-wzlw-close]' ? [cancel] : [cancel, proceed];
	const listeners = {};
	const opened = [];
	const location = { href: 'https://site.test/blog/post/' };
	const document = {
		baseURI: location.href,
		readyState: 'complete',
		body: { children: [modal], classList: element().classList },
		getElementById: () => modal,
		querySelectorAll: () => link.classList.contains('wzlw-processed') ? [] : [link],
		addEventListener: (name, callback) => { listeners[name] = callback; },
	};
	const context = {
		document,
		window: { location, open: (...args) => opened.push(args) },
		wzlwSettings: {
			siteHost: 'site.test',
			warningMethod: 'inline_modal',
			scope: 'external',
			excludedDomains: [],
			forceExternalClass: ['wzlw-force-external'],
			affiliateClass: ['wzlw-affiliate'],
			redirectBaseUrl: 'https://site.test/blog/external-redirect/',
			...overrides,
		},
		URL,
		FormData,
		CSS: { escape: (value) => value },
		fetch: async () => ({ json: async () => ({ success: true, data: {} }) }),
	};
	vm.runInNewContext(script, context);
	return {
		link,
		modal,
		title,
		message,
		displayUrl,
		location,
		opened,
		click() {
			const event = { target: link, defaultPrevented: false, preventDefault() { this.defaultPrevented = true; } };
			listeners.click(event);
			return event.defaultPrevented;
		},
		continue() { proceed.listeners.click(); },
	};
}

function redirectUrl(destination) {
	const url = new URL('https://site.test/blog/external-redirect/');
	url.searchParams.set('url', destination);
	url.searchParams.set('wzlw_sig', 'a'.repeat(64));
	return url.href;
}

for (const filename of ['modal.js', 'modal.min.js']) {
	const script = readFileSync(join(__dirname, '../includes/admin/js', filename), 'utf8');
	for (const method of ['modal', 'inline_modal']) {
		test(`${filename}: ${method} ignores a forged data destination`, () => {
			const page = boot(script, { href: '/', 'data-wzlw-external': 'true', 'data-wzlw-url': 'javascript:void(0)' }, { warningMethod: method });
			page.click();
			assert.equal(page.displayUrl.textContent, 'https://site.test/');
			page.continue();
			assert.equal(page.location.href, 'https://site.test/');
		});
	}
	for (const unsafe of ['javascript:void(0)', 'JaVaScRiPt:void(0)', '\tjava\nscript:void(0)', 'data:text/html,test']) {
		test(`${filename}: unsafe href is never passed to a modal navigation sink: ${JSON.stringify(unsafe)}`, () => {
			const page = boot(script, { href: unsafe, class: 'wzlw-processed', 'data-wzlw-external': 'true', 'data-wzlw-url': unsafe });
			assert.equal(page.click(), false);
			assert.equal(page.modal.hasAttribute('hidden'), true);
			assert.equal(page.location.href, 'https://site.test/blog/post/');
			assert.equal(page.opened.length, 0);
		});
	}
	for (const destination of ['javascript:void(0)', 'https://outside.test/', 'https://site.test/blog/other/', redirectUrl('https://different.test/')]) {
		test(`${filename}: rejects forged redirect attribute: ${destination}`, () => {
			const page = boot(script, { href: '/safe/', class: 'wzlw-processed', 'data-wzlw-external': 'true', 'data-wzlw-redirect-url': destination }, { warningMethod: 'redirect' });
			assert.equal(page.click(), false);
			assert.equal(page.location.href, 'https://site.test/blog/post/');
			assert.equal(page.opened.length, 0);
		});
	}
	for (const target of ['', '_blank']) {
		test(`${filename}: valid signed redirect retains navigation behavior for ${target || 'same tab'}`, () => {
			const destination = 'https://outside.test/?a=1&b=2#section';
			const signed = redirectUrl(destination);
			const page = boot(script, { href: destination, target, rel: 'noreferrer', class: 'wzlw-processed', 'data-wzlw-external': 'true', 'data-wzlw-redirect-url': signed }, { warningMethod: 'redirect' });
			assert.equal(page.click(), true);
			if (target) {
				assert.deepEqual(page.opened[0], [signed, '_blank', 'noopener,noreferrer']);
			} else {
				assert.equal(page.location.href, signed);
			}
		});
	}
	test(`${filename}: root-relative affiliate redirects still work`, () => {
		const signed = redirectUrl('/go/product/');
		const page = boot(script, { href: '/go/product/', class: 'wzlw-processed', 'data-wzlw-external': 'true', 'data-wzlw-redirect-url': signed }, { warningMethod: 'redirect' });
		assert.equal(page.click(), true);
		assert.equal(page.location.href, signed);
	});
	test(`${filename}: PHP exclusion marker is respected even with stale warning attributes`, () => {
		const page = boot(script, { href: 'https://outside.test/', 'data-wzlw-excluded': 'true', 'data-wzlw-external': 'true', 'data-wzlw-url': 'https://outside.test/' }, { excludedDomains: ['outside.test'] });
		assert.equal(page.link.classList.contains('wzlw-processed'), false);
		assert.equal(page.link.markup, '');
		assert.equal(page.click(), false);
	});
	test(`${filename}: explicit force class still overrides exclusion`, () => {
		const page = boot(script, { href: 'https://outside.test/', class: 'wzlw-force-external', 'data-wzlw-excluded': 'true' }, { excludedDomains: ['outside.test'] });
		assert.equal(page.link.getAttribute('data-wzlw-external'), 'true');
		assert.equal(page.link.hasAttribute('data-wzlw-excluded'), false);
		assert.equal(page.click(), true);
	});
	test(`${filename}: network-path external URL is detected`, () => {
		const page = boot(script, { href: '//outside.test/' });
		assert.equal(page.link.getAttribute('data-wzlw-external'), 'true');
	});
	test(`${filename}: internal configured download URL is processed and uses download copy`, () => {
		const page = boot(script, { href: '/files/report.PDF?download=1#top' }, {
			downloadExtensions: ['pdf', 'zip'],
			downloadModalTitle: 'Download this file',
			downloadModalMessage: 'The file will be downloaded. Continue?',
		});
		assert.equal(page.link.getAttribute('data-wzlw-download'), 'true');
		assert.equal(page.link.getAttribute('data-wzlw-external'), null);
		assert.match(page.link.markup, /wzlw-download-icon/);
		assert.equal(page.click(), true);
		assert.equal(page.title.textContent, 'Download this file');
		assert.equal(page.message.textContent, 'The file will be downloaded. Continue?');
	});
	test(`${filename}: file extension in a query string is ignored`, () => {
		const page = boot(script, { href: '/download?file=report.pdf' }, { downloadExtensions: ['pdf'] });
		assert.equal(page.link.getAttribute('data-wzlw-download'), null);
		assert.equal(page.link.classList.contains('wzlw-processed'), false);
	});
	for (const href of ['/path/', '//site.test/path/', '?query=1', '#section']) {
		test(`${filename}: internal URL is not marked external: ${href}`, () => {
			const page = boot(script, { href });
			assert.equal(page.link.getAttribute('data-wzlw-external'), null);
		});
	}
	test(`${filename}: network-path excluded URL receives no warning`, () => {
		const page = boot(script, { href: '//sub.outside.test/', target: '_blank' }, { scope: 'both', excludedDomains: ['*.outside.test'] });
		assert.equal(page.link.getAttribute('data-wzlw-blank'), null);
		assert.equal(page.link.markup, '');
	});
	test(`${filename}: destination is revalidated when Continue is clicked`, () => {
		const page = boot(script, { href: 'https://outside.test/' });
		page.click();
		page.link.setAttribute('href', 'javascript:void(0)');
		page.link.setAttribute('data-wzlw-url', 'javascript:void(0)');
		page.continue();
		assert.equal(page.location.href, 'https://site.test/blog/post/');
		assert.equal(page.opened.length, 0);
	});
}
