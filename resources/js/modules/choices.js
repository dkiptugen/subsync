import Choices from 'choices.js';
document.addEventListener('DOMContentLoaded', () => {

// ========== ENHANCED SELECTS ==========
    if (Choices) {
        document.querySelectorAll('.js-choice,  .chosen-select').forEach(select => {
            if (select.dataset.choicesInitialized === 'true') {
                return;
            }

            const isMultiple = select.hasAttribute('multiple');
            const placeholder = select.dataset.placeholder || select.getAttribute('placeholder') || '';

            new Choices(select, {
                allowHTML: false,
                itemSelectText: '',
                removeItemButton: isMultiple,
                searchEnabled: select.options.length > 8,
                shouldSort: false,
                placeholder: Boolean(placeholder),
                placeholderValue: placeholder,
            });

            select.dataset.choicesInitialized = 'true';
        });

        document.querySelectorAll('.js-choice-ajax').forEach(select => {
            if (select.dataset.choicesInitialized === 'true') {
                return;
            }

            const url = select.dataset.choicesAjaxUrl;
            const placeholder = select.dataset.placeholder || '';
            const minSearch = Number.parseInt(select.dataset.choicesMinSearch || '2', 10);

            if (!url) {
                return;
            }

            const choices = new Choices(select, {
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

            select.dataset.choicesInitialized = 'true';
        });
    }
});
