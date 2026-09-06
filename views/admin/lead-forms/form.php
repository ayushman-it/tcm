<?php
$isEdit   = $form !== null;
$action   = $isEdit ? base_url('/admin/lead-forms/' . $form['id']) : base_url('/admin/lead-forms');
$val      = static fn(string $k, $d = '') => e((string)($form[$k] ?? $d));
$fields   = json_decode($form['fields_json'] ?? '[]', true) ?: [
    ['name'=>'name',    'label'=>'Full Name',    'type'=>'text',     'required'=>true],
    ['name'=>'email',   'label'=>'Email Address','type'=>'email',    'required'=>true],
    ['name'=>'phone',   'label'=>'Phone Number', 'type'=>'tel',      'required'=>false],
    ['name'=>'message', 'label'=>'Message',       'type'=>'textarea','required'=>false],
];
$shareUrl = $isEdit ? rtrim(config('app.url'), '/') . base_url('/form/' . ($form['slug'] ?? '')) : null;
?>

<style>
/* ── Lead Form Builder Styles ── */
.lfb-wrap { display: grid; grid-template-columns: 1fr 320px; gap: 16px; align-items: start; }

/* Left: builder area */
.lfb-meta-card, .lfb-fields-card { background: #fff; border: 1px solid #ececec; border-radius: 16px; padding: 20px 22px; margin-bottom: 14px; }

.lfb-section-label {
    font-size: .68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .1em;
    color: #aaa; margin-bottom: 14px; padding-bottom: 10px; border-bottom: 1px solid #f5f5f5;
    display: flex; align-items: center; gap: 7px;
}
.lfb-section-label i { font-size: .78rem; color: #111; }

/* Share banner */
.lfb-share-banner {
    display: flex; align-items: center; gap: 10px;
    background: #f0fdf4; border: 1px solid #bbf7d0;
    border-radius: 11px; padding: 10px 14px; margin-bottom: 16px;
}
.lfb-share-url {
    font-size: .78rem; color: #555; flex: 1; overflow: hidden;
    text-overflow: ellipsis; white-space: nowrap; font-family: monospace;
}

/* Field rows */
.lfb-field-row {
    background: #f9f9f9; border: 1px solid #ececec;
    border-radius: 12px; padding: 14px 16px; margin-bottom: 10px;
    position: relative;
}
.lfb-field-row:hover { border-color: #ddd; }
.lfb-field-row-head {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 12px;
}
.lfb-field-num {
    width: 24px; height: 24px; border-radius: 50%;
    background: #111; color: #fff; font-size: .68rem;
    font-weight: 800; display: grid; place-items: center; flex-shrink: 0;
}
.lfb-field-row-grid { display: grid; grid-template-columns: 1fr 1fr 100px 80px; gap: 8px; }

/* Add field type buttons */
.lfb-add-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 7px; margin-top: 14px; }
.lfb-add-btn {
    display: flex; flex-direction: column; align-items: center; gap: 5px;
    padding: 10px 6px; border-radius: 10px; border: 1.5px dashed #e0e0e0;
    background: #fff; cursor: pointer; font-family: inherit;
    font-size: .7rem; font-weight: 600; color: #888;
    transition: .15s;
}
.lfb-add-btn i { font-size: 1rem; color: #aaa; }
.lfb-add-btn:hover { border-color: #111; color: #111; background: #f9f9f9; }
.lfb-add-btn:hover i { color: #111; }

/* Right: settings panel */
.lfb-settings-card { background: #fff; border: 1px solid #ececec; border-radius: 16px; padding: 18px 20px; margin-bottom: 12px; }

/* Preview card */
.lfb-preview { background: #111; border-radius: 14px; padding: 18px; margin-bottom: 12px; }
.lfb-preview h4 { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: rgba(255,255,255,.4); margin-bottom: 10px; }
.lfb-preview-title { font-size: .95rem; font-weight: 800; color: #fff; margin-bottom: 4px; }
.lfb-preview-url {
    font-size: .68rem; font-family: monospace; color: rgba(255,255,255,.4);
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}

/* Submit btn */
.lfb-submit-btn {
    width: 100%; padding: 13px; background: #111; color: #fff;
    border: none; border-radius: 11px; font-size: .92rem; font-weight: 700;
    font-family: inherit; cursor: pointer; display: flex; align-items: center;
    justify-content: center; gap: 8px; transition: background .15s;
}
.lfb-submit-btn:hover { background: #333; }

@media(max-width:900px) {
    .lfb-wrap { grid-template-columns: 1fr; }
    .lfb-field-row-grid { grid-template-columns: 1fr 1fr; gap: 8px; }
}
@media(max-width:500px) {
    .lfb-field-row-grid { grid-template-columns: 1fr; }
    .lfb-add-grid { grid-template-columns: repeat(2,1fr); }
}
</style>

<!-- Page header -->
<div class="tcm-page-head">
    <div>
        <h2><?= $isEdit ? 'Edit Form' : 'New Lead Form' ?></h2>
        <p><?= $isEdit ? e($form['title']) : 'Build a shareable lead capture form.' ?></p>
    </div>
    <div class="d-flex gap-8">
        <?php if ($isEdit && $shareUrl): ?>
            <a href="<?= e($shareUrl) ?>" target="_blank" class="tcm-btn sm"><i class="bi bi-eye"></i> Preview</a>
        <?php endif; ?>
        <a href="<?= base_url('/admin/lead-forms') ?>" class="tcm-btn ghost sm"><i class="bi bi-arrow-left"></i> Back</a>
    </div>
</div>

<?php if ($isEdit && $shareUrl): ?>
<!-- Share banner -->
<div class="lfb-share-banner">
    <i class="bi bi-share-fill" style="color:#16a34a;flex-shrink:0;"></i>
    <div class="lfb-share-url"><?= e($shareUrl) ?></div>
    <button onclick="navigator.clipboard.writeText('<?= e($shareUrl) ?>').then(function(){var b=this;b.innerHTML='<i class=\'bi bi-check2\'></i> Copied!';b.style.color='#16a34a';setTimeout(function(){b.innerHTML='<i class=\'bi bi-copy\'></i> Copy';b.style.color='';},2000)}.bind(this))"
            class="tcm-btn sm" style="flex-shrink:0;"><i class="bi bi-copy"></i> Copy</button>
    <a href="<?= e($shareUrl) ?>" target="_blank" class="tcm-btn sm primary" style="flex-shrink:0;">
        <i class="bi bi-box-arrow-up-right"></i> Open
    </a>
</div>
<?php endif; ?>

<form method="post" action="<?= $action ?>" id="lfbForm">
<?= csrf_field() ?>

<div class="lfb-wrap">

<!-- ══ LEFT COLUMN ══ -->
<div>

    <!-- Form meta -->
    <div class="lfb-meta-card">
        <div class="lfb-section-label"><i class="bi bi-info-circle"></i> Form Details</div>
        <div class="tcm-field">
            <label>Form Title <span style="color:#dc2626">*</span></label>
            <input class="tcm-input" name="title" required value="<?= $val('title') ?>"
                   placeholder="e.g. React Bootcamp Registration"
                   oninput="document.getElementById('lfbPreviewTitle').textContent=this.value||'Form Title'">
        </div>
        <div class="tcm-field" style="margin-bottom:10px;">
            <label>Description <span style="font-weight:400;color:#aaa;">(shown on the form)</span></label>
            <textarea class="tcm-textarea" name="description" style="min-height:64px;"
                      placeholder="Tell people what this form is for..."><?= $val('description') ?></textarea>
        </div>
        <div class="tcm-grid-2">
            <div class="tcm-field" style="margin-bottom:0;">
                <label>Context / Linked to</label>
                <select class="tcm-select" name="context_type" id="lfbCtxType" onchange="lfbUpdateCtx()">
                    <?php foreach (['general'=>'General Campaign','event'=>'Event','course'=>'Course'] as $ct=>$cl): ?>
                        <option value="<?= $ct ?>" <?= ($form['context_type']??'general')===$ct?'selected':'' ?>><?= $cl ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="tcm-field" style="margin-bottom:0;" id="lfbCtxIdWrap">
                <label>Link to specific item</label>
                <select class="tcm-select" name="context_id" id="lfbCtxId" onchange="lfbPickCtx(this)">
                    <option value="">— None —</option>
                    <?php foreach ($events as $ev): ?>
                        <option value="<?= $ev['id'] ?>" data-type="event" data-title="<?= e($ev['title']) ?>"
                            <?= (int)($form['context_id']??0)===(int)$ev['id']&&($form['context_type']??'')==='event'?'selected':'' ?>>
                            [Event] <?= e($ev['title']) ?></option>
                    <?php endforeach; ?>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= $c['id'] ?>" data-type="course" data-title="<?= e($c['title']) ?>"
                            <?= (int)($form['context_id']??0)===(int)$c['id']&&($form['context_type']??'')==='course'?'selected':'' ?>>
                            [Course] <?= e($c['title']) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="context_title" id="lfbCtxTitle" value="<?= $val('context_title') ?>">
            </div>
        </div>
    </div>

    <!-- Field builder -->
    <div class="lfb-fields-card">
        <div class="lfb-section-label"><i class="bi bi-ui-checks"></i> Form Fields</div>

        <div id="lfbFieldsContainer">
            <?php foreach ($fields as $i => $f): ?>
            <div class="lfb-field-row" data-idx="<?= $i ?>">
                <div class="lfb-field-row-head">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span class="lfb-field-num"><?= $i+1 ?></span>
                        <span style="font-size:.8rem;font-weight:600;color:#555;"><?= e($f['label']??$f['name']) ?></span>
                        <span class="tcm-badge gray" style="font-size:.6rem;"><?= e($f['type']??'text') ?></span>
                        <?php if ($f['required']??false): ?><span class="tcm-badge red" style="font-size:.6rem;">required</span><?php endif; ?>
                    </div>
                    <button type="button" onclick="lfbRemove(this)" class="tcm-btn sm danger" style="padding:3px 8px;"><i class="bi bi-trash"></i></button>
                </div>
                <div class="lfb-field-row-grid">
                    <div>
                        <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#aaa;margin-bottom:4px;">Field Name</div>
                        <input class="tcm-input" name="field_name[]" value="<?= e($f['name']??'') ?>" placeholder="field_slug" required style="font-size:.82rem;">
                    </div>
                    <div>
                        <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#aaa;margin-bottom:4px;">Label (shown to user)</div>
                        <input class="tcm-input" name="field_label[]" value="<?= e($f['label']??'') ?>" placeholder="Display label" style="font-size:.82rem;">
                    </div>
                    <div>
                        <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#aaa;margin-bottom:4px;">Type</div>
                        <select class="tcm-select lfb-type-sel" name="field_type[]" style="font-size:.82rem;" onchange="lfbToggleOptions(this)">
                            <?php foreach (['text','email','tel','number','textarea','select','date','url'] as $t): ?>
                                <option value="<?= $t ?>" <?= ($f['type']??'text')===$t?'selected':'' ?>><?= ucfirst($t) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#aaa;margin-bottom:4px;">Required</div>
                        <select class="tcm-select" name="field_required[]" style="font-size:.82rem;">
                            <option value="0" <?= !($f['required']??false)?'selected':'' ?>>No</option>
                            <option value="1" <?= ($f['required']??false)?'selected':'' ?>>Yes</option>
                        </select>
                    </div>
                </div>
                <!-- Options row — only visible for select type -->
                <?php $existingOpts = implode(', ', $f['options'] ?? []); ?>
                <div class="lfb-options-row" style="margin-top:8px;<?= ($f['type']??'text')!=='select'?'display:none;':'' ?>">
                    <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#aaa;margin-bottom:4px;">
                        <i class="bi bi-list-ul"></i> Dropdown Options
                        <span style="font-weight:400;text-transform:none;letter-spacing:0;color:#bbb;"> — comma separated (e.g. Option 1, Option 2, Option 3)</span>
                    </div>
                    <input class="tcm-input" name="field_options[]"
                           value="<?= e($existingOpts) ?>"
                           placeholder="e.g. Beginner, Intermediate, Advanced"
                           style="font-size:.82rem;">
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Add field buttons — Google Form style -->
        <div style="margin-top:6px;">
            <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#aaa;margin-bottom:8px;">Add a field</div>
            <div class="lfb-add-grid">
                <button type="button" onclick="lfbAdd('text','Text','text')" class="lfb-add-btn"><i class="bi bi-fonts"></i>Short Text</button>
                <button type="button" onclick="lfbAdd('email','Email Address','email')" class="lfb-add-btn"><i class="bi bi-envelope"></i>Email</button>
                <button type="button" onclick="lfbAdd('phone','Phone Number','tel')" class="lfb-add-btn"><i class="bi bi-telephone"></i>Phone</button>
                <button type="button" onclick="lfbAdd('message','Message','textarea')" class="lfb-add-btn"><i class="bi bi-chat-text"></i>Long Text</button>
                <button type="button" onclick="lfbAdd('number','Number','number')" class="lfb-add-btn"><i class="bi bi-hash"></i>Number</button>
                <button type="button" onclick="lfbAdd('date','Date','date')" class="lfb-add-btn"><i class="bi bi-calendar3"></i>Date</button>
                <button type="button" onclick="lfbAdd('option','Choose One','select')" class="lfb-add-btn"><i class="bi bi-list-ul"></i>Dropdown</button>
                <button type="button" onclick="lfbAdd('website','Website URL','url')" class="lfb-add-btn"><i class="bi bi-globe2"></i>URL</button>
            </div>
        </div>
    </div>

</div>

<!-- ══ RIGHT COLUMN ══ -->
<div>

    <!-- Live preview -->
    <div class="lfb-preview">
        <h4><i class="bi bi-eye" style="margin-right:4px;"></i> Preview</h4>
        <div class="lfb-preview-title" id="lfbPreviewTitle"><?= $val('title', 'Form Title') ?></div>
        <div class="lfb-preview-url">thecodemunk.in/form/<?= $val('slug', 'your-form-slug') ?></div>
    </div>

    <!-- Settings -->
    <div class="lfb-settings-card">
        <div class="lfb-section-label"><i class="bi bi-gear"></i> Settings</div>

        <div class="tcm-field">
            <label>Submit Button Text</label>
            <input class="tcm-input" name="cta_text" value="<?= $val('cta_text', 'Submit') ?>" placeholder="Submit">
        </div>
        <div class="tcm-field">
            <label>Thank You Message</label>
            <textarea class="tcm-textarea" name="thank_you_message" style="min-height:62px;"
                      placeholder="Thank you! We will be in touch shortly."><?= $val('thank_you_message', 'Thank you! We will be in touch shortly.') ?></textarea>
        </div>
        <div class="tcm-field">
            <label>Status</label>
            <select class="tcm-select" name="status">
                <option value="active"   <?= ($form['status']??'active')==='active'  ?'selected':'' ?>>✅ Active (public)</option>
                <option value="inactive" <?= ($form['status']??'')==='inactive'?'selected':'' ?>>⏸️ Inactive (hidden)</option>
            </select>
        </div>
        <div class="tcm-field" style="margin-bottom:0;">
            <label style="display:flex;align-items:center;gap:8px;font-size:.84rem;cursor:pointer;font-weight:normal;text-transform:none;letter-spacing:0;">
                <input type="checkbox" name="whatsapp_redirect" value="1"
                       <?= (int)($form['whatsapp_redirect']??1)?'checked':'' ?>
                       style="accent-color:#111;width:15px;height:15px;">
                Redirect to WhatsApp after submit
            </label>
            <div style="font-size:.7rem;color:#aaa;margin-top:4px;">Lead is saved first, then user is sent to WhatsApp.</div>
        </div>
    </div>

    <!-- Save -->
    <button type="submit" class="lfb-submit-btn">
        <i class="bi bi-check2-circle"></i>
        <?= $isEdit ? 'Save Changes' : 'Create & Get Shareable Link' ?>
    </button>

    <?php if ($isEdit && $shareUrl): ?>
    <a href="<?= e($shareUrl) ?>" target="_blank"
       class="tcm-btn w-full" style="justify-content:center;margin-top:8px;">
        <i class="bi bi-box-arrow-up-right"></i> Open Live Form
    </a>
    <?php endif; ?>

</div>
</div>

<!-- hidden template for dynamic field rows -->
<template id="lfbFieldTpl">
    <div class="lfb-field-row">
        <div class="lfb-field-row-head">
            <div style="display:flex;align-items:center;gap:8px;">
                <span class="lfb-field-num" style="font-size:.68rem;"></span>
                <span style="font-size:.8rem;font-weight:600;color:#555;" class="lfb-row-label"></span>
                <span class="tcm-badge gray lfb-row-type-badge" style="font-size:.6rem;"></span>
            </div>
            <button type="button" onclick="lfbRemove(this)" class="tcm-btn sm danger" style="padding:3px 8px;"><i class="bi bi-trash"></i></button>
        </div>
        <div class="lfb-field-row-grid">
            <div>
                <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#aaa;margin-bottom:4px;">Field Name</div>
                <input class="tcm-input" name="field_name[]" placeholder="field_slug" required style="font-size:.82rem;">
            </div>
            <div>
                <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#aaa;margin-bottom:4px;">Label (shown to user)</div>
                <input class="tcm-input" name="field_label[]" placeholder="Display label" style="font-size:.82rem;">
            </div>
            <div>
                <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#aaa;margin-bottom:4px;">Type</div>
                <select class="tcm-select lfb-type-sel" name="field_type[]" style="font-size:.82rem;" onchange="lfbToggleOptions(this)">
                    <option value="text">Text</option>
                    <option value="email">Email</option>
                    <option value="tel">Phone</option>
                    <option value="number">Number</option>
                    <option value="textarea">Textarea</option>
                    <option value="select">Dropdown</option>
                    <option value="date">Date</option>
                    <option value="url">URL</option>
                </select>
            </div>
            <div>
                <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#aaa;margin-bottom:4px;">Required</div>
                <select class="tcm-select" name="field_required[]" style="font-size:.82rem;">
                    <option value="0">No</option>
                    <option value="1">Yes</option>
                </select>
            </div>
        </div>
        <!-- Options row — only visible for select type -->
        <div class="lfb-options-row" style="margin-top:8px;display:none;">
            <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#aaa;margin-bottom:4px;">
                <i class="bi bi-list-ul"></i> Dropdown Options
                <span style="font-weight:400;text-transform:none;letter-spacing:0;color:#bbb;"> — comma separated (e.g. Option 1, Option 2, Option 3)</span>
            </div>
            <input class="tcm-input" name="field_options[]"
                   placeholder="e.g. Beginner, Intermediate, Advanced"
                   style="font-size:.82rem;">
        </div>
    </div>
</template>

</form>

<script>
function lfbRenumber() {
    document.querySelectorAll('#lfbFieldsContainer .lfb-field-row').forEach(function(row, i) {
        var n = row.querySelector('.lfb-field-num');
        if (n) n.textContent = i + 1;
    });
}

// Toggle options row when type changes
function lfbToggleOptions(sel) {
    var row = sel.closest('.lfb-field-row');
    if (!row) return;
    var optRow = row.querySelector('.lfb-options-row');
    if (optRow) {
        optRow.style.display = sel.value === 'select' ? 'block' : 'none';
        if (sel.value === 'select') {
            optRow.querySelector('input').focus();
        }
    }
}

// Add field from type-button click
function lfbAdd(slug, label, type) {
    var tpl  = document.getElementById('lfbFieldTpl').content.cloneNode(true);
    var cont = document.getElementById('lfbFieldsContainer');

    tpl.querySelector('[name="field_name[]"]').value  = slug + '_' + Date.now().toString().slice(-4);
    tpl.querySelector('[name="field_label[]"]').value = label;
    var typeSel = tpl.querySelector('[name="field_type[]"]');
    typeSel.value = type;
    tpl.querySelector('.lfb-row-label').textContent   = label;
    tpl.querySelector('.lfb-row-type-badge').textContent = type;

    // Show options row if dropdown type
    var optRow = tpl.querySelector('.lfb-options-row');
    if (optRow) {
        optRow.style.display = type === 'select' ? 'block' : 'none';
    }

    cont.appendChild(tpl);
    lfbRenumber();
    cont.lastElementChild.scrollIntoView({ behavior: 'smooth', block: 'center' });
    cont.lastElementChild.querySelector('[name="field_name[]"]').focus();
    if (type === 'select') {
        setTimeout(function(){
            cont.lastElementChild.querySelector('[name="field_options[]"]').focus();
        }, 100);
    }
}

// Remove field
function lfbRemove(btn) {
    var row = btn.closest('.lfb-field-row');
    row.style.opacity = '0';
    row.style.transform = 'scale(.96)';
    row.style.transition = 'opacity .2s, transform .2s';
    setTimeout(function(){ row.remove(); lfbRenumber(); }, 200);
}

// Context type change
function lfbUpdateCtx() {
    var type = document.getElementById('lfbCtxType').value;
    var wrap = document.getElementById('lfbCtxIdWrap');
    if (wrap) wrap.style.display = type === 'general' ? 'none' : '';
    // filter dropdown options
    document.querySelectorAll('#lfbCtxId option').forEach(function(o) {
        if (!o.value) { o.style.display = ''; return; }
        o.style.display = (o.getAttribute('data-type') === type) ? '' : 'none';
    });
    document.getElementById('lfbCtxId').value = '';
    document.getElementById('lfbCtxTitle').value = '';
}

function lfbPickCtx(sel) {
    var opt = sel.options[sel.selectedIndex];
    document.getElementById('lfbCtxTitle').value = opt ? (opt.getAttribute('data-title') || '') : '';
}

// Init
(function(){
    var type = document.getElementById('lfbCtxType').value;
    if (document.getElementById('lfbCtxIdWrap')) {
        document.getElementById('lfbCtxIdWrap').style.display = type === 'general' ? 'none' : '';
    }
    document.getElementById('lfbCtxId').addEventListener('change', function(){ lfbPickCtx(this); });
})();
</script>
