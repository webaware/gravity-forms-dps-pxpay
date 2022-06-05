(function($) {

	let clicked = false;
	let ids = [];
	let step_next = 0;

	$("#gfdpspxpay-upgrade").on("click", function() {
		if (!clicked) {
			clicked = true;
			processStep();
		}
	});

	function processStep() {
		const step = gfdpspxpay_updatev1[step_next];

		if (step === "end") {
			$("#gfdpspxpay-upgrade").replaceWith("<strong>Done.</strong>");
			$("#gfdpspxpay-updatev1-progress").hide();
		}
		else {
			$("#gfdpspxpay-upgrade").text("Processing " + step + "...");
			$("#gfdpspxpay-updatev1-progress").show();

			$.getJSON(ajaxurl, { action: "gfdpspxpay_upgradev1", step: step + "_list" }, processList);
		}
	}

	function processList(response) {
		if (response && response.success) {
			ids = response.data.ids;

			processNext(0, response.data.step);
		}
	}

	function processNext(next, step) {
		if (next < ids.length) {
			$.getJSON(ajaxurl, { action: "gfdpspxpay_upgradev1", step: step, id: ids[next], next: next + 1 }, processItem);
		}
	}

	function processItem(response) {
		if (response && response.success) {
			const next = response.data.next;
			let pcnt;

			if (next < ids.length) {
				pcnt = Math.floor(next / ids.length * 100) + "%";
				processNext(next, response.data.step);
			}
			else {
				pcnt = "100%";
				step_next++;
				processStep();
			}

			$("#gfdpspxpay-updatev1-progress > div").css({width : pcnt}).text(pcnt);
		}
		else {
			const error = response.data.error || "ERROR";
			$("#gfdpspxpay-updatev1-progress > div").css({width : "100%", backgroundColor: "transparent"}).text(error);
		}
	}

})(jQuery);
