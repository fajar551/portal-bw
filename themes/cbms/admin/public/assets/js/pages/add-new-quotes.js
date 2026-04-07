$(document).ready(function () {
	$("input[name='radioButtonClient']").click(function () {
		let checkedValue = $("input[name='radioButtonClient']:checked").val()
		if (checkedValue == 'option2') {
			$('#collapseExample').collapse('show')
		} else if (checkedValue == 'option1') {
			$('#collapseExample').collapse('hide')
		}
	})
})
