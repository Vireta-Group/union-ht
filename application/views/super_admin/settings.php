<?php
/**
 * Settings module — platform settings and per-tenant contact details.
 *
 * Rendered inside Template::sadmin(). The heading and sidebar come from
 * the shell; this file is the content.
 *
 * ONE FETCH HELPER, TWO WIRE FORMATS
 * Every save in this file goes through SST(). The text panels send JSON,
 * the two asset uploads send FormData — because a file cannot be carried
 * in a JSON body without base64-inlining it, and inlining a 300 KB PNG
 * into a string that then has to be parsed back out is a bad way to move
 * a file. The helper takes the body as an argument rather than deciding,
 * so the difference is visible at the call site.
 *
 * THE PANELS ARE GENERATED, NOT HAND-WRITTEN
 * Every field below is emitted from $groups, which
 * Sadmin_settings::platform_specs() returns. The same list is the
 * endpoint's whitelist, so a field cannot exist in the panel and be
 * silently dropped by the save. Writing the markup per field by hand
 * would reintroduce exactly that drift.
 *
 * LEGACY FIELDS ARE SHOWN BUT NOT EDITABLE
 * The nid_* rate columns arrived with the original single-institute
 * install and nothing reads them. Rendering them as live inputs would
 * assert that editing them does something. Each one says so, on the
 * field, and only nid_search_activity — which back/entry_data_form.php
 * really does read — has a control.
 */
defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('sa_hint')) {
/**
 * A hint line, in the one style this page uses for them.
 */
function sa_hint($text, $tone = 'muted')
{
    if ($text === '') {
        return '';
    }
    $cls = $tone === 'warn'
        ? 'text-amber-700'
        : 'text-neutral-500';
    $ico = $tone === 'warn' ? 'ph-warning' : 'ph-info';
    return '<p class="mt-1 text-[11px] leading-relaxed ' . $cls . ' flex items-start gap-1">'
         . '<i class="ph ' . $ico . ' text-xs mt-0.5 shrink-0"></i>'
         . '<span>' . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . '</span></p>';
}
}

/** The one input class string, so the four panel types look identical. */
if ( ! function_exists('sa_in')) {
function sa_in($extra = '')
{
    return 'w-full p-2.5 rounded-lg border border-neutral-300 text-xs focus:border-indigo-600 focus:outline-none '
         . $extra;
}
}
?>

