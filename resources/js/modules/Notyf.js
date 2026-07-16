import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';
export const notify = new Notyf({
    duration: 5000,
    position: {x: 'right', y: 'top'}
});
document.addEventListener('click', async function (e) {

    const btn = e.target.closest('.dt-action');
    if (!btn) return;

    e.preventDefault();

    const url = btn.dataset.url || btn.href;
    const method = btn.dataset.method || 'GET';

    try {

        const res = await fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });

        const contentType = res.headers.get('content-type') || '';
        const raw = await res.text();

        let Mess = {};

        if (contentType.includes('application/json')) {
            Mess = JSON.parse(raw);
        } else {
            throw new Error(
                raw.startsWith('<')
                    ? 'Server returned HTML instead of JSON. Check login/session or backend error.'
                    : raw || 'Unexpected server response.'
            );
        }

        if (!res.ok) {
            throw new Error(
                Mess.msg ||
                Mess.message ||
                `Request failed with status ${res.status}`
            );
        }

        if (Mess.status === true) {

            notify.success(Mess.msg || 'Success');

            setTimeout(() => {

                if (Mess.url) {
                    window.location.href = Mess.url;
                }

                // optional datatable reload
                if (window.LaravelDataTables) {
                    Object.values(LaravelDataTables).forEach(dt => dt.ajax.reload(null,false));
                }

            }, 800);

        } else {
            throw new Error(Mess.msg || 'Operation failed');
        }

    } catch (error) {
        console.error(error);
        notify.error(error.message || 'Something went wrong.');
    }

});


// Event delegation (replaces $(document).on)
document.addEventListener('submit', async function (e) {
    if (!e.target.classList.contains('create-form')) return;

    e.preventDefault();

    const frm = e.target;
    const formData = new FormData(frm);

    try {
        const res = await fetch(frm.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        });

        const contentType = res.headers.get('content-type') || '';
        const raw = await res.text();

        let Mess = {};
        if (contentType.includes('application/json')) {
            Mess = JSON.parse(raw);
        } else {
            throw new Error(raw.startsWith('<')
                ? 'Server returned HTML instead of JSON. Check login/session or backend error.'
                : raw || 'Unexpected server response.');
        }

        if (!res.ok) {
            throw new Error(
                Mess.msg ||
                Mess.message ||
                (Mess.errors ? Object.values(Mess.errors).flat().join(' ') : '') ||
                `Request failed with status ${res.status}`
            );
        }

        if (Mess.status === true) {
            notify.success(Mess.msg || 'Success');
            setTimeout(() => {
                if (Mess.url) window.location.href = Mess.url;
            }, 1000);
        } else {
            throw new Error(Mess.msg || 'Operation failed');
        }
    } catch (error) {
        console.error(error);
        notify.error(error.message || 'Something went wrong. Please try again.');
    }
});
