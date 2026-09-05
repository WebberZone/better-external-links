/**
 * Modal functionality for External Link Accessibility plugin.
 *
 * @package WebberZone\Link_Warnings
 * @since 1.0.0
 */

(function () {
	'use strict';

	const settings = typeof wzlwSettings !== 'undefined' ? wzlwSettings : {};
	const method = settings.warningMethod || 'inline';

	// Modal elements.
	let modal = null;
	let modalTitle = null;
	let modalMessage = null;
	let modalUrl = null;
	let modalContinue = null;
	let modalCancel = null;
	let modalDismiss = null;
	let currentLink = null;
	let focusableElements = [];
	let firstFocusable = null;
	let lastFocusable = null;
	let hiddenElements = [];

	/**
	 * Initialize modal functionality.
	 */
	function init() {
		scanNonPostLinks();

		document.addEventListener('click', handleLinkClick);

		modal = document.getElementById('wzlw-modal');
		if (!modal) {
			return;
		}

		modalTitle = modal.querySelector('#wzlw-modal-title');
		modalMessage = modal.querySelector('#wzlw-modal-message');
		modalUrl = modal.querySelector('.wzlw-modal-url-value');
		modalContinue = modal.querySelector('[data-wzlw-continue]');
		modalCancel = modal.querySelector('.wzlw-modal-cancel');
		modalDismiss = modal.querySelector('[data-wzlw-dismiss]');

		if (settings.modalTitle) modalTitle.textContent = settings.modalTitle;
		if (settings.modalMessage) modalMessage.textContent = settings.modalMessage;
		if (settings.continueText) modalContinue.textContent = settings.continueText;
		if (settings.cancelText) modalCancel.textContent = settings.cancelText;

		modal.querySelectorAll('[data-wzlw-close]').forEach(function (element) {
			element.addEventListener('click', closeModal);
		});
		modalContinue.addEventListener('click', handleContinue);
		modal.addEventListener('keydown', handleKeydown);
	}

	// ─── DOM scan ─────────────────────────────────────────────────────────────

	/**
	 * Scan all links not already processed by PHP and apply the same rules.
	 */
	function scanNonPostLinks() {
		if (!settings.siteHost) {
			return;
		}

		const noIconWrapperClasses = Array.isArray(settings.noIconWrapperClass) ? settings.noIconWrapperClass : (settings.noIconWrapperClass ? [settings.noIconWrapperClass] : []);
		const forceExternalWrapperClasses = Array.isArray(settings.forceExternalWrapperClass) ? settings.forceExternalWrapperClass : (settings.forceExternalWrapperClass ? [settings.forceExternalWrapperClass] : []);
		const forceExternalClasses = Array.isArray(settings.forceExternalClass) ? settings.forceExternalClass : (settings.forceExternalClass ? [settings.forceExternalClass] : []);
		const affiliateWrapperClasses = Array.isArray(settings.affiliateWrapperClass) ? settings.affiliateWrapperClass : (settings.affiliateWrapperClass ? [settings.affiliateWrapperClass] : []);
		const affiliateClasses = Array.isArray(settings.affiliateClass) ? settings.affiliateClass : (settings.affiliateClass ? [settings.affiliateClass] : []);
		const noIconClasses = Array.isArray(settings.noIconClass) ? settings.noIconClass : (settings.noIconClass ? [settings.noIconClass] : []);
		const isInlineMethod = ['inline', 'inline_modal', 'inline_redirect'].includes(method);
		const needsDataAttrs = ['modal', 'inline_modal', 'redirect', 'inline_redirect'].includes(method);
		const needsRedirectUrl = ['redirect', 'inline_redirect'].includes(method);

		const linksNeedingRedirectUrl = [];

		document.querySelectorAll('a:not(.wzlw-processed)').forEach(function (link) {
			const href = link.getAttribute('href');
			if (!href) {
				return;
			}

			const inNoIconWrapper = noIconWrapperClasses.length && noIconWrapperClasses.some(function (c) { return link.closest('.' + CSS.escape(c)); });
			const inForceExtWrapper = forceExternalWrapperClasses.length && forceExternalWrapperClasses.some(function (c) { return link.closest('.' + CSS.escape(c)); });
			const hasForceExtClass = forceExternalClasses.length && forceExternalClasses.some(function (c) { return link.classList.contains(c); });
			const inAffiliateWrapper = affiliateWrapperClasses.length && affiliateWrapperClasses.some(function (c) { return link.closest('.' + CSS.escape(c)); });
			const hasAffiliateClass = affiliateClasses.length && affiliateClasses.some(function (c) { return link.classList.contains(c); });
			const hasNoIconClass = noIconClasses.length && noIconClasses.some(function (c) { return link.classList.contains(c); });

			const isAffiliate = !!(inAffiliateWrapper || hasAffiliateClass);
			const isForced = !!(isAffiliate || inForceExtWrapper || hasForceExtClass);
			if (link.hasAttribute('data-wzlw-excluded')) {
				if (!isForced) {
					return;
				}
				link.removeAttribute('data-wzlw-excluded');
			}
			const isExternal = isForced || isExternalHref(href);
			applyLinkAttributes(link, isExternal, isAffiliate);
			const hasTarget = '_blank' === link.getAttribute('target');

			if (!shouldProcess(isExternal, hasTarget)) {
				if (inNoIconWrapper && hasTarget) {
					appendAriaLabel(link);
				}
				return;
			}

			// Excluded-domain links with target=_blank reach here under scope=both.
			// Suppress modal/data attrs but keep ARIA so screen readers know the tab will open.
			if (!isExternal && hasTarget && isExcludedHref(href)) {
				appendAriaLabel(link);
				return;
			}

			link.classList.add('wzlw-processed');
			if (isExternal) {
				link.classList.add('wzlw-external');
			}
			if (inNoIconWrapper && noIconClasses.length) {
				noIconClasses.forEach(function (c) { link.classList.add(c); });
			}

			appendAriaLabel(link);

			if (needsDataAttrs) {
				link.setAttribute('data-wzlw-url', href);
				if (isExternal) {
					link.setAttribute('data-wzlw-external', 'true');
				} else {
					link.setAttribute('data-wzlw-blank', 'true');
				}
				if (needsRedirectUrl) {
					linksNeedingRedirectUrl.push(link);
				}
			}

			if (isInlineMethod) {
				const indicator = buildIndicatorHtml(!!(inNoIconWrapper || hasNoIconClass), hasTarget);
				if (indicator) {
					link.insertAdjacentHTML('beforeend', indicator);
				}
			}
		});

		if (linksNeedingRedirectUrl.length) {
			fetchRedirectUrls(linksNeedingRedirectUrl);
		}
	}

	/**
	 * Apply configured rel and target attributes without replacing existing rel values.
	 *
	 * @param {HTMLAnchorElement} link Link to update.
	 * @param {boolean} isExternal Whether the link is external.
	 * @param {boolean} isAffiliate Whether the link is an affiliate link.
	 */
	function applyLinkAttributes(link, isExternal, isAffiliate) {
		let attributes = [];
		if (isExternal) {
			attributes = attributes.concat(Array.isArray(settings.linkAttributesExternal) ? settings.linkAttributesExternal : []);
		}
		if (isAffiliate) {
			attributes = attributes.concat(Array.isArray(settings.linkAttributesAffiliate) ? settings.linkAttributesAffiliate : []);
		}
		attributes = [...new Set(attributes)];

		if (attributes.includes('target_blank')) {
			link.setAttribute('target', '_blank');
		}

		const relValues = attributes.filter(function (attribute) {
			return ['nofollow', 'sponsored', 'ugc'].includes(attribute);
		});
		if ('_blank' === link.getAttribute('target')) {
			['noopener', 'noreferrer'].forEach(function (attribute) {
				if (attributes.includes(attribute)) {
					relValues.push(attribute);
				}
			});
		}
		if (relValues.length) {
			const existing = (link.getAttribute('rel') || '').trim().split(/\s+/).filter(Boolean);
			relValues.forEach(function (value) {
				if (!existing.some(function (existingValue) { return existingValue.toLowerCase() === value; })) {
					existing.push(value);
				}
			});
			link.setAttribute('rel', existing.join(' '));
		}
	}

	/**
	 * Determine if a URL points to an external host.
	 *
	 * @param {string} href
	 * @return {boolean}
	 */
	function isExternalHref(href) {
		if ((href.startsWith('/') && !href.startsWith('//')) || href.startsWith('#') || href.startsWith('?')) {
			return false;
		}
		try {
			const host = new URL(href, window.location.href).hostname.toLowerCase().replace(/\.$/, '');
			if (!host) {
				return false;
			}
			if (host === settings.siteHost) {
				return false;
			}
			const excludedDomains = settings.excludedDomains || [];
			for (const domain of excludedDomains) {
				if (domain.startsWith('*.')) {
					const base = domain.slice(2);
					if (base && host.endsWith('.' + base)) {
						return false;
					}
				} else if (host === domain) {
					return false;
				}
			}
			return true;
		} catch (e) {
			return false;
		}
	}

	/**
	 * Return true if the href matches an excluded domain entry.
	 * Unlike isExternalHref, this does not check the site host.
	 *
	 * @param {string} href
	 * @return {boolean}
	 */
	function isExcludedHref(href) {
		if ((href.startsWith('/') && !href.startsWith('//')) || href.startsWith('#') || href.startsWith('?')) {
			return false;
		}
		try {
			const host = new URL(href, window.location.href).hostname.toLowerCase().replace(/\.$/, '');
			if (!host) {
				return false;
			}
			const excludedDomains = settings.excludedDomains || [];
			for (const domain of excludedDomains) {
				if (domain.startsWith('*.')) {
					const base = domain.slice(2);
					if (base && host.endsWith('.' + base)) {
						return true;
					}
				} else if (host === domain) {
					return true;
				}
			}
			return false;
		} catch (e) {
			return false;
		}
	}

	/**
	 * Mirror PHP's should_process_link() logic.
	 *
	 * @param {boolean} isExternal
	 * @param {boolean} hasTarget
	 * @return {boolean}
	 */
	function shouldProcess(isExternal, hasTarget) {
		return 'both' === (settings.scope || 'external')
			? (isExternal || hasTarget)
			: isExternal;
	}

	/**
	 * Append screen reader text to aria-label if one already exists.
	 *
	 * @param {HTMLElement} link
	 */
	function appendAriaLabel(link) {
		const srText = settings.screenReaderText || '';
		if (!srText) {
			return;
		}
		const existing = link.getAttribute('aria-label');
		if (existing) {
			link.setAttribute('aria-label', existing + ', ' + srText);
		}
	}

	/**
	 * Build indicator HTML, mirroring PHP's get_visual_indicator() /
	 * add_indicator_to_link() logic.
	 *
	 * @param {boolean} suppress  True when the link has the no-icon class or is in a no-icon wrapper.
	 * @param {boolean} hasTarget True when the link has target="_blank".
	 * @return {string}
	 */
	function buildIndicatorHtml(suppress, hasTarget) {
		const srText = settings.screenReaderText || '';
		const srSpan = srText ? '<span class="screen-reader-text">' + escHtml(srText) + '</span>' : '';

		if (suppress) {
			return hasTarget ? srSpan : '';
		}

		const visual = settings.visualIndicator || 'icon';
		let html = srSpan;

		if ('icon' === visual || 'both' === visual) {
			html += '<span class="wzlw-icon" aria-hidden="true"></span>';
		}
		if ('text' === visual || 'both' === visual) {
			html += '<span class="wzlw-text" aria-hidden="true">' + escHtml(settings.indicatorText || '') + '</span>';
		}

		return html;
	}

	/**
	 * Minimal HTML escaping for text injected via insertAdjacentHTML.
	 *
	 * @param {string} str
	 * @return {string}
	 */
	function escHtml(str) {
		return str
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}

	/**
	 * Build the window.open() features string for a link.
	 *
	 * noreferrer honours the link's own rel, so sites that want referrers passed to
	 * affiliate destinations can opt out of it. noopener is always set: unlike a plain
	 * target="_blank" navigation, window.open() hands the destination a live window.opener
	 * reference unless it is suppressed here.
	 *
	 * @param {HTMLElement} link
	 * @return {string}
	 */
	function windowOpenFeatures(link) {
		const rel = (link.getAttribute('rel') || '').toLowerCase().split(/\s+/);
		return rel.indexOf('noreferrer') !== -1 ? 'noopener,noreferrer' : 'noopener';
	}

	/**
	 * Fetch HMAC-signed redirect URLs for a batch of links via AJAX.
	 * Only used for redirect/inline_redirect methods.
	 *
	 * @param {HTMLElement[]} links
	 */
	function fetchRedirectUrls(links) {
		const formData = new FormData();
		formData.append('action', 'wzlw_sign_urls');
		formData.append('nonce', settings.nonce || '');
		links.forEach(function (link) {
			formData.append('urls[]', link.getAttribute('data-wzlw-url'));
		});

		fetch(settings.ajaxUrl, {
			method: 'POST',
			body: formData,
			credentials: 'same-origin',
		})
			.then(function (response) {
				return response.json();
			})
			.then(function (data) {
				if (!data.success) {
					return;
				}
				links.forEach(function (link) {
					const signed = data.data[link.getAttribute('data-wzlw-url')];
					if (signed) {
						link.setAttribute('data-wzlw-redirect-url', signed);
					}
				});
			})
			.catch(function () { });
	}

	// ─── Dismissal ("Don't show again") ───────────────────────────────────────

	const DISMISS_KEY = 'wzlwDismissed';
	const frequency = settings.modalFrequency || 'always';
	const dismissScope = settings.modalFrequencyScope || 'domain';

	/**
	 * Return the storage backend for the configured frequency, or null when
	 * dismissals are disabled or storage is unavailable (e.g. private mode).
	 *
	 * @return {Storage|null}
	 */
	function getDismissStore() {
		if ('always' === frequency) {
			return null;
		}
		try {
			return 'session' === frequency ? window.sessionStorage : window.localStorage;
		} catch (e) {
			return null;
		}
	}

	/**
	 * Read the stored dismissal map.
	 *
	 * @return {Object} Map of scope key to expiry timestamp (0 = session-only).
	 */
	function readDismissals() {
		const store = getDismissStore();
		if (!store) {
			return {};
		}
		try {
			const data = JSON.parse(store.getItem(DISMISS_KEY) || '{}');
			return data && 'object' === typeof data ? data : {};
		} catch (e) {
			return {};
		}
	}

	/**
	 * Persist the dismissal map.
	 *
	 * @param {Object} data
	 */
	function writeDismissals(data) {
		const store = getDismissStore();
		if (!store) {
			return;
		}
		try {
			store.setItem(DISMISS_KEY, JSON.stringify(data));
		} catch (e) { }
	}

	/**
	 * Build the dismissal key for a URL, honouring the configured scope.
	 *
	 * @param {string} url
	 * @return {string} Empty string when no key can be derived.
	 */
	function dismissKeyFor(url) {
		if ('global' === dismissScope) {
			return '*';
		}
		try {
			return new URL(url, window.location.href).hostname.toLowerCase().replace(/\.$/, '');
		} catch (e) {
			return '';
		}
	}

	/**
	 * Determine whether the modal has been dismissed for a URL.
	 *
	 * @param {string} url
	 * @return {boolean}
	 */
	function isDismissed(url) {
		if (!getDismissStore() || !url) {
			return false;
		}
		const key = dismissKeyFor(url);
		if (!key) {
			return false;
		}
		const data = readDismissals();
		const expiry = data[key];
		if ('number' !== typeof expiry) {
			return false;
		}
		if (0 === expiry || Date.now() < expiry) {
			return true;
		}
		delete data[key];
		writeDismissals(data);
		return false;
	}

	/**
	 * Record a dismissal for a URL.
	 *
	 * @param {string} url
	 */
	function storeDismissal(url) {
		if (!getDismissStore() || !url) {
			return;
		}
		const key = dismissKeyFor(url);
		if (!key) {
			return;
		}
		const days = parseInt(settings.modalFrequencyDays, 10) || 30;
		const data = readDismissals();
		data[key] = 'session' === frequency ? 0 : Date.now() + days * 86400000;
		writeDismissals(data);
	}

	// ─── Click handling ───────────────────────────────────────────────────────

	function parseHttpUrl(value) {
		if (typeof value !== 'string' || !value.trim()) {
			return null;
		}
		try {
			const url = new URL(value, document.baseURI);
			return ['http:', 'https:'].includes(url.protocol) ? url : null;
		} catch (e) {
			return null;
		}
	}

	function getRedirectUrl(link, destination) {
		const url = parseHttpUrl(link.getAttribute('data-wzlw-redirect-url'));
		const base = parseHttpUrl(settings.redirectBaseUrl);
		const target = url ? parseHttpUrl(url.searchParams.get('url')) : null;
		if (!url || !base || !target || url.origin !== base.origin || url.pathname !== base.pathname ||
			url.username || url.password || url.hash || url.searchParams.getAll('url').length !== 1 ||
			target.href !== destination.href) {
			return null;
		}
		return url.href;
	}

	/**
	 * Handle link clicks.
	 *
	 * @param {Event} e Click event.
	 */
	function handleLinkClick(e) {
		const link = e.target.closest('a[data-wzlw-external], a[data-wzlw-blank]');
		if (!link || link.hasAttribute('data-wzlw-excluded')) {
			return;
		}
		const destination = parseHttpUrl(link.getAttribute('href'));
		if (!destination) {
			return;
		}

		if ('redirect' === method || 'inline_redirect' === method) {
			const redirectUrl = getRedirectUrl(link, destination);
			if (redirectUrl) {
				e.preventDefault();
				if ('_blank' === link.getAttribute('target')) {
					window.open(redirectUrl, '_blank', windowOpenFeatures(link));
				} else {
					window.location.href = redirectUrl;
				}
				return;
			}
		}

		if ('modal' === method || 'inline_modal' === method) {
			// Previously dismissed: let the browser follow the link untouched.
			if (!modal || isDismissed(destination.href)) {
				return;
			}
			e.preventDefault();
			currentLink = link;
			showModal(link);
		}
	}

	// ─── Modal ────────────────────────────────────────────────────────────────

	/**
	 * Show modal.
	 *
	 * @param {HTMLElement} link Link element.
	 */
	function showModal(link) {
		const url = parseHttpUrl(link.getAttribute('href'));
		if (!url) {
			return;
		}
		modalUrl.textContent = url.href;
		if (modalDismiss) {
			modalDismiss.checked = false;
		}
		if ('_blank' === link.getAttribute('target') && settings.screenReaderText) {
			modalContinue.setAttribute('aria-label', settings.continueText + ', ' + settings.screenReaderText);
		} else {
			modalContinue.removeAttribute('aria-label');
		}
		modal.removeAttribute('hidden');
		document.body.classList.add('wzlw-modal-active');
		hiddenElements = [];
		Array.from(document.body.children).forEach(function (el) {
			if (el !== modal && !el.hasAttribute('aria-hidden')) {
				el.setAttribute('aria-hidden', 'true');
				hiddenElements.push(el);
			}
		});
		setupFocusTrap();
		if (firstFocusable) {
			firstFocusable.focus();
		}
	}

	/**
	 * Close modal.
	 */
	function closeModal() {
		modal.setAttribute('hidden', '');
		document.body.classList.remove('wzlw-modal-active');
		hiddenElements.forEach(function (el) {
			el.removeAttribute('aria-hidden');
		});
		hiddenElements = [];
		if (currentLink) {
			currentLink.focus();
		}
		currentLink = null;
	}

	/**
	 * Handle continue button click.
	 */
	function handleContinue() {
		if (!currentLink) {
			return;
		}
		const url = parseHttpUrl(currentLink.getAttribute('href'));
		if (!url) {
			closeModal();
			return;
		}
		if (modalDismiss && modalDismiss.checked) {
			storeDismissal(url.href);
		}
		if ('_blank' === currentLink.getAttribute('target')) {
			window.open(url.href, '_blank', windowOpenFeatures(currentLink));
		} else {
			window.location.href = url.href;
		}
		closeModal();
	}

	/**
	 * Set up focus trap.
	 */
	function setupFocusTrap() {
		focusableElements = modal.querySelectorAll(
			'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
		);
		firstFocusable = focusableElements[0];
		lastFocusable = focusableElements[focusableElements.length - 1];
	}

	/**
	 * Handle keyboard events.
	 *
	 * @param {KeyboardEvent} e Keyboard event.
	 */
	function handleKeydown(e) {
		if ('Escape' === e.key) {
			closeModal();
			return;
		}
		if ('Tab' === e.key) {
			if (e.shiftKey) {
				if (document.activeElement === firstFocusable) {
					e.preventDefault();
					lastFocusable.focus();
				}
			} else {
				if (document.activeElement === lastFocusable) {
					e.preventDefault();
					firstFocusable.focus();
				}
			}
		}
	}

	if ('loading' === document.readyState) {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
