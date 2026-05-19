/* Greyshades - drag & drop uploader with progress bar + media preview */
(() => {
    'use strict';

    const form     = document.getElementById('upload-form');
    if (!form) return;
    const input    = document.getElementById('file-input');
    const drop     = document.getElementById('drop-area');
    const browse   = document.getElementById('browse-btn');
    const info     = document.getElementById('file-info');
    const progress = document.getElementById('progress-wrap');
    const bar      = document.getElementById('progress-bar');
    const result   = document.getElementById('upload-result');
    const titleInput = form.querySelector('input[name="title"]');
    const submitBtn  = document.getElementById('upload-submit-btn');

    // Preview elements
    const previewWrap  = document.getElementById('upload-preview');
    const previewVideo = document.getElementById('preview-video');
    const previewImage = document.getElementById('preview-image');
    const previewPdf   = document.getElementById('preview-pdf');
    const previewPpt   = document.getElementById('preview-ppt');

    let selectedFile = null;

    const showInfo = (file) => {
        info.hidden = false;
        info.innerHTML =
            '<div><strong>' + escape(file.name) + '</strong></div>' +
            '<div class="muted small">' + (file.type || 'unknown') + ' · ' + bytes(file.size) + '</div>';
        if (titleInput && !titleInput.value.trim()) {
            titleInput.value = file.name.replace(/\.[^.]+$/, '');
        }
    };

    const showPreview = (file) => {
        // Hide all previews first
        [previewVideo, previewImage, previewPdf, previewPpt].forEach(el => { if (el) el.style.display = 'none'; });
        if (!previewWrap) return;
        previewWrap.classList.remove('visible');

        const type = file.type || '';
        const ext = file.name.split('.').pop().toLowerCase();

        if (type.startsWith('video/') || ext === 'mp4') {
            const url = URL.createObjectURL(file);
            previewVideo.src = url;
            previewVideo.style.display = 'block';
            previewVideo.onloadeddata = () => previewWrap.classList.add('visible');
            previewWrap.classList.add('visible');
        } else if (type.startsWith('image/') || ['png','jpg','jpeg','webp','gif'].includes(ext)) {
            const url = URL.createObjectURL(file);
            previewImage.src = url;
            previewImage.style.display = 'block';
            previewImage.onload = () => previewWrap.classList.add('visible');
            previewWrap.classList.add('visible');
        } else if (type === 'application/pdf' || ext === 'pdf') {
            previewPdf.style.display = 'block';
            previewWrap.classList.add('visible');
        } else if (['ppt','pptx'].includes(ext) || type.includes('powerpoint') || type.includes('presentation')) {
            previewPpt.style.display = 'block';
            previewWrap.classList.add('visible');
        }
    };

    const escape = (s) => s.replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const bytes  = (n) => {
        const u = ['B','KB','MB','GB','TB']; let i = 0;
        while (n >= 1024 && i < u.length - 1) { n /= 1024; i++; }
        return n.toFixed(2) + ' ' + u[i];
    };

    const selectFile = (file) => {
        selectedFile = file;
        const dt = new DataTransfer();
        dt.items.add(file);
        input.files = dt.files;
        showInfo(file);
        showPreview(file);
    };

    drop.addEventListener('click', (e) => {
        if (e.target.closest('button') || e.target.closest('a')) return;
        input.click();
    });
    if (browse) browse.addEventListener('click', () => input.click());

    ['dragenter','dragover'].forEach(ev =>
        drop.addEventListener(ev, (e) => { e.preventDefault(); drop.classList.add('dragover'); }));
    ['dragleave','drop'].forEach(ev =>
        drop.addEventListener(ev, (e) => { e.preventDefault(); drop.classList.remove('dragover'); }));

    drop.addEventListener('drop', (e) => {
        const f = e.dataTransfer.files?.[0];
        if (f) selectFile(f);
    });
    input.addEventListener('change', () => {
        if (input.files?.length) selectFile(input.files[0]);
    });

    // Manual submit via the button
    if (submitBtn) {
        submitBtn.addEventListener('click', () => {
            if (!input.files?.length) {
                // Flash the drop area
                drop.classList.add('dragover');
                setTimeout(() => drop.classList.remove('dragover'), 600);
                return;
            }
            submit();
        });
    }

    const submit = () => {
        const fd  = new FormData(form);
        const xhr = new XMLHttpRequest();

        progress.hidden = false; bar.style.width = '0%';
        result.hidden = true; result.classList.remove('success','error','duplicate');
        if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Uploading...'; }

        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                bar.style.width = ((e.loaded / e.total) * 100).toFixed(1) + '%';
            }
        });
        xhr.addEventListener('load', () => {
            if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19V5M5 12l7-7 7 7"/></svg> Upload file'; }
            try {
                const data = JSON.parse(xhr.responseText);
                if (xhr.status >= 200 && xhr.status < 300 && data.ok) {
                    if (data.duplicate) {
                        result.className = 'upload-result duplicate';
                        result.innerHTML = (data.message || 'Duplicate file detected.') +
                          ' <a href="' + data.media.url + '">View existing</a>';
                    } else {
                        result.className = 'upload-result success';
                        result.innerHTML = 'Upload successful! <a href="' + data.media.url + '">View media</a>';
                        input.value = ''; info.hidden = true;
                        selectedFile = null;
                        if (previewWrap) previewWrap.classList.remove('visible');
                        [previewVideo, previewImage, previewPdf, previewPpt].forEach(el => { if (el) el.style.display = 'none'; });
                    }
                } else {
                    result.className = 'upload-result error';
                    result.textContent = data.error || 'Upload failed.';
                }
            } catch (err) {
                result.className = 'upload-result error';
                result.textContent = 'Server error during upload.';
            }
            result.hidden = false;
            setTimeout(() => { progress.hidden = true; bar.style.width = '0%'; }, 800);
        });
        xhr.addEventListener('error', () => {
            if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19V5M5 12l7-7 7 7"/></svg> Upload file'; }
            result.className = 'upload-result error';
            result.textContent = 'Network error.';
            result.hidden = false;
            progress.hidden = true;
        });

        xhr.open('POST', form.action);
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        if (csrf) xhr.setRequestHeader('X-CSRF-TOKEN', csrf);
        xhr.send(fd);
    };

    // --- Accordion logic for category cards ---
    document.querySelectorAll('[data-accordion]').forEach(btn => {
        btn.addEventListener('click', () => {
            const body = btn.nextElementSibling;
            if (!body) return;
            const isOpen = body.classList.contains('open');
            body.classList.toggle('open', !isOpen);
            btn.classList.toggle('open', !isOpen);
        });
    });

    // --- Section toggle: show/hide category groups based on checked sections ---
    const sectionToggles = document.querySelectorAll('[data-section-toggle]');
    const catGroups = document.querySelectorAll('.cat-group[data-section]');
    
    function updateCatVisibility() {
        const checkedSections = [...sectionToggles]
            .filter(cb => cb.checked)
            .map(cb => cb.value);
        catGroups.forEach(g => {
            g.classList.toggle('active', checkedSections.includes(g.dataset.section));
        });
    }
    
    sectionToggles.forEach(cb => cb.addEventListener('change', updateCatVisibility));
    updateCatVisibility(); // initial state

    // --- Auto-select parent categories when child is checked ---
    document.querySelectorAll('input[name="categories[]"]').forEach(cb => {
        cb.addEventListener('change', () => {
            if (!cb.checked) return;
            // Walk up the DOM to find parent category checkboxes
            let el = cb.closest('.cat-section-body') || cb.parentElement;
            if (!el) return;
            const card = cb.closest('.cat-section-card');
            if (card) {
                const parentCb = card.querySelector('input[name="categories[]"]');
                if (parentCb && parentCb !== cb) parentCb.checked = true;
            }
        });
    });
})();
