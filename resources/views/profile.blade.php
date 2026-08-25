<div class="card p-4 shadow-sm">
    <h4>Autenticación en Dos Factores (2FA)</h4>
    
    @if(! auth()->user()->two_factor_secret)
        {{-- Activar 2FA --}}
        <form method="POST" action="{{ url('/user/two-factor-authentication') }}">
            @csrf
            <button type="submit" class="btn btn-success">Habilitar 2FA</button>
        </form>
    @else
        {{-- Si está activado pero pendiente de confirmación, mostramos el QR --}}
        <div class="my-3">
            <p>Escanea este código QR con Google Authenticator o Authy:</p>
            <div>{!! auth()->user()->twoFactorQrCodeSvg() !!}</div>
        </div>

        <div class="my-3">
            <h6>Códigos de recuperación:</h6>
            <ul class="list-group">
                @foreach (json_decode(decrypt(auth()->user()->two_factor_recovery_codes), true) as $code)
                    <li class="list-group-item font-monospace">{{ $code }}</li>
                @endforeach
            </ul>
        </div>

        {{-- Desactivar 2FA --}}
        <form method="POST" action="{{ url('/user/two-factor-authentication') }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Deshabilitar 2FA</button>
        </form>
    @endif
</div>