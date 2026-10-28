
	<!--begin::Body-->
	<!-- Login for the public API/app surface. Rendered through
	     template->front(), which no longer loads Bootstrap, so this is plain
	     Tailwind on the portal.css token layer. The form contract is
	     unchanged: action, method, field names, id and data-kt-redirect-url
	     must keep working with api/Login::login(). -->
	<body class="min-h-screen bg-neutral-100 text-neutral-900 antialiased">
		<div class="flex min-h-screen flex-col lg:flex-row">

			<!--begin::Aside — tenant branding-->
			<div class="flex flex-col justify-center gap-6 bg-primary px-8 py-12 text-ink-inverse lg:w-1/2 lg:px-16">
				<a href="<?= base_url() ?>" class="inline-flex w-fit">
					<img alt="Logo" width="150" src="<?= media_url($ins_info->ins_logo_path ?? '', 'image/png') ?>" />
				</a>
				<div>
					<h1 class="text-2xl font-bold leading-tight lg:text-3xl"><?= $ins_info->ins_name ?? 'ইউনিয়ন পরিষদ'; ?></h1>
					<p class="mt-2 text-sm opacity-80"><?= $ins_info->addre_s_s ?? ''; ?></p>
				</div>
			</div>
			<!--end::Aside-->

			<!--begin::Body — sign-in card-->
			<div class="flex flex-1 items-center justify-center px-6 py-12 lg:px-10">
				<div class="flex w-full max-w-md flex-col gap-8 rounded-2xl border border-neutral-200 bg-white p-8 shadow-sm sm:p-10">

					<div class="text-center">
						<h1 class="text-xl font-bold text-neutral-900">সাইন ইন করুন</h1>
						<?php if ($this->session->flashdata('message')) { ?>
							<p class="mt-3 text-sm font-medium text-danger"><?= $this->session->flashdata('message'); ?></p>
						<?php } ?>
					</div>

					<form class="flex w-full flex-col gap-5" novalidate="novalidate"
					      id="kt_sign_in_form" data-kt-redirect-url="login_check" action="app_ck" method="post">

						<div class="flex flex-col gap-1.5">
							<label class="ht-label" for="identity">ইউজারনেম</label>
							<input id="identity" class="ht-field" type="text" name="identity"
							       placeholder="ইউজারনেম" autocomplete="username" required />
						</div>

						<div class="flex flex-col gap-1.5">
							<label class="ht-label" for="password">পাসওয়ার্ড</label>
							<div class="relative">
								<input id="password" class="ht-field w-full pr-10" type="password" name="password" data-saas-password
								       placeholder="পাসওয়ার্ড" autocomplete="current-password" required />
								<button type="button" data-saas-toggle-password
								        class="absolute right-3 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-neutral-600 cursor-pointer"
								        aria-label="পাসওয়ার্ড দেখান">
									<span data-saas-eye="show"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg></span>
									<span data-saas-eye="hide" class="hidden"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg></span>
								</button>
							</div>
						</div>

						<button type="submit" id="kt_sign_in_submit" class="ht-btn ht-btn-primary w-full">
							<span class="indicator-label">সাইন ইন করুন</span>
							<span class="indicator-progress hidden">অপেক্ষা করুন...</span>
						</button>
					</form>
				</div>
			</div>
			<!--end::Body-->

		</div>

		<script>
		// Replaces the Metronic form plugin's submit indicator, which is no
		// longer on this page. Blocks a double submit and shows progress.
		(function () {
			var form = document.getElementById('kt_sign_in_form');
			if (!form) return;
			form.addEventListener('submit', function () {
				var btn = document.getElementById('kt_sign_in_submit');
				if (!btn || btn.disabled) return;
				btn.disabled = true;
				var label = btn.querySelector('.indicator-label');
				var progress = btn.querySelector('.indicator-progress');
				if (label) label.classList.add('hidden');
				if (progress) progress.classList.remove('hidden');
			});
		})();
		</script>
		<!--end::Main-->
