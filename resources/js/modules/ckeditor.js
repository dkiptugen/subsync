import 'ckeditor5/ckeditor5.css';

import {
    Bold,
    ButtonView,
    ClassicEditor,
    FileRepository,
    FontColor,
    FontFamily,
    FontSize,
    GeneralHtmlSupport,
    Heading,
    HtmlEmbed,
    Image,
    ImageCaption,
    ImageInsert,
    ImageResize,
    ImageStyle,
    ImageToolbar,
    ImageUpload,
    Italic,
    Link,
    List,
    MediaEmbed,
    Plugin,
    Strikethrough,
    Style,
    Subscript,
    Superscript,
    Table,
    TableCellProperties,
    TableProperties,
    TableToolbar,
    Underline,
} from 'ckeditor5';

class LaravelMediaUploadAdapter {
    constructor(loader, uploadUrl, csrfToken) {
        this.loader = loader;
        this.uploadUrl = uploadUrl;
        this.csrfToken = csrfToken;
        this.xhr = null;
    }

    upload() {
        return this.loader.file.then((file) => {
            return new Promise((resolve, reject) => {
                this.xhr = new XMLHttpRequest();
                this.xhr.open('POST', this.uploadUrl, true);
                this.xhr.responseType = 'json';
                this.xhr.setRequestHeader('Accept', 'application/json');
                this.xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

                if (this.csrfToken) {
                    this.xhr.setRequestHeader('X-CSRF-TOKEN', this.csrfToken);
                }

                this.xhr.addEventListener('error', () => reject('Upload failed.'));
                this.xhr.addEventListener('abort', () => reject('Upload aborted.'));
                this.xhr.addEventListener('load', () => {
                    const response = this.xhr.response;

                    if (!response || this.xhr.status >= 400) {
                        reject(response?.message || 'Upload failed.');

                        return;
                    }

                    resolve({
                        default: response.url,
                    });
                });

                if (this.xhr.upload) {
                    this.xhr.upload.addEventListener('progress', (event) => {
                        if (!event.lengthComputable) {
                            return;
                        }

                        this.loader.uploadTotal = event.total;
                        this.loader.uploaded = event.loaded;
                    });
                }

                const data = new FormData();
                data.append('file', file);

                this.xhr.send(data);
            });
        });
    }

    abort() {
        if (this.xhr) {
            this.xhr.abort();
        }
    }
}

class LaravelMediaUploadPlugin extends Plugin {
    init() {
        const uploadUrl = this.editor.config.get('mediaLibrary.uploadUrl');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        if (!uploadUrl) {
            return;
        }

        this.editor.plugins.get(FileRepository).createUploadAdapter = (loader) => {
            return new LaravelMediaUploadAdapter(loader, uploadUrl, csrfToken);
        };
    }
}

class MediaLibraryButtonPlugin extends Plugin {
    init() {
        this.editor.ui.componentFactory.add('mediaLibrary', () => {
            const button = new ButtonView(this.editor.locale);

            button.set({
                label: 'Library',
                tooltip: true,
                withText: true,
            });

            button.on('execute', () => {
                const mediaLibrary = this.editor.config.get('mediaLibrary');

                if (typeof mediaLibrary?.open === 'function') {
                    mediaLibrary.open(this.editor);
                }
            });

            return button;
        });
    }
}

class MapsEmbedButtonPlugin extends Plugin {
    init() {
        this.editor.ui.componentFactory.add('mapsEmbed', () => {
            const button = new ButtonView(this.editor.locale);

            button.set({
                label: 'Map',
                tooltip: true,
                withText: true,
            });

            button.on('execute', () => {
                const maps = this.editor.config.get('mapsEmbed');

                if (typeof maps?.open === 'function') {
                    maps.open(this.editor);
                }
            });

            return button;
        });
    }
}

class RelatedContentButtonPlugin extends Plugin {
    init() {
        this.editor.ui.componentFactory.add('relatedContent', () => {
            const button = new ButtonView(this.editor.locale);

            button.set({
                label: 'Related',
                tooltip: true,
                withText: true,
            });

            button.on('execute', () => {
                const relatedContent = this.editor.config.get('relatedContent');

                if (typeof relatedContent?.open === 'function') {
                    relatedContent.open(this.editor);
                }
            });

            return button;
        });
    }
}

