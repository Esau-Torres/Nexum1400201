import DataTable from 'datatables.net-bs5';

// 1. Textos en español sin depender de llamadas asíncronas que pisen tus ajustes
const spanishLanguage = {
    processing: "Procesando...",
    search: "",                     // Sin texto "Buscar:"
    searchPlaceholder: "Buscar...", // Placeholder visible
    lengthMenu: "Mostrar _MENU_ registros",
    info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
    infoEmpty: "Mostrando 0 a 0 de 0 registros",
    infoFiltered: "(filtrado de _MAX_ registros en total)",
    loadingRecords: "Cargando...",
    zeroRecords: "No se encontraron resultados",
    emptyTable: "No hay datos disponibles en la tabla",
    lengthMenu: "_MENU_",
    paginate: {
        first: "«",
        previous: "‹",
        next: "›",
        last: "»"
    },
    aria: {
        sortAscending: ": activar para ordenar columna ascendente",
        sortDescending: ": activar para ordenar columna descendente"
    }
};

// 2. Configuración global por defecto
const nexumDataTableDefaults = {
    language: spanishLanguage,
    pageLength: 5,
    lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Todos"]],
    dom: '<"row align-items-center mb-2 px-4 pt-4"<"col-6 col-md-8 d-flex align-items-center"l><"col-6 col-md-4 d-flex justify-content-end"f>>rt<"row align-items-center mt-3 px-4 pb-4"<"col-12 col-md-7"i><"col-12 col-md-5 d-flex justify-content-end"p>>',
    responsive: true,
    autoWidth: false,
initComplete: function () {
    const container = this.api().table().container();
    const filter = container.querySelector('.dataTables_filter, .dt-search');
    
    if (filter) {
        const label = filter.querySelector('label');
        if (label) {
            // Remueve cualquier nodo de texto suelto ("Buscar:", "Search:") dejando solo el input
            Array.from(label.childNodes).forEach(node => {
                if (node.nodeType === Node.TEXT_NODE) {
                    node.remove();
                }
            });
        }
        
        // Asegura que el input conserve el placeholder
        const input = filter.querySelector('input');
        if (input && !input.getAttribute('placeholder')) {
            input.setAttribute('placeholder', 'Buscar...');
        }
    }
}
};

// 3. Ajustes específicos para Roles
const nexumDataTableRoles = {
    pageLength: 4,
    dom: '<"row align-items-center mb-2 px-4 pt-4"<"col-12 d-flex justify-content-end"f>>rt<"row align-items-center mt-3 px-4 pb-4"<"col-12 d-flex justify-content-end"p>>',
};


function initTable(selector, overrides = {}) {
    const el = document.querySelector(selector);
    if (!el) return null;

    return new DataTable(el, {
        ...nexumDataTableDefaults,
        ...overrides,
    });
}

document.addEventListener('DOMContentLoaded', function () {
    initTable('#usersTable', { columnDefs: [{ orderable: false, targets: 5 }] });
    initTable('#rolesTable', { 
        ...nexumDataTableRoles,
        columnDefs: [{ orderable: false, targets: 3 }] 
    });
    initTable('#usersAssignTable', { columnDefs: [{ orderable: false, targets: 4 }] });
    
});