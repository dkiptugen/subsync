document.addEventListener('DOMContentLoaded', () => {



    // ========== IMAGE SEARCH FORM ==========
    const imageSearch = document.getElementById('image-search');
    if (imageSearch) {
        imageSearch.addEventListener('submit', e => {
            e.preventDefault();
            document.getElementById('images_display').innerHTML = '';
        });
    }


    // ========== MODAL RESET ==========
    const myModal = document.getElementById('myModal');
    if (myModal) {
        myModal.addEventListener('hidden.bs.modal', e => {
            const form = e.target.querySelector('form');
            if (form) form.reset();
            const body = e.target.querySelector('.modal-body');
            if (body) body.innerHTML = '';
        });
    }

    // ========== SELECT IMAGE ==========
    document.querySelectorAll('.selectImage').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();
            const id = btn.dataset.id;
            const src = btn.getAttribute('src');
            document.getElementById('mainImage').value = id;
            document.getElementById('thumbnail').src = src;
            const preview = document.getElementById('content-preview');
            preview.classList.add('d-none');
            preview.classList.remove('d-flex');
            document.getElementById('image-modal').classList.remove('show');
        });
    });

    // ========== FILE INPUT UPLOAD ==========
    document.querySelectorAll('.file-input').forEach(input => {
        input.addEventListener('change', async e => {
            const files = Array.from(input.files);
            const formName = input.name;
            const data = new FormData();
            files.forEach(f => data.append(formName, f));

            const progressBar = document.getElementById('uploadProgressBar');
            const progressContainer = document.getElementById('progressBarContainer');
            progressContainer.style.display = 'block';
            progressBar.style.width = '0%';
            progressBar.setAttribute('aria-valuenow', 0);

            try {
                const res = await fetch(input.dataset.url, {
                    method: 'POST',
                    headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
                    body: data
                });
                const result = await res.json();

                if (result === 'invalid') {
                    const errEl = document.getElementById('err');
                    errEl.innerHTML = 'Invalid File!';
                    errEl.style.display = 'block';
                } else {
                    document.querySelector('.upload').classList.add('d-none');
                    document.getElementById('image').src = result.imageloc;
                    document.getElementById('imgname').value = result.imgname;
                    document.getElementById('size').value = result.size;
                    document.getElementById('mime').value = result.mime;

                    const preview = document.getElementById('content-preview');
                    preview.classList.remove('d-none');
                    preview.classList.add('d-flex');
                }
            } catch (err) {
                console.error(err);
                const errEl = document.getElementById('err');
                errEl.innerHTML = 'Upload failed.';
                errEl.style.display = 'block';
            }
        });
    });


});


