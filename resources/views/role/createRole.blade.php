<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<link rel="stylesheet" href="{{ asset('css/role/createRole.css') }}">
<title>Crear Rol</title>
</head>
<body>
<div class="container">
    <nav class="top-nav">
    <a href="{{ url('role') }}">Roles</a>
    <a href="{{ url('permission') }}">Permisos</a>
    <a href="{{ url('user') }}">Usuarios</a>
    </nav>

    <h1>Crear rol</h1>

    {{-- Mensajes --}}
    @if (session('success'))
    <div class="alert success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
    <div class="alert danger">{{ session('error') }}</div>
    @endif

    {{-- Errores de validación --}}
    @if ($errors->any())
    <div class="alert danger">
        <ul class="mb-0">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('role.store') }}" class="card">
    @csrf

    <div class="form-row">
        <label for="name">Nombre del rol</label>
        <input id="name" name="name" type="text" required value="{{ old('name') }}" placeholder="Ej. editor, supervisor">
    </div>

    <div class="form-row">
        <label for="perm-search">Permisos</label>
        <div class="perm-toolbar">
        <input id="perm-search" type="text" placeholder="Buscar permiso...">
        <div class="perm-actions">
            <button type="button" class="btn ghost" id="btn-select-all">Seleccionar todos</button>
            <button type="button" class="btn ghost" id="btn-clear-all">Limpiar</button>
        </div>
        </div>

        {{-- Lista de permisos (checkboxes) --}}
        <div class="perm-list" id="perm-list">
        @foreach ($permissions as $permission)
        <label>
            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
            {{ in_array($permission->id, old('permissions', $rolePermissionIds ?? [])) ? 'checked' : '' }}>
            {{ $permission->name }}
        </label>
        @endforeach

        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn primary">Crear rol</button>
        <a href="{{ route('role.index') }}" class="btn">Cancelar</a>
    </div>
    </form>
</div>

<script>
    // Filtro rápido por texto
    const search = document.getElementById('perm-search');
    const list = document.getElementById('perm-list');
    if (search && list) {
    search.addEventListener('input', () => {
        const q = search.value.trim().toLowerCase();
        list.querySelectorAll('.perm-item').forEach(item => {
        const name = item.dataset.name || '';
        item.style.display = name.includes(q) ? '' : 'none';
        });
    });
    }

    // Seleccionar todos / limpiar
    const btnAll = document.getElementById('btn-select-all');
    const btnClear = document.getElementById('btn-clear-all');

    function setAll(checked) {
    list.querySelectorAll('input[type="checkbox"]').forEach(ch => { ch.checked = checked; });
    }
    if (btnAll) btnAll.addEventListener('click', () => setAll(true));
    if (btnClear) btnClear.addEventListener('click', () => setAll(false));
</script>
</body>
</html>
