<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="{{ asset('css/role/editRole.css') }}">
  <title>Editar Rol</title>
</head>
<body>
<br>
  <div class="container">
    <nav class="top-nav">
      <a href="{{ route('role.index') }}">Roles</a>
      <a href="{{ url('permission') }}">Permisos</a>
      <a href="{{ url('user') }}">Usuarios</a>
    </nav>

    <br><hr class="hr-grueso"><center><h1>Editar rol</h1></center><hr class="hr-grueso"><br>

    @if (session('success'))
      <div class="alert success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
      <div class="alert danger">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
      <div class="alert danger">
        <ul class="mb-0">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('role.update', $role->id) }}" class="card">
      @csrf
      @method('PUT')

      <div class="form-row">
        <label for="name">Nombre del rol</label>
        <input id="name" name="name" type="text" required
               value="{{ old('name', $role->name) }}"
               placeholder="Ej. editor, supervisor">
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
        <button type="submit" class="btn primary">Guardar cambios</button>
        <a href="{{ route('role.index') }}" class="btn">Cancelar</a>
      </div>
    </form>
  </div>

  <script>
    // Buscador
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
    const setAll = (checked) => {
      list.querySelectorAll('input[type="checkbox"]').forEach(ch => ch.checked = checked);
    };
    if (btnAll) btnAll.addEventListener('click', () => setAll(true));
    if (btnClear) btnClear.addEventListener('click', () => setAll(false));
  </script>
</body>
</html>
