import DataTable from 'datatables.net-bs5';
import 'datatables.net-buttons-bs5';
import 'datatables.net-fixedheader-bs5';
import 'datatables.net-responsive-bs5';

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

function normalizeAjaxConfig(ajaxConfig = {}) {
    const csrfToken = getCsrfToken();

    if (typeof ajaxConfig === 'string') {
        return {
            url: ajaxConfig,
            dataType: 'json',
            type: 'POST',
            data: {_token: csrfToken}
        };
    }

    return {
        dataType: 'json',
        type: 'POST',
        data: {_token: csrfToken},
        ...ajaxConfig,
        data: {
            _token: csrfToken,
            ...(ajaxConfig.data ?? {})
        }
    };
}

/**
 * Initialize a DataTable dynamically with optional extensions.
 * @param {string} tableId - The selector of the table (e.g., '#curated-table').
 * @param {string} ajaxUrl - The URL for server-side processing.
 * @param {Array} columns - Array of column definitions for DataTable.
 * @param {Array} order - Array defining default ordering. Example: [[1, 'asc']]
 * @param {Object} extensions - Optional DataTable extensions and configs.
 *                              Example: { buttons: ['copy', 'excel'], fixedHeader: true, responsive: true }
 */
export function initDataTable(tableId, ajaxUrl, columns, order = [[1, 'asc']], extensions = {}) {
    return renderDataTable(tableId, {
        processing: true,
        serverSide: true,
        ajax: ajaxUrl,
        columns,
        order,
        ...extensions,
        ...(extensions.buttons ? {
            dom: extensions.dom || 'Bfrtip',
            buttons: extensions.buttons
        } : {})
    });
}

export function renderDataTable(tableId, config = {}) {
    return new DataTable(tableId, {
        processing: true,
        serverSide: true,
        ...config,
        ajax: normalizeAjaxConfig(config.ajax)
    });
}

window.initDataTable = initDataTable;
window.renderDataTable = renderDataTable;
