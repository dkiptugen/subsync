import Choices from 'choices.js';

const createChoices = (select, config) => {
    try {
        const choices = new Choices(select, config);
        select.dataset.choicesInitialized = 'true';

        return choices;
    } catch (error) {
        console.error('Choices initialization failed.', {select, error});

        return null;
    }
};

const initChoices = (root = document) => {
    if (!Choices) {
        return;
    }

    root.querySelectorAll('select:not(.js-choice-ajax):not(.dt-input):not([data-choices-native]):not([data-no-choices])').forEach(select => {
        if (select.dataset.choicesInitialized === 'true') {
            return;
        }

        const isMultiple = select.hasAttribute('multiple');
        const emptyOption = select.querySelector('option[value=""]');
        const placeholder = select.dataset.placeholder || select.getAttribute('placeholder') || emptyOption?.textContent?.trim() || (isMultiple ? 'Select options' : 'Select an option');

        createChoices(select, {
            allowHTML: false,
            itemSelectText: '',
            removeItemButton: isMultiple,
            searchEnabled: select.options.length > 6,
            shouldSort: false,
            placeholder: Boolean(placeholder),
            placeholderValue: placeholder,
            noResultsText: 'No results found',
            noChoicesText: 'No options available',
            searchPlaceholderValue: placeholder,
        });
    });

    root.querySelectorAll('.js-choice-ajax').forEach(select => {
        if (select.dataset.choicesInitialized === 'true') {
            return;
        }

        const url = select.dataset.choicesAjaxUrl;
        const placeholder = select.dataset.placeholder || '';
        const minSearch = Number.parseInt(select.dataset.choicesMinSearch || '2', 10);

        if (!url) {
            return;
        }

        const choices = createChoices(select, {
            allowHTML: false,
            itemSelectText: '',
            removeItemButton: select.hasAttribute('multiple'),
            searchEnabled: true,
            shouldSort: false,
            placeholder: Boolean(placeholder),
            placeholderValue: placeholder,
            noResultsText: 'No results found',
            noChoicesText: 'Start typing to search',
            searchPlaceholderValue: placeholder || 'Search...',
        });

        if (!choices) {
            return;
        }

        select.addEventListener('search', async event => {
            const term = event.detail.value?.trim() || '';

            if (term.length < minSearch) {
                return;
            }

            try {
                const response = await fetch(`${url}?term=${encodeURIComponent(term)}`, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                const data = await response.json();

                choices.clearChoices();
                choices.setChoices(data, 'id', 'text', true);
            } catch (error) {
                console.error('Choices AJAX search failed.', error);
            }
        });
    });
};

window.initChoices = initChoices;

document.addEventListener('DOMContentLoaded', () => {
    initChoices();
});
