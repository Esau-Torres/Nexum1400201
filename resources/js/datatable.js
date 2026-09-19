import DataTable from 'datatables.net-bs5';

const nexumDataTableDefaults = {
    language: {
        url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json'
    },
    pageLength: 5,
    lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Todos"]],
    dom: '<"row align-items-center mb-2 px-4 pt-4"<"col-6 col-md-8 d-flex align-items-center"l><"col-6 col-md-4"f>>rt<"row align-items-center mt-3 px-4 pb-4"<"col-12 col-md-7"i><"col-12 col-md-5 d-flex justify-content-end"p>>',
    responsive: true,
    autoWidth: false,
};

document.addEventListener('DOMContentLoaded', function () {
    initTable('#usersTable', { columnDefs: [{ orderable: false, targets: 5 }] });
    initTable('#rolesTable', { columnDefs: [{ orderable: false, targets: 3 }] });
    initTable('#usersAssignTable', { columnDefs: [{ orderable: false, targets: 4 }] });
});

function initTable(selector, overrides = {}) {
    const el = document.querySelector(selector);
    if (!el) return null;

    return new DataTable(el, {
        ...nexumDataTableDefaults,
        ...overrides,
    });
}
