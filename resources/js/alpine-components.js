import Alpine from 'alpinejs';

window.HORARIOS_POR_CICLO = window.HORARIOS_POR_CICLO || {};

Alpine.data('grupoForm', () => ({
    cicloId: '',
    horariosFiltrados: [],
    filtrarHorarios() {
        this.horariosFiltrados = window.HORARIOS_POR_CICLO[this.cicloId] || [];
    }
}));

Alpine.data('grupoFormEditar', (cicloInicial, seleccionados) => ({
    cicloId: String(cicloInicial),
    seleccionados: (seleccionados || []).map(Number),
    horariosFiltrados: [],
    init() { this.filtrarHorarios(); },
    filtrarHorarios() {
        this.horariosFiltrados = window.HORARIOS_POR_CICLO[this.cicloId] || [];
    },
    toggle(id) {
        id = Number(id);
        const i = this.seleccionados.indexOf(id);
        if (i > -1) this.seleccionados.splice(i, 1);
        else this.seleccionados.push(id);
    }
}));