<!-- ===================== Page banner ===================== -->
<div class="bg-white border border-neutral-200 rounded-2xl p-4 sm:p-5 shadow-xs">
    <div class="flex items-start gap-3">
        <i class="ph ph-check-circle text-lg shrink-0 mt-0.5" style="color:#047857"></i>
        <div class="min-w-0">
            <p class="text-sm font-bold text-neutral-900">এই তথ্যগুলো মূল ওয়েবসাইটে প্রদর্শিত হয়</p>
            <p class="text-xs text-neutral-600 mt-1 leading-relaxed">
                অফিসিয়াল যোগাযোগ নম্বর, হোয়াটসঅ্যাপ, সাপোর্ট ইমেইল ও ঠিকানা নেভিগেশন বার, সাপোর্ট মডাল ও কন্টাক্ট পেজে দেখানো হয়। যে ফিল্ডটি খালি রাখবেন, সেই নির্দিষ্ট তথ্যটি ওয়েবসাইটে প্রদর্শিত হবে না।
            </p>
            <?php if (!empty($updated_at)): ?>
                <p class="text-[11px] text-neutral-400 mt-2">
                    সর্বশেষ সংরক্ষণ:
                    <span class="bengali-num"><?= bn_number(date('d M Y, h:i a', strtotime($updated_at))) ?></span>
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ===================== Platform panels ===================== -->
<?php foreach ($groups as $gkey => $group): ?>
    <form
        class="bg-white border border-neutral-200 rounded-2xl shadow-xs overflow-hidden"
        onsubmit="return saSaveGroup(event, '<?= $gkey ?>')"
        id="saGrp-<?= $gkey ?>">

        <div class="px-4 sm:px-5 py-3.5 border-b border-neutral-200 flex items-center justify-between gap-3">
            <h2 class="text-sm font-bold text-neutral-900"><?= $group['title'] ?></h2>
            <span id="saSt-<?= $gkey ?>" class="text-[11px] font-bold"></span>
        </div>

        <?php if (!empty($group['note'])): ?>
            <div class="px-4 sm:px-5 py-2.5 bg-neutral-50 border-b border-neutral-200">
                <p class="text-[11px] text-neutral-600 leading-relaxed"><?= $group['note'] ?></p>
            </div>
        <?php endif; ?>

        <div class="p-4 sm:p-5 space-y-4">
            <?php foreach ($group['fields'] as $f):
                $key = $f['key'];
                $val = (string) ($row->$key ?? '');
                $cap = $caps[$key] ?? 4000;
                $id  = 'saF-' . $key;
                $edit = $f['edit'] ?? TRUE;
            ?>

                <?php if ($f['type'] === 'asset'):
                    $a = $assets[$key] ?? ['raw' => '', 'ok' => FALSE, 'url' => ''];
                ?>
                    <div>
                        <label class="block font-bold text-neutral-700 mb-1" for="<?= $id ?>">
                            <?= $f['label'] ?>
                        </label>

                        <div class="flex flex-col sm:flex-row items-start gap-3 sm:gap-4">
                            <div class="shrink-0">
                                <?php if ($a['ok']): ?>
                                    <img
                                        src="<?= $a['url'] ?>"
                                        alt=""
                                        class="w-20 h-20 object-contain rounded-xl border border-neutral-200 bg-neutral-50"
                                        onerror="this.src='data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=='">
                                <?php else: ?>
                                    <div class="w-20 h-20 rounded-xl border border-dashed border-neutral-300 bg-neutral-50 flex items-center justify-center">
                                        <i class="ph ph-image text-2xl text-neutral-300"></i>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="flex-1 min-w-0">
                                <input
                                    type="file"
                                    id="<?= $id ?>"
                                    accept="image/png,image/jpeg,image/gif,image/webp"
                                    class="block w-full text-xs text-neutral-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700 file:font-bold file:text-xs file:cursor-pointer hover:file:bg-indigo-100">

                                <div class="flex items-center gap-2 mt-2">
                                    <button type="button"
                                            onclick="saUploadAsset('<?= $key ?>', '<?= $id ?>')"
                                            class="px-3 py-1.5 rounded-lg bg-indigo-700 hover:bg-indigo-800 text-white font-bold text-[11px] cursor-pointer transition-colors">
                                        আপলোড করুন
                                    </button>
                                    <?php if ($a['raw'] !== ''): ?>
                                        <button type="button"
                                                onclick="saClearAsset('<?= $key ?>')"
                                                class="px-3 py-1.5 rounded-lg bg-neutral-100 hover:bg-neutral-200 text-neutral-700 font-bold text-[11px] cursor-pointer transition-colors">
                                            সরান
                                        </button>
                                    <?php endif; ?>
                                </div>

                                <?php if ($a['raw'] === ''): ?>
                                    <p class="mt-2 text-[11px] text-neutral-400">কোনো ফাইল সেট করা নেই।</p>
                                <?php elseif ($a['ok']): ?>
                                    <p class="mt-2 text-[11px] text-neutral-500 font-mono break-all"><?= $a['raw'] ?></p>
                                <?php else: ?>
                                    <?php
                                    /*
                                     * The pre-migration value. It is a path to
                                     * a file that does not exist AND it is not
                                     * a shape media_url() recognises, so
                                     * rendering it would produce a broken
                                     * image. Say both things rather than
                                     * showing an <img> that cannot load.
                                     */
                                    ?>
                                    <div class="mt-2 rounded-lg bg-amber-50 border border-amber-200 p-2.5">
                                        <p class="text-[11px] text-amber-800 leading-relaxed flex items-center gap-1.5">
                                            <i class="ph ph-warning"></i>
                                            <span>এই অ্যাসেট ফাইলটি বর্তমানে সার্ভারে নেই। নতুন ফাইল আপলোড করে হালনাগাদ করুন।</span>
                                        </p>
                                    </div>
                                <?php endif; ?>

                                <?= sa_hint($f['hint']) ?>
                            </div>
                        </div>
                    </div>

                <?php elseif ( ! $edit): ?>
                    <?php /* read-only: see the file docblock */ ?>
                    <div>
                        <p class="block font-bold text-neutral-500 mb-1"><?= $f['label'] ?></p>
                        <div class="px-3 py-2 rounded-lg bg-neutral-100 text-xs text-neutral-500 font-mono break-all">
                            <?= $val === '' ? '—' : $val ?>
                        </div>
                        <?= sa_hint($f['hint'] !== '' ? $f['hint'] : 'পড়া হয় না। কোথাও ব্যবহার হচ্ছে না।', 'warn') ?>
                    </div>

                <?php else: ?>
                    <div>
                        <?php if ($f['type'] === 'textarea'): ?>
                            <label class="block font-bold text-neutral-700 mb-1" for="<?= $id ?>"><?= $f['label'] ?></label>
                            <textarea id="<?= $id ?>" name="<?= $key ?>" rows="3"
                                      maxlength="<?= $cap ?>"
                                      placeholder="<?= htmlspecialchars($f['label'], ENT_QUOTES, 'UTF-8') ?>"
                                      class="<?= sa_in('resize-y') ?>"><?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8') ?></textarea>

                        <?php elseif ($f['type'] === 'int'): ?>
                            <label class="block font-bold text-neutral-700 mb-1" for="<?= $id ?>"><?= $f['label'] ?></label>
                            <select id="<?= $id ?>" name="<?= $key ?>" class="<?= sa_in() ?>">
                                <option value="0" <?= $val === '0' || $val === '' ? 'selected' : '' ?>>না (০)</option>
                                <option value="1" <?= $val === '1' ? 'selected' : '' ?>>হ্যাঁ (১)</option>
                            </select>

                        <?php else: ?>
                            <?php
                            /*
                             * type=email and type=url on the <input> are a
                             * convenience for the browser, not the check.
                             * They only fire on form submit, and this form
                             * is submitted with a preventDefault() in
                             * saSaveGroup(), so the browser's own validation
                             * never runs. Everything is enforced server-side
                             * in Sadmin_settings::validate() — which is the
                             * only place it can be enforced anyway, since
                             * the endpoint is reachable directly.
                             */
                            $itype = $f['type'] === 'email' ? 'email'
                                   : ($f['type'] === 'phone' ? 'tel' : 'text');
                            ?>
                            <label class="block font-bold text-neutral-700 mb-1" for="<?= $id ?>"><?= $f['label'] ?></label>
                            <input type="<?= $itype ?>"
                                   id="<?= $id ?>" name="<?= $key ?>"
                                   value="<?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8') ?>"
                                   maxlength="<?= $cap ?>"
                                   autocomplete="off"
                                   class="<?= sa_in() ?>">
                        <?php endif; ?>

                        <?php if ($f['type'] !== 'int'): ?>
                            <?php /* No cap line for the int field. An integer
                                     * column has no character_maximum_length,
                                     * so the cap fell back to 4000, and
                                     * printing "সর্বোচ্চ ৪০০০ অক্ষর" under a
                                     * ০/১ switch would be telling the
                                     * operator a limit that does not exist. */ ?>
                            <p class="mt-1 text-[11px] text-neutral-400">
                                সর্বোচ্চ <span class="bengali-num"><?= bn_number($cap) ?></span> অক্ষর
                            </p>
                        <?php endif; ?>
                        <?= sa_hint($f['hint']) ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <?php if ($gkey !== 'legacy'): ?>
            <div class="px-4 sm:px-5 py-3 border-t border-neutral-200 bg-neutral-50 flex justify-end">
                <button type="submit"
                        class="px-4 py-2 rounded-lg bg-indigo-700 hover:bg-indigo-800 text-white font-bold text-xs cursor-pointer transition-colors">
                    সংরক্ষণ করুন
                </button>
            </div>
        <?php endif; ?>
    </form>