class CleanFormattingButtonPlugin extends Plugin {
    init() {
        this.editor.ui.componentFactory.add('cleanFormatting', () => {
            const button = new ButtonView(this.editor.locale);

            button.set({
                label: 'Clean',
                tooltip: 'Remove heavy inline formatting',
                withText: true,
            });

            button.on('execute', () => {
                const cleanedHtml = sanitizeOfficeHtml(this.editor.getData());

                if (cleanedHtml !== this.editor.getData()) {
                    this.editor.setData(cleanedHtml);
                }
            });

            return button;
        });
    }
}

class AutoCleanWordFormattingPlugin extends Plugin {
    init() {
        const clipboardPipeline = this.editor.plugins.get('ClipboardPipeline');

        clipboardPipeline.on('inputTransformation', (event, data) => {
            const html = data.dataTransfer?.getData('text/html');

            if (!html) {
                return;
            }

            const cleanedHtml = sanitizeOfficeHtml(html);

            if (cleanedHtml === html) {
                return;
            }

            data.content = this.editor.data.processor.toView(cleanedHtml);
        }, { priority: 'high' });
    }
}

const extraPlugins = [
    Heading,
    Bold,
    Italic,
    Underline,
    FontColor,
    FontFamily,
    FontSize,
    Link,
    List,
    Strikethrough,
    Superscript,
    Subscript,
    Style,
    Image,
    ImageCaption,
    ImageInsert,
    ImageResize,
    ImageStyle,
    ImageToolbar,
    ImageUpload,
    MediaEmbed,
    Table,
    TableToolbar,
    TableProperties,
    TableCellProperties,
    GeneralHtmlSupport,
    HtmlEmbed,
    LaravelMediaUploadPlugin,
    MediaLibraryButtonPlugin,
    MapsEmbedButtonPlugin,
    RelatedContentButtonPlugin,
    CleanFormattingButtonPlugin,
    AutoCleanWordFormattingPlugin,
];

const baseToolbar = [
    'heading',
    '|',
    'bold',
    'italic',
    'underline',
    'strikethrough',
    'superscript',
    'subscript',
    '|',
    'fontColor',
    'fontFamily',
    'fontSize',
    'style',
    'link',
    '|',
    'bulletedList',
    'numberedList',
    '|',
    'cleanFormatting',
    'insertImage',
    'mediaLibrary',
    'insertTable',
    'mediaEmbed',
    'htmlEmbed',
    'mapsEmbed',
    'relatedContent',
    '|',
    'undo',
    'redo',
];

function createModalShell(id, title, subtitle, bodyMarkup) {
    const modalElement = document.createElement('div');
    modalElement.className = 'modal fade';
    modalElement.id = id;
    modalElement.tabIndex = -1;
    modalElement.setAttribute('aria-hidden', 'true');
    modalElement.style.display = 'none';
    modalElement.style.position = 'fixed';
    modalElement.style.inset = '0';
    modalElement.style.zIndex = '1080';
    modalElement.style.background = 'rgba(15, 23, 42, 0.55)';
    modalElement.style.overflowY = 'auto';
    modalElement.style.padding = '2rem 1rem';
    modalElement.innerHTML = `
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-1">${title}</h5>
                        <p class="text-muted small mb-0">${subtitle}</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">${bodyMarkup}</div>
            </div>
        </div>
    `;

    document.body.appendChild(modalElement);

    const dialogElement = modalElement.querySelector('.modal-dialog');
    const contentElement = modalElement.querySelector('.modal-content');

    dialogElement.style.maxWidth = '1140px';
    dialogElement.style.margin = '0 auto';
    dialogElement.style.minHeight = 'calc(100vh - 4rem)';
    dialogElement.style.display = 'flex';
    dialogElement.style.alignItems = 'center';

    contentElement.style.width = '100%';
    contentElement.style.background = '#ffffff';
    contentElement.style.color = '#0f172a';
    contentElement.style.border = '1px solid rgba(15, 23, 42, 0.08)';
    contentElement.style.borderRadius = '1rem';
    contentElement.style.boxShadow = '0 25px 60px rgba(15, 23, 42, 0.25)';
    contentElement.style.overflow = 'hidden';

    const headerElement = modalElement.querySelector('.modal-header');
    const bodyElement = modalElement.querySelector('.modal-body');

    headerElement.style.background = '#eeeeee';
    headerElement.style.padding = '1.25rem 1.5rem';
    headerElement.style.borderBottom = '1px solid rgba(15, 23, 42, 0.08)';
    headerElement.style.display = 'flex';
    headerElement.style.alignItems = 'flex-start';
    headerElement.style.justifyContent = 'space-between';
    headerElement.style.gap = '1rem';

    const closeButtonElement = modalElement.querySelector('.btn-close');
    closeButtonElement.style.marginLeft = 'auto';
    closeButtonElement.style.flexShrink = '0';

    bodyElement.style.background = '#ffffff';
    bodyElement.style.padding = '1.5rem';

    return modalElement;
}

