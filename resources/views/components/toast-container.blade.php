{{-- Contenedor global de Toasts posicionado en la esquina superior derecha --}}
<div aria-live="polite" aria-atomic="true" class="position-relative">
    <div id="nexum-toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1090;">
        {{-- Procesamiento automático de flashes de sesión de Laravel --}}
        @php
            $alerts = [
                'success' => ['icon' => 'bi-check-circle-fill', 'title' => 'Éxito'],
                'error'   => ['icon' => 'bi-x-circle-fill',     'title' => 'Error del Sistema'],
                'warning' => ['icon' => 'bi-exclamation-triangle-fill', 'title' => 'Advertencia'],
                'info'    => ['icon' => 'bi-info-circle-fill',  'title' => 'Información'],
            ];
        @endphp

        @foreach ($alerts as $type => $meta)
            @if (session()->has($type))
                <div class="toast align-items-center border-0 shadow-sm nexum-toast-{{ $type }}" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
                    <div class="d-flex">
                        <div class="toast-body d-flex align-items-start gap-2">
                            <i class="bi {{ $meta['icon'] }} fs-5 flex-shrink-0"></i>
                            <div>
                                <strong class="d-block mb-1 text-dark">{{ $meta['title'] }}</strong>
                                <span class="text-secondary small">{{ session($type) }}</span>
                            </div>
                        </div>
                        <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</div>