<?php endforeach; ?>

<!-- ===================== Per-tenant contact details ===================== -->
<form class="bg-white border border-neutral-200 rounded-2xl shadow-xs overflow-hidden"
      onsubmit="return saSaveTenant(event)">

    <div class="px-4 sm:px-5 py-3.5 border-b border-neutral-200">
        <h2 class="text-sm font-bold text-neutral-900">ইউনিয়ন পরিষদের যোগাযোগের তথ্য</h2>
        <p class="text-[11px] text-neutral-600 mt-1 leading-relaxed">
            ইউনিয়ন পরিষদের পাবলিক নাগরিক পোর্টালে প্রদর্শিত হেল্পডেস্ক নম্বর, হোয়াটসঅ্যাপ ও যোগাযোগের তথ্য এখান থেকে সম্পাদনা করুন।
        </p>
    </div>

    <div class="p-4 sm:p-5">
        <label class="block font-bold text-neutral-700 mb-1" for="saTenantSel">যে ইউনিয়ন পরিষদের তথ্য বদলাতে চান</label>
        <div class="flex flex-col sm:flex-row gap-2">
            <select id="saTenantSel" class="<?= sa_in() ?> flex-1">
                <option value="">— ইউনিয়ন পরিষদ বেছে নিন —</option>
                <?php foreach ($tenants as $t): ?>
                    <option value="<?= (int) $t->ins_titute_p_idds ?>"
                        <?= ($selected && (int) $selected->ins_titute_p_idds === (int) $t->ins_titute_p_idds) ? 'selected' : '' ?>>
                        <?= htmlspecialchars(
                            $t->ins_name . '  (' . ($t->sub_domains ?: '—') . ')',
                            ENT_QUOTES, 'UTF-8'
                        ) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="button"
                    onclick="saLoadTenant()"
                    class="px-4 py-2 rounded-lg bg-neutral-900 hover:bg-black text-white font-bold text-xs cursor-pointer transition-colors shrink-0">
                তথ্য দেখুন
            </button>
        </div>
        <p class="mt-1.5 text-[11px] text-neutral-500">
            টাইপ করে শুরু করলেও বাকি নাম খুঁজে দেখাবে —
            <input type="search" id="saTenantFind" placeholder="নাম বা ডোমেইন লিখুন…"
                   oninput="saFilterTenants(this.value)"
                   class="mt-2 w-full p-2.5 rounded-lg border border-neutral-300 text-xs focus:border-indigo-600 focus:outline-none">
        </p>
    </div>

    <div id="saTenantBody" class="hidden px-4 sm:px-5 pb-5">
        <div class="grid sm:grid-cols-2 gap-4 pt-1">
            <?php foreach ($tenant_specs as $f):
                $tcap = $tenant_caps[$f['key']] ?? 4000;
            ?>
                <div class="<?= $f['type'] === 'textarea' ? 'sm:col-span-2' : '' ?>">
                    <label class="block font-bold text-neutral-700 mb-1" for="saT-<?= $f['key'] ?>">
                        <?= $f['label'] ?>
                    </label>
                    <?php if ($f['type'] === 'textarea'): ?>
                        <textarea id="saT-<?= $f['key'] ?>" rows="2" maxlength="<?= $tcap ?>"
                                  class="<?= sa_in('resize-y') ?>"></textarea>
                    <?php else: ?>
                        <input id="saT-<?= $f['key'] ?>" maxlength="<?= $tcap ?>" autocomplete="off"
                               class="<?= sa_in() ?>">
                    <?php endif; ?>
                    <?= sa_hint($f['hint']) ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="flex justify-end gap-2 mt-4 pt-3 border-t border-neutral-200">
            <span id="saTenantMsg" class="text-[11px] font-bold self-center mr-auto"></span>
            <button type="submit" id="saTenantSave"
                    class="px-4 py-2 rounded-lg bg-indigo-700 hover:bg-indigo-800 text-white font-bold text-xs cursor-pointer transition-colors">
                সংরক্ষণ করুন
            </button>
        </div>
    </div>
