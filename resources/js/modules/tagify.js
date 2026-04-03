import Tagify from '@yaireo/tagify'

// Select the input
const input = document.querySelector('#tags-input')

if (input) {
    // Get keyword suggestion URL from data attribute
    const keyword_link = input.dataset.keyword_link

    // Initialize Tagify
    const tagify = new Tagify(input, {
        whitelist: [],   // start empty, suggestions will be loaded dynamically
        dropdown: {
            enabled: 1,       // show suggestions after 1 character
            maxItems: 10,     // maximum number of suggestions shown
            classname: 'tags-suggestions',
            fuzzySearch: true // match partially typed words
        }
    })

    // Optional: debounce function to limit AJAX calls
    function debounce(func, wait) {
        let timeout

        return function(...args) {
            clearTimeout(timeout)
            timeout = setTimeout(() => func.apply(this, args), wait)
        }
    }

    // Event: fetch suggestions on input
    tagify.on('input', debounce(function(e) {
        const value = e.detail.value

        // Don't query empty input
        if (!value) {
            return
        }

        fetch(`${keyword_link}?q=${encodeURIComponent(value)}`)
            .then(res => res.json())
            .then(suggestions => {
                // Update whitelist and show dropdown
                tagify.settings.whitelist = suggestions
                tagify.dropdown.show.call(tagify, value)
            })
            .catch(err => console.error('Tag suggestion error:', err))
    }, 300))  // 300ms debounce
}
