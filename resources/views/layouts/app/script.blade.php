
	<script src="{{asset('AdminAssets/js/jquery-3.7.1.min.js')}}" type="ca0789102193bd8248eafb96-text/javascript"></script>
	<script src="{{asset('AdminAssets/js/feather.min.js')}}" type="ca0789102193bd8248eafb96-text/javascript"></script>
	<script src="{{asset('AdminAssets/js/jquery.slimscroll.min.js')}}" type="ca0789102193bd8248eafb96-text/javascript"></script>
	<script src="{{asset('AdminAssets/js/bootstrap.bundle.min.js')}}" type="ca0789102193bd8248eafb96-text/javascript"></script>
	<script src="{{asset('AdminAssets/plugins/apexchart/apexcharts.min.js')}}" type="ca0789102193bd8248eafb96-text/javascript"></script>
	<script src="{{asset('AdminAssets/plugins/apexchart/chart-data.js')}}" type="ca0789102193bd8248eafb96-text/javascript"></script>
	<script src="{{asset('AdminAssets/plugins/chartjs/chart.min.js')}}" type="ca0789102193bd8248eafb96-text/javascript"></script>
	<script src="{{asset('AdminAssets/plugins/chartjs/chart-data.js')}}" type="ca0789102193bd8248eafb96-text/javascript"></script>
	<script src="{{asset('AdminAssets/js/moment.min.js')}}" type="ca0789102193bd8248eafb96-text/javascript"></script>
	<script src="{{asset('AdminAssets/plugins/daterangepicker/daterangepicker.js')}}" type="ca0789102193bd8248eafb96-text/javascript"></script>
	<script src="{{asset('AdminAssets/plugins/select2/js/select2.min.js')}}" type="ca0789102193bd8248eafb96-text/javascript"></script>
	<script src="{{asset('AdminAssets/plugins/@simonwep/pickr/pickr.es5.min.js')}}" type="ca0789102193bd8248eafb96-text/javascript"></script>
	<script src="{{asset('AdminAssets/js/theme-colorpicker.js')}}" type="ca0789102193bd8248eafb96-text/javascript"></script>
	<script src="{{asset('AdminAssets/js/script.js')}}" type="ca0789102193bd8248eafb96-text/javascript"></script>

<style>
body.fullscreen-sidebar-hidden .sidebar {
	display: none !important;
}

body.fullscreen-sidebar-hidden .page-wrapper {
	margin-left: 0 !important;
}

body.fullscreen-sidebar-hidden .header-left {
	display: none !important;
}
</style>

<script>
(function () {
	const body = document.body;
	const fullscreenBtn = document.getElementById('btnFullscreen');
	const fullscreenLayoutKey = 'appPersistentFullscreenLayout';

	if (!body) {
		return;
	}

	function isPersistentLayoutEnabled() {
		try {
			return localStorage.getItem(fullscreenLayoutKey) === '1';
		} catch (e) {
			return false;
		}
	}

	function setPersistentLayoutEnabled(value) {
		try {
			localStorage.setItem(fullscreenLayoutKey, value ? '1' : '0');
		} catch (e) {
		}
	}

	function applyFullscreenLayout(isFullscreen) {
		body.classList.toggle('fullscreen-sidebar-hidden', Boolean(isFullscreen));
	}

	function togglePageFullscreen() {
		if (isInFullscreen()) {
			if (document.exitFullscreen) {
				document.exitFullscreen();
			} else if (document.webkitExitFullscreen) {
				document.webkitExitFullscreen();
			} else if (document.msExitFullscreen) {
				document.msExitFullscreen();
			}
			return;
		}

		const target = document.documentElement;
		if (target.requestFullscreen) {
			target.requestFullscreen();
		} else if (target.webkitRequestFullscreen) {
			target.webkitRequestFullscreen();
		} else if (target.msRequestFullscreen) {
			target.msRequestFullscreen();
		}
	}

	function isInFullscreen() {
		return Boolean(
			document.fullscreenElement ||
			document.webkitFullscreenElement ||
			document.mozFullScreenElement ||
			document.msFullscreenElement
		);
	}

	function applySavedOrNativeFullscreen() {
		applyFullscreenLayout(isPersistentLayoutEnabled() || isInFullscreen());
	}

	['fullscreenchange', 'webkitfullscreenchange', 'mozfullscreenchange', 'MSFullscreenChange'].forEach((eventName) => {
		document.addEventListener(eventName, function () {
			applySavedOrNativeFullscreen();
		});
	});

	window.addEventListener('storage', function (event) {
		if (event.key === fullscreenLayoutKey) {
			applySavedOrNativeFullscreen();
		}
	});

	if (!fullscreenBtn) {
		applySavedOrNativeFullscreen();
		return;
	}

	fullscreenBtn.addEventListener('click', function (event) {
		event.preventDefault();
		const enablePersistentLayout = !isPersistentLayoutEnabled();
		setPersistentLayoutEnabled(enablePersistentLayout);

		if (enablePersistentLayout) {
			if (!isInFullscreen()) {
				togglePageFullscreen();
			}
		} else if (isInFullscreen()) {
			togglePageFullscreen();
		}

		setTimeout(function () {
			applySavedOrNativeFullscreen();
		}, 120);
	});

	applySavedOrNativeFullscreen();
})();
</script>


<script src="/cdn-cgi/scripts/7d0fa10a/cloudflare-static/rocket-loader.min.js" data-cf-settings="ca0789102193bd8248eafb96-|49" defer></script></body>


@yield('script')