</form>

<script>
    /**
     * The one fetch helper.
     *
     * `body` is passed in rather than serialised here, because the two
     * callers differ: a text panel sends a JSON string, an asset upload
     * sends a FormData object and must NOT have its Content-Type set —
     * the browser adds the multipart boundary itself, and setting the
     * header by hand produces a request the server cannot parse.
     */
    const SST = (path, body, isForm) => fetch('sadmin_settings/' + path, {
        method: 'POST',
        headers: isForm ? {} : {'Content-Type': 'application/json'},
        body: body
    }).then(r => r.json().catch(() => ({
        success: false,
        message: 'সার্ভার থেকে অগ্রহণযোগ্য উত্তর আসেনি (HTTP ' + r.status + ')।'
    })));

    function saStatus(group, kind, text) {
        const el = document.getElementById('saSt-' + group);
        if (!el) return;
        el.className = 'text-[11px] font-bold ' + (
            kind === 'ok'  ? 'text-emerald-600' :
            kind === 'err' ? 'text-rose-600'    : 'text-neutral-400'
        );
        el.textContent = text;
    }

    /**
     * Save one platform panel.
     *
     * The form's own inputs are read with FormData rather than by name,
     * so a field the operator left out of the DOM is simply not sent —
     * and Sadmin_settings::api_save_platform() only writes keys it finds
     * in its group whitelist, so an extra key cannot reach the database.
     */
    async function saSaveGroup(event, group) {
        event.preventDefault();
        saStatus(group, '', 'সংরক্ষণ হচ্ছে…');

        const payload = {};
        document.getElementById('saGrp-' + group).querySelectorAll('input,select,textarea')
            .forEach(el => { if (el.name) payload[el.name] = el.value; });
        payload.group = group;

        try {
            const r = await SST('api_save_platform', JSON.stringify(payload));
            saStatus(group, r.success ? 'ok' : 'err', r.message);
        } catch (e) {
            saStatus(group, 'err', 'সার্ভারে সংযোগ স্থাপন করা যায়নি।');
        }
        return false;
    }

    async function saUploadAsset(key, inputId) {
        const input = document.getElementById(inputId);
        if (!input.files || input.files.length === 0) {
            saStatus('identity', 'err', 'আগে একটি ফাইল বেছে নিন।');
            return;
        }

        const group = 'identity';
        saStatus(group, '', 'আপলোড হচ্ছে…');

        const fd = new FormData();
        fd.append('key', key);
        fd.append('file', input.files[0]);

        try {
            const r = await SST('api_upload_asset', fd, true);
            saStatus(group, r.success ? 'ok' : 'err', r.message);
            if (r.success) setTimeout(() => location.reload(), 700);
        } catch (e) {
            saStatus(group, 'err', 'সার্ভারে সংযোগ স্থাপন করা যায়নি।');
        }
    }

    async function saClearAsset(key) {
        const confirmed = await vConfirm('অ্যাসেট মুছে ফেলা', 'অ্যাসেটটি কি মুছে ফেলবেন? ফাইলটি সার্ভার থেকে স্থায়ীভাবে মুছে যাবে।', { danger: true });
        if (!confirmed) return;

        saStatus('identity', '', 'মুছে ফেলা হচ্ছে…');
        const fd = new FormData();
        fd.append('key', key);
        fd.append('clear', '1');

        try {
            const r = await SST('api_upload_asset', fd, true);
            saStatus('identity', r.success ? 'ok' : 'err', r.message);
            if (r.success) setTimeout(() => location.reload(), 700);
        } catch (e) {
            saStatus('identity', 'err', 'সার্ভারে সংযোগ স্থাপন করা যায়নি।');
        }
    }

    /* -------- per-tenant -------- */

    let saTenantId = 0;

    /**
     * The tenant list, as data rather than as markup, so it can be
     * filtered and rebuilt.
     *
     * Emitted from the same $tenants the <select> was rendered from, and
     * that duplication is deliberate: a filter over the existing <option>
     * elements needs to hide them, and `hidden` on an <option> is not
     * honoured by Chrome — the list does not narrow at all and the feature
     * reads as broken. Rebuilding the list is the only approach that
     * behaves the same everywhere.
     */
    const SA_TENANTS = [
        <?php foreach ($tenants as $t): ?>
            {
                id: <?= (int) $t->ins_titute_p_idds ?>,
                label: <?= json_encode(
                    $t->ins_name . '  (' . ($t->sub_domains ?: '—') . ')',
                    JSON_UNESCAPED_UNICODE
                ) ?>
            },
        <?php endforeach; ?>
    ];

    /** Draw the tenant <select> from SA_TENANTS, optionally narrowed. */
    function saDrawTenants(term) {
        const sel = document.getElementById('saTenantSel');
        const q = (term || '').trim().toLowerCase();
        const keep = saTenantId;   // never drop the open tenant by typing

        sel.innerHTML = '';
        sel.appendChild(new Option('— ইউনিয়ন পরিষদ বেছে নিন —', ''));

        let shown = 0;
        SA_TENANTS.forEach(t => {
            if (q !== '' && t.id !== keep && t.label.toLowerCase().indexOf(q) === -1) return;
            const opt = new Option(t.label, String(t.id));
            if (t.id === keep) opt.selected = true;
            sel.appendChild(opt);
            shown++;
        });

        // A search with no hits says so, rather than leaving an empty box
        // that looks like the list failed to load.
        if (shown === 0) {
            const none = new Option('কিছু মেলেনি', '');
            none.disabled = true;
            sel.appendChild(none);
        }
    }

    function saFilterTenants(term) {
        saDrawTenants(term);
    }

    async function saLoadTenant() {
        const sel = document.getElementById('saTenantSel');
        const id = parseInt(sel.value, 10);
        if (!id) {
            alert('আগে একটি ইউনিয়ন পরিষদ বেছে নিন।');
            return;
        }

        saTenantId = id;
        document.getElementById('saTenantBody').classList.add('hidden');
        saTenantMsg('', '');

        try {
            const r = await SST('api_get_tenant', JSON.stringify({tenant_id: id}));
            if (!r.success) { alert(r.message); return; }

            for (const [k, f] of Object.entries(r.tenant.fields)) {
                const el = document.getElementById('saT-' + k);
                if (el) el.value = f.value;
            }
            document.getElementById('saTenantBody').classList.remove('hidden');
        } catch (e) {
            alert('সার্ভারে সংযোগ স্থাপন করা যায়নি।');
        }
    }

    function saTenantMsg(kind, text) {
        const el = document.getElementById('saTenantMsg');
        el.className = 'text-[11px] font-bold self-center mr-auto ' + (
            kind === 'ok'  ? 'text-emerald-600' :
            kind === 'err' ? 'text-rose-600'    : ''
        );
        el.textContent = text;
    }

    async function saSaveTenant(event) {
        event.preventDefault();
        if (!saTenantId) { alert('আগে "তথ্য দেখুন" চাপুন।'); return false; }

        saTenantMsg('', 'সংরক্ষণ হচ্ছে…');

        const payload = {tenant_id: saTenantId};
        <?php foreach ($tenant_specs as $f): ?>
            payload['<?= $f['key'] ?>'] = document.getElementById('saT-<?= $f['key'] ?>').value;
        <?php endforeach; ?>

        try {
            const r = await SST('api_save_tenant', JSON.stringify(payload));
            saTenantMsg(r.success ? 'ok' : 'err', r.message);
        } catch (e) {
            saTenantMsg('err', 'সার্ভারে সংযোগ স্থাপন করা যায়নি।');
        }
        return false;
    }

    <?php if ($selected): ?>
        /* ?tenant=N arrived with the request (a link from the
           subscriptions or tenants page). Open it rather than making the
           operator pick it again. */
        saTenantId = <?= (int) $selected->ins_titute_p_idds ?>;
        saLoadTenant();
    <?php endif; ?>
</script>
