/* Greyshades - drag & drop uploader with progress bar */
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

    const showInfo = (file) => {
        info.hidden = false;
        info.innerHTML =
            '<div><strong>' + escape(file.name) + '</strong></div>' +
            '<div class="muted small">' + (file.type || 'unknown') + ' · ' + bytes(file.size) + '</div>';
        if (titleInput && !titleInput.value.trim()) {
            titleInput.value = file.name.replace(/\.[^.]+$/, '');
        }
    };

    const escape = (s) => s.replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const bytes  = (n) => {
        const u = ['B','KB','MB','GB','TB']; let i = 0;
        while (n >= 1024 && i < u.length - 1) { n /= 1024; i++; }
        return n.toFixed(2) + ' ' + u[i];
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
        if (f) {
            const dt = new DataTransfer();
            dt.items.add(f);
            input.files = dt.files;
            showInfo(f);
            submit();
        }
    });
    input.addEventListener('change', () => {
        if (input.files?.length) {
            showInfo(input.files[0]);
            submit();
        }
    });

    const submit = () => {
        const fd  = new FormData(form);
        const xhr = new XMLHttpRequest();

        progress.hidden = false; bar.style.width = '0%';
        result.hidden = true; result.classList.remove('success','error','duplicate');

        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                bar.style.width = ((e.loaded / e.total) * 100).toFixed(1) + '%';
            }
        });
        xhr.addEventListener('load', () => {
            try {
                const data = JSON.parse(xhr.responseText);
                if (xhr.status >= 200 && xhr.status < 300 && data.ok) {
                    if (data.duplicate) {
                        result.className = 'upload-result duplicate';
                        result.innerHTML = (data.message || 'Duplicate file detected.') +
                          ' <a href="' + data.media.url + '">View existing</a>';
                    } else {
                        result.className = 'upload-result success';
                        result.innerHTML = 'Upload successful. <a href="' + data.media.url + '">View media</a>';
                        // reset file input but keep metadata for further uploads
                        input.value = ''; info.hidden = true;
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
})();