function createModalController(modalElement) {
    const hide = () => {
        modalElement.style.display = 'none';
        modalElement.classList.remove('show');
        modalElement.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
    };

    const show = () => {
        modalElement.style.display = 'block';
        modalElement.classList.add('show');
        modalElement.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
        document.body.style.overflow = 'hidden';
    };

    modalElement.addEventListener('click', (event) => {
        if (event.target === modalElement || event.target.closest('[data-bs-dismiss="modal"]')) {
            hide();
        }
    });

    return {
        hide,
        show,
    };
}

function createMediaLibraryManager() {
    const modalElement = createModalShell(
        'ckeditor-media-library-modal',
        'Media library',
        'Upload media or reuse existing images, video, audio, and files.',
        `
            <div class="d-flex gap-2 mb-4" data-media-library-tabs>
                <button type="button" class="btn btn-dark-blue" data-media-library-tab-trigger data-tab="browse">Browse</button>
                <button type="button" class="btn btn-outline-secondary" data-media-library-tab-trigger data-tab="upload">Upload</button>
            </div>
            <section data-media-library-tab-panel="browse">
                <div class="row g-3 align-items-end mb-4">
                    <div class="col-lg-7">
                        <label class="form-label" for="ckeditor-media-search">Search media</label>
                        <input class="form-control" id="ckeditor-media-search" type="search" placeholder="Search by filename">
                    </div>
                    <div class="col-lg-5">
                        <label class="form-label" for="ckeditor-media-caption">Caption</label>
                        <input class="form-control" id="ckeditor-media-caption" type="text" placeholder="Optional caption for inserted media">
                    </div>
                </div>
                <div class="alert alert-danger d-none" data-media-library-error></div>
                <div class="d-flex justify-content-center py-5 d-none" data-media-library-loading>
                    <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
                </div>
                <div class="row g-3" data-media-library-grid></div>
                <div class="text-center text-muted py-5 d-none" data-media-library-empty>No media found.</div>
            </section>
            <section class="d-none" data-media-library-tab-panel="upload">
                <form class="row g-3 align-items-end" data-media-library-upload-form>
                    <div class="col-lg-5">
                        <label class="form-label" for="ckeditor-media-upload-caption">Caption</label>
                        <input class="form-control" id="ckeditor-media-upload-caption" type="text" placeholder="Optional caption for uploaded media">
                    </div>
                    <div class="col-lg-5">
                        <label class="form-label" for="ckeditor-media-library-file">Upload media</label>
                        <input
                            class="form-control"
                            id="ckeditor-media-library-file"
                            name="file"
                            type="file"
                            accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.txt,.zip"
                        >
                    </div>
                    <div class="col-lg-2 d-grid">
                        <button class="btn btn-primary" type="submit">Upload</button>
                    </div>
                </form>
                <div class="alert alert-danger d-none mt-3" data-media-library-upload-error></div>
            </section>
        `,
    );

    const modal = createModalController(modalElement);
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const uploadForm = modalElement.querySelector('[data-media-library-upload-form]');
    const fileInput = modalElement.querySelector('#ckeditor-media-library-file');
    const searchInput = modalElement.querySelector('#ckeditor-media-search');
    const captionInput = modalElement.querySelector('#ckeditor-media-caption');
    const uploadCaptionInput = modalElement.querySelector('#ckeditor-media-upload-caption');
    const errorElement = modalElement.querySelector('[data-media-library-error]');
    const uploadErrorElement = modalElement.querySelector('[data-media-library-upload-error]');
    const loadingElement = modalElement.querySelector('[data-media-library-loading]');
    const emptyElement = modalElement.querySelector('[data-media-library-empty]');
    const gridElement = modalElement.querySelector('[data-media-library-grid]');
    const tabTriggers = modalElement.querySelectorAll('[data-media-library-tab-trigger]');
    const tabPanels = modalElement.querySelectorAll('[data-media-library-tab-panel]');

    const state = {
        editor: null,
        libraryUrl: null,
        uploadUrl: null,
        search: '',
    };

    function resetError() {
        errorElement.textContent = '';
        errorElement.classList.add('d-none');
        uploadErrorElement.textContent = '';
        uploadErrorElement.classList.add('d-none');
    }

    function showError(message, target = 'browse') {
        const targetElement = target === 'upload' ? uploadErrorElement : errorElement;
        targetElement.textContent = message;
        targetElement.classList.remove('d-none');
    }

    function setLoading(isLoading) {
        loadingElement.classList.toggle('d-none', !isLoading);
    }

    function renderPreview(item) {
        if (item.is_image) {
            return `<img src="${item.url}" alt="${escapeHtml(item.name)}" class="card-img-top object-fit-cover" style="height: 160px;">`;
        }

        if (item.mime_type.startsWith('video/')) {
            return `<video class="w-100 border-bottom bg-black" style="height: 160px;" muted controls src="${item.url}"></video>`;
        }

        if (item.mime_type.startsWith('audio/')) {
            return `<div class="d-flex align-items-center justify-content-center bg-light border-bottom p-3" style="height: 160px;">
                <audio class="w-100" controls src="${item.url}"></audio>
            </div>`;
        }

        return `<div class="d-flex align-items-center justify-content-center bg-light border-bottom" style="height: 160px;">
            <span class="fw-semibold text-uppercase text-muted">${escapeHtml(item.mime_type.split('/').pop())}</span>
        </div>`;
    }

    function renderItems(items) {
        gridElement.innerHTML = '';

        if (!items.length) {
            emptyElement.classList.remove('d-none');

            return;
        }

        emptyElement.classList.add('d-none');

        const groups = [
            { key: 'image', label: 'Images' },
            { key: 'video', label: 'Video' },
            { key: 'audio', label: 'Audio' },
            { key: 'document', label: 'Documents' },
        ];

        groups.forEach((group) => {
            const groupItems = items.filter((item) => (item.type || inferMediaType(item)) === group.key);

            if (!groupItems.length) {
                return;
            }

            const section = document.createElement('div');
            section.className = 'col-12';
            section.innerHTML = `
                <div class="d-flex align-items-center justify-content-between mt-2 mb-3">
                    <h6 class="mb-0 text-uppercase text-muted small fw-bold">${group.label}</h6>
                    <span class="badge bg-light text-dark">${groupItems.length}</span>
                </div>
                <div class="row g-3" data-media-group="${group.key}"></div>
            `;

            const sectionGrid = section.querySelector('[data-media-group]');

            groupItems.forEach((item) => {
                const column = document.createElement('div');
                column.className = 'col-md-4 col-xl-3';

                column.innerHTML = `
                    <div class="card h-100 shadow-sm">
                        ${renderPreview(item)}
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title text-truncate" title="${escapeHtml(item.name)}">${escapeHtml(item.name)}</h6>
                            <p class="card-text small text-muted mb-3">${formatSize(item.size)}</p>
                            <button
                                type="button"
                                class="btn btn-outline-primary mt-auto"
                                data-media-library-select
                                data-media-item='${JSON.stringify(item).replace(/'/g, '&#39;')}'
                            >
                                Insert
                            </button>
                        </div>
                    </div>
                `;

                sectionGrid.appendChild(column);
            });

            gridElement.appendChild(section);
        });
    }

    async function loadItems() {
        if (!state.libraryUrl) {
            renderItems([]);

            return;
        }

        resetError();
        setLoading(true);
        emptyElement.classList.add('d-none');

        try {
            const url = new URL(state.libraryUrl, window.location.origin);

            if (state.search) {
                url.searchParams.set('search', state.search);
            }

            const response = await fetch(url, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            const payload = await response.json();

            if (!response.ok) {
                throw new Error(payload?.message || 'Unable to load media library.');
            }

            renderItems(payload.data ?? []);
        } catch (error) {
            renderItems([]);
            showError(error.message || 'Unable to load media library.', 'browse');
        } finally {
            setLoading(false);
        }
    }

    async function uploadFile(event) {
        event.preventDefault();

        if (!state.uploadUrl) {
            showError('Uploads are not configured for this editor.', 'upload');

            return;
        }

        if (!fileInput.files.length) {
            showError('Choose a file before uploading.', 'upload');

            return;
        }

        resetError();

        const formData = new FormData();
        formData.append('file', fileInput.files[0]);

        try {
            const response = await fetch(state.uploadUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                },
                body: formData,
                credentials: 'same-origin',
            });

            const payload = await response.json();

            if (!response.ok) {
                throw new Error(payload?.message || 'Upload failed.');
            }

            fileInput.value = '';
            await loadItems();

            if (payload.data) {
                insertMedia(state.editor, payload.data, uploadCaptionInput.value.trim());
                uploadCaptionInput.value = '';
                modal.hide();
            }
        } catch (error) {
            showError(error.message || 'Upload failed.', 'upload');
        }
    }

    modalElement.addEventListener('click', (event) => {
        const selectButton = event.target.closest('[data-media-library-select]');

        if (!selectButton) {
            return;
        }

        const item = JSON.parse(selectButton.dataset.mediaItem);

        insertMedia(state.editor, item, captionInput.value.trim());
        modal.hide();
    });

    uploadForm.addEventListener('submit', uploadFile);
    searchInput.addEventListener('input', debounce((event) => {
        state.search = event.target.value.trim();
        loadItems();
    }, 250));
    tabTriggers.forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const targetTab = trigger.dataset.tab;

            tabTriggers.forEach((button) => {
                const isActive = button.dataset.tab === targetTab;
                button.classList.toggle('btn-dark-blue', isActive);
                button.classList.toggle('btn-outline-secondary', !isActive);
            });

            tabPanels.forEach((panel) => {
                panel.classList.toggle('d-none', panel.dataset.mediaLibraryTabPanel !== targetTab);
            });
        });
    });

    return {
        open(editor, options) {
            state.editor = editor;
            state.libraryUrl = options.libraryUrl;
            state.uploadUrl = options.uploadUrl;
            state.search = '';
            searchInput.value = '';
            captionInput.value = '';
            uploadCaptionInput.value = '';

            fileInput.disabled = !state.uploadUrl;
            uploadForm.querySelector('button[type="submit"]').disabled = !state.uploadUrl;

            resetError();
            renderItems([]);
            modal.show();
            tabTriggers[0]?.click();
            loadItems();
        },
    };
}

