(function () {
	if (!window.wc || !window.wc.wcBlocksRegistry || !window.wc.wcSettings || !window.wp || !window.wp.element) {
		return;
	}

	var settings = window.wc.wcSettings.getSetting('paxig_vnpay_data', {});
	var createElement = window.wp.element.createElement;
	var label = settings.title || 'VNPAY';
	var description = settings.description || '';
	var content = description ? createElement('div', null, description) : createElement('div');

	window.wc.wcBlocksRegistry.registerPaymentMethod({
		name: 'paxig_vnpay',
		label: createElement('span', null, label),
		content: content,
		edit: content,
		canMakePayment: function () {
			return true;
		},
		ariaLabel: label,
		supports: {
			features: settings.supports || ['products'],
		},
	});
})();
