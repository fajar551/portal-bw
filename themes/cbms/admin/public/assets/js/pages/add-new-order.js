!(function (i) {
	'use strict'
	function a() {}
	;(a.prototype.init = function () {
		i('.select2-placeholder').select2({
			placeholder: 'Type to Search Client',
			allowClear: true,
			width: 'resolve',
		}),
			i('.select2-limiting').select2({
				maximumSelectionLength: 3,
				width: 'resolve',
			}),
			i('.select2-search-disable').select2({
				minimumResultsForSearch: 1 / 0,
				width: 'resolve',
			})
	}),
		(i.AdvancedForm = new a()),
		(i.AdvancedForm.Constructor = a)
})(window.jQuery),
	(function () {
		'use strict'
		window.jQuery.AdvancedForm.init()
	})()