function createMapsManager() {
    const modalElement = createModalShell(
        'ckeditor-maps-modal',
        'Embed a map',
        'Paste a Google Maps or OpenStreetMap embed URL, or enter an address.',
        `
            <form data-maps-embed-form>
                <div class="mb-3">
                    <label class="form-label" for="ckeditor-map-value">Map URL or address</label>
                    <textarea class="form-control" id="ckeditor-map-value" rows="4" placeholder="https://www.google.com/maps/embed?... or Nairobi CBD"></textarea>
                </div>
                <div class="alert alert-danger d-none" data-maps-error></div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Insert map</button>
                </div>
            </form>
        `,
    );

    const modal = createModalController(modalElement);
    const form = modalElement.querySelector('[data-maps-embed-form]');
    const textarea = modalElement.querySelector('#ckeditor-map-value');
    const errorElement = modalElement.querySelector('[data-maps-error]');
    let currentEditor = null;

    function showError(message) {
        errorElement.textContent = message;
        errorElement.classList.remove('d-none');
    }

    function resetError() {
        errorElement.textContent = '';
        errorElement.classList.add('d-none');
    }

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        resetError();

        const value = textarea.value.trim();

        if (!value) {
            showError('Enter a map URL or address first.');

            return;
        }

        const src = value.startsWith('http')
            ? value
            : `https://www.google.com/maps?q=${encodeURIComponent(value)}&output=embed`;

        insertHtml(currentEditor, `
            <div class="ck-map-embed">
                <iframe
                    src="${escapeAttribute(src)}"
                    width="100%"
                    height="360"
                    style="border:0;"
                    loading="lazy"
                    allowfullscreen
                    referrerpolicy="no-referrer-when-downgrade"
                ></iframe>
            </div>
        `);

        textarea.value = '';
        modal.hide();
    });

    return {
        open(editor) {
            currentEditor = editor;
            resetError();
            textarea.value = '';
            modal.show();
        },
    };
}

