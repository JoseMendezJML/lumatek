@if(session('success'))
    <div class="alert-flash success">
        <div><strong>Listo.</strong> {{ session('success') }}</div>
        <button type="button" data-flash-close aria-label="Cerrar">×</button>
    </div>
@endif

@if(session('warning'))
    <div class="alert-flash warning">
        <div><strong>Atención.</strong> {{ session('warning') }}</div>
        <button type="button" data-flash-close aria-label="Cerrar">×</button>
    </div>
@endif

@if(session('status'))
    <div class="alert-flash success">
        <div>{{ session('status') }}</div>
        <button type="button" data-flash-close aria-label="Cerrar">×</button>
    </div>
@endif

@if($errors->any())
    <div class="alert-flash error">
        <div>
            <strong>Revisa la información:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        <button type="button" data-flash-close aria-label="Cerrar">×</button>
    </div>
@endif
