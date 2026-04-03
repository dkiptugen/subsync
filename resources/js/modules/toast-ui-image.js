import ImageEditor from 'tui-image-editor';

let editor = null;

document.addEventListener('DOMContentLoaded', () => {
    const container = document.querySelector('#tui-image-editor');
    const saveButton = document.querySelector('#save-image');

    if (!container) {
        return;
    }

    // Create editor once
    editor = new ImageEditor(container, {
        includeUI: {
            loadImage: {
                path: '',
                name: ''
            },
            initMenu: 'filter',
            menu: [
                'crop',
                'flip',
                'rotate',
                'draw',
                'shape',
                'icon',
                'text',
                'mask',
                'filter'
            ],
            uiSize: {
                width: '100%',
                height: '600px'
            },
            menuBarPosition: 'bottom'
        },
        cssMaxWidth: 700,
        cssMaxHeight: 500,
        usageStatistics: false
    });

    // Attach click handlers
    document.querySelectorAll('.edit-image').forEach(btn => {

        btn.addEventListener('click', function () {

            const imagePath = this.dataset.image;
            const imageName = this.dataset.name;

            editor.loadImageFromURL(imagePath, imageName).then(() => {
                editor.clearUndoStack();
            });

        });

    });

    if (!saveButton) {
        return;
    }

    saveButton.addEventListener('click', function () {
        if (!editor) {
            return;
        }

        const dataURL = editor.toDataURL();
        const imageLink = this.dataset.image_link;

        if (!imageLink) {
            return;
        }

        fetch(imageLink, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({image: dataURL})
        });
    });
});