function createRelatedContentManager() {
    const modalElement = createModalShell(
        'ckeditor-related-content-modal',
        'Related articles',
        'Search your content source and insert a related-article block.',
        `
            <div class="mb-3">
                <label class="form-label" for="ckeditor-related-search">Search stories or articles</label>
                <input class="form-control" id="ckeditor-related-search" type="search" placeholder="Search articles">
            </div>
            <div class="alert alert-danger d-none" data-related-content-error></div>
            <div class="d-flex justify-content-center py-5 d-none" data-related-content-loading>
                <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
            </div>
            <div class="list-group" data-related-content-results></div>
            <div class="text-center text-muted py-5 d-none" data-related-content-empty>No articles found.</div>
        `,
    );

    const modal = createModalController(modalElement);
    const searchInput = modalElement.querySelector('#ckeditor-related-search');
    const errorElement = modalElement.querySelector('[data-related-content-error]');
    const loadingElement = modalElement.querySelector('[data-related-content-loading]');
    const resultsElement = modalElement.querySelector('[data-related-content-results]');
    const emptyElement = modalElement.querySelector('[data-related-content-empty]');

    const state = {
        editor: null,
        url: null,
        search: '',
    };

    function resetState() {
        errorElement.textContent = '';
        errorElement.classList.add('d-none');
        resultsElement.innerHTML = '';
        emptyElement.classList.add('d-none');
    }

    async function searchArticles() {
        if (!state.url) {
            emptyElement.classList.remove('d-none');

            return;
        }

        resetState();
        loadingElement.classList.remove('d-none');

        try {
            const url = new URL(state.url, window.location.origin);

            if (state.search) {
                url.searchParams.set('search', state.search);
            }

            const response = await fetch(url, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            const payload = await response.json();

            if (!response.ok) {
                throw new Error(payload?.message || 'Unable to load related articles.');
            }

            const items = payload.data ?? [];

            if (!items.length) {
                emptyElement.classList.remove('d-none');

                return;
            }

            items.forEach((item) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'list-group-item list-group-item-action';
                button.dataset.relatedItem = JSON.stringify(item).replace(/'/g, '&#39;');
                button.innerHTML = `
                    <div class="fw-semibold">${escapeHtml(item.title ?? item.name ?? 'Untitled')}</div>
                    ${item.url ? `<div class="small text-muted">${escapeHtml(item.url)}</div>` : ''}
                `;
                resultsElement.appendChild(button);
            });
        } catch (error) {
            errorElement.textContent = error.message || 'Unable to load related articles.';
            errorElement.classList.remove('d-none');
        } finally {
            loadingElement.classList.add('d-none');
        }
    }

    searchInput.addEventListener('input', debounce((event) => {
        state.search = event.target.value.trim();
        searchArticles();
    }, 250));

    modalElement.addEventListener('click', (event) => {
        const itemButton = event.target.closest('[data-related-item]');

        if (!itemButton) {
            return;
        }

        const item = JSON.parse(itemButton.dataset.relatedItem);
        const title = item.title ?? item.name ?? 'Untitled article';
        const url = item.url ?? '#';

        insertHtml(state.editor, `
            <aside class="related-article">
                <p><strong>Related article:</strong> <a href="${escapeAttribute(url)}">${escapeHtml(title)}</a></p>
            </aside>
        `);

        modal.hide();
    });

    return {
        open(editor, options) {
            state.editor = editor;
            state.url = options.url;
            state.search = '';
            searchInput.value = '';

            resetState();
            modal.show();
            searchArticles();
        },
    };
}

const mediaLibraryManager = createMediaLibraryManager();
const mapsManager = createMapsManager();
const relatedContentManager = createRelatedContentManager();

function createEditorConfig(editorElement) {
    const uploadUrl = editorElement.dataset.ckeditorUploadUrl?.trim() || window.appConfig?.mediaLibraryStoreUrl || null;
    const libraryUrl = editorElement.dataset.ckeditorMediaLibraryUrl?.trim() || window.appConfig?.mediaLibraryIndexUrl || null;
    const relatedContentUrl = editorElement.dataset.ckeditorRelatedContentUrl?.trim() || window.appConfig?.relatedContentUrl || null;
    const toolbar = [...baseToolbar];

    if (!uploadUrl && !libraryUrl) {
        removeToolbarItem(toolbar, 'mediaLibrary');
    }

    if (!relatedContentUrl) {
        removeToolbarItem(toolbar, 'relatedContent');
    }

    return {
        licenseKey: 'GPL',
        extraPlugins,
        toolbar,
        heading: {
            options: [
                { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' },
                { model: 'heading4', view: 'h4', title: 'Heading 4', class: 'ck-heading_heading4' },
                { model: 'heading5', view: 'h5', title: 'Heading 5', class: 'ck-heading_heading5' },
                { model: 'heading6', view: 'h6', title: 'Heading 6', class: 'ck-heading_heading6' }
            ],
        },
        fontFamily: {
            options: [
                'default',
                'Arial, Helvetica, sans-serif',
                'Georgia, serif',
                'Tahoma, Geneva, sans-serif',
                'Times New Roman, Times, serif',
                'Trebuchet MS, Helvetica, sans-serif',
                'Verdana, Geneva, sans-serif',
            ],
            supportAllValues: true,
        },
        fontSize: {
            options: [10, 12, 14, 'default', 18, 20, 24, 28, 32],
            supportAllValues: true,
        },
        htmlSupport: {
            allow: [
                {
                    name: /^(audio|video|source|iframe|figure|figcaption|aside|div|span|p|a)$/,
                    attributes: true,
                    classes: true,
                    styles: true,
                },
            ],
        },
        htmlEmbed: {
            showPreviews: true,
        },
        style: {
            definitions: [
                {
                    name: 'Lead Paragraph',
                    element: 'p',
                    classes: ['lead'],
                },
                {
                    name: 'Muted Note',
                    element: 'p',
                    classes: ['text-muted'],
                },
                {
                    name: 'Highlighted Intro',
                    element: 'p',
                    classes: ['bg-warning-subtle', 'px-3', 'py-2', 'rounded'],
                },
            ],
        },
        image: {
            toolbar: [
                'imageTextAlternative',
                'toggleImageCaption',
                '|',
                'imageStyle:inline',
                'imageStyle:block',
                'imageStyle:side',
            ],
        },
        link: {
            addTargetToExternalLinks: true,
            defaultProtocol: 'https://',
        },
        mediaEmbed: {
            previewsInData: true,
        },
        table: {
            contentToolbar: [
                'tableColumn',
                'tableRow',
                'mergeTableCells',
                'tableProperties',
                'tableCellProperties',
            ],
        },
        mapsEmbed: {
            open(editor) {
                mapsManager.open(editor);
            },
        },
        mediaLibrary: {
            libraryUrl,
            open(editor) {
                mediaLibraryManager.open(editor, {
                    libraryUrl,
                    uploadUrl,
                });
            },
            uploadUrl,
        },
        relatedContent: {
            open(editor) {
                relatedContentManager.open(editor, {
                    url: relatedContentUrl,
                });
            },
            url: relatedContentUrl,
        },
    };
}

function removeToolbarItem(toolbar, item) {
    const index = toolbar.indexOf(item);

    if (index !== -1) {
        toolbar.splice(index, 1);
    }
}

function insertMedia(editor, item, caption = '') {
    if (!editor || !item?.url) {
        return;
    }

    if (item.is_image) {
        if (caption) {
            insertHtml(editor, `
                <figure class="image">
                    <img src="${escapeAttribute(item.url)}" alt="${escapeAttribute(caption || item.name)}">
                    <figcaption>${escapeHtml(caption)}</figcaption>
                </figure>
            `);
        } else {
            editor.execute('insertImage', {
                source: [
                    {
                        alt: item.name,
                        src: item.url,
                    },
                ],
            });
            editor.editing.view.focus();
        }

        return;
    }

    if (item.mime_type.startsWith('video/')) {
        insertHtml(editor, `
            <figure class="media">
                <video controls width="100%" src="${escapeAttribute(item.url)}"></video>
                <figcaption>${escapeHtml(caption || item.name)}</figcaption>
            </figure>
        `);

        return;
    }

    if (item.mime_type.startsWith('audio/')) {
        insertHtml(editor, `
            <figure class="media">
                <audio controls src="${escapeAttribute(item.url)}"></audio>
                <figcaption>${escapeHtml(caption || item.name)}</figcaption>
            </figure>
        `);

        return;
    }

    insertHtml(editor, `<p><a href="${escapeAttribute(item.url)}">${escapeHtml(item.name)}</a></p>`);
}

function insertHtml(editor, html) {
    const viewFragment = editor.data.processor.toView(html);
    const modelFragment = editor.data.toModel(viewFragment);

    editor.model.insertContent(modelFragment, editor.model.document.selection);
    editor.editing.view.focus();
}

function sanitizeOfficeHtml(html) {
    const parser = new DOMParser();
    const documentFragment = parser.parseFromString(html, 'text/html');

    documentFragment.querySelectorAll('style, meta, link, xml, o\\:p').forEach((node) => node.remove());

    removeCommentNodes(documentFragment.body);

    documentFragment.body.querySelectorAll('*').forEach((element) => {
        [...element.attributes].forEach((attribute) => {
            const name = attribute.name.toLowerCase();
            const value = attribute.value;
            const isOfficeAttribute = name.startsWith('mso-') || name.startsWith('xmlns');
            const isPresentationalAttribute = ['class', 'style', 'lang', 'align', 'valign'].includes(name);

            if (isOfficeAttribute || isPresentationalAttribute) {
                element.removeAttribute(attribute.name);
            }

            if (name === 'style' && /mso-|font-family|font-size|line-height|color:/i.test(value)) {
                element.removeAttribute(attribute.name);
            }
        });

        if (element.tagName === 'SPAN' && !element.attributes.length) {
            element.replaceWith(...element.childNodes);
        }
    });

    return documentFragment.body.innerHTML.trim();
}

function removeCommentNodes(node) {
    [...node.childNodes].forEach((childNode) => {
        if (childNode.nodeType === Node.COMMENT_NODE) {
            childNode.remove();

            return;
        }

        removeCommentNodes(childNode);
    });
}

function attachEditorUtilities(editor, editorElement, editorIndex) {
    const sourceKey = editorElement.dataset.ckeditorAutosaveKey
        || editorElement.name
        || editorElement.id
        || `editor-${editorIndex}`;
    const autosaveEnabled = editorElement.dataset.ckeditorAutosave !== 'false';
    const autosaveKey = `ckeditor-draft:${window.location.pathname}:${sourceKey}`;
    const footer = document.createElement('div');
    footer.className = 'd-flex flex-wrap gap-3 justify-content-between small text-muted mt-2';
    footer.innerHTML = `
        <span data-word-count>Words: 0</span>
        <span data-autosave-status>${autosaveEnabled ? 'Draft not saved yet' : 'Autosave disabled'}</span>
    `;

    editor.ui.view.editable.element.parentElement?.appendChild(footer);

    const wordCountElement = footer.querySelector('[data-word-count]');
    const autosaveElement = footer.querySelector('[data-autosave-status]');

    const syncSourceElement = () => {
        if (editor.sourceElement) {
            editor.sourceElement.value = editor.getData();
        }
    };

    const updateWordCount = () => {
        const text = getPlainText(editor.getData());
        const words = text ? text.split(/\s+/).filter(Boolean).length : 0;
        const characters = text.length;

        wordCountElement.textContent = `Words: ${words} | Characters: ${characters}`;
    };

    const saveDraft = debounce(() => {
        syncSourceElement();
        updateWordCount();

        if (!autosaveEnabled) {
            return;
        }

        window.localStorage.setItem(autosaveKey, editor.getData());
        autosaveElement.textContent = `Draft saved ${new Date().toLocaleTimeString()}`;
    }, 600);

    if (autosaveEnabled) {
        const savedDraft = window.localStorage.getItem(autosaveKey);

        if (savedDraft && !getPlainText(editor.getData())) {
            editor.setData(savedDraft);
            autosaveElement.textContent = 'Restored saved draft';
        }
    }

    editor.model.document.on('change:data', () => {
        saveDraft();
    });

    updateWordCount();
    syncSourceElement();
}

function getPlainText(html) {
    const container = document.createElement('div');
    container.innerHTML = html;

    return (container.textContent || container.innerText || '').trim();
}

function formatSize(size) {
    if (size < 1024) {
        return `${size} B`;
    }

    if (size < 1024 * 1024) {
        return `${(size / 1024).toFixed(1)} KB`;
    }

    return `${(size / (1024 * 1024)).toFixed(1)} MB`;
}

function inferMediaType(item) {
    if (item.is_image) {
        return 'image';
    }

    if (item.mime_type?.startsWith('video/')) {
        return 'video';
    }

    if (item.mime_type?.startsWith('audio/')) {
        return 'audio';
    }

    return 'document';
}

function escapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

function escapeAttribute(value) {
    return escapeHtml(value);
}

function debounce(callback, wait) {
    let timeoutId = null;

    return (...args) => {
        window.clearTimeout(timeoutId);
        timeoutId = window.setTimeout(() => callback(...args), wait);
    };
}

document.addEventListener('DOMContentLoaded', () => {
    const editorElements = document.querySelectorAll('#editor, [data-ckeditor]');

    editorElements.forEach((editorElement, editorIndex) => {
        ClassicEditor.create(editorElement, createEditorConfig(editorElement))
            .then((editor) => {
                const height = editorElement.dataset.ckeditorHeight;

                if (height) {
                    editor.ui.view.editable.element.style.minHeight = height;
                }

                attachEditorUtilities(editor, editorElement, editorIndex);
            })
            .catch((error) => {
                console.error(error);
            });
    });
});
