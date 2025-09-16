<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/role/createRole.css') }}">
    <title>Crear rol</title>
</head>
<body>
    <div class="page-container">
        <main class="content">
        <br><x-barracreate/>
            <div class="container">
                <br><hr class="hr-grueso"><center><h1>Crear nuevo rol</h1></center><hr class="hr-grueso"><br>

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

                <form method="POST" action="{{ route('role.store') }}" class="card">
                @csrf
                    <div class="form-row">
                        <label for="name">Nombre del rol</label>
                        <input id="name" name="name" type="text" required value="{{ old('name') }}" placeholder="Ej. editor, supervisor">
                    </div>

                    <div class="form-row">
                        <label>Permisos</label>

                        <div class="perm-actions mb-2">
                            <button type="button" class="btn btn-warning" id="btn-select-all">Seleccionar todos</button>
                            <button type="button" class="btn btn-gray" id="btn-clear-all">Limpiar</button>
                        </div>

                        <div class="perm-list" id="perm-list">
                        @foreach ($permissions as $permission)
                        <label class="perm-item">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                            {{ in_array($permission->id, old('permissions', $rolePermissionIds ?? [])) ? 'checked' : '' }}>
                            <span class="perm-text">{{ $permission->name }}</span>
                        </label>
                        @endforeach
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-registrar">Crear rol</button>
                        <a href="{{ route('role.index') }}" class="btn btn-danger">Cancelar</a>
                    </div>
                </form>
            </div>
        <script>
            const list = document.getElementById('perm-list');
            const btnAll = document.getElementById('btn-select-all');
            const btnClear = document.getElementById('btn-clear-all');

            function setAll(checked) {
                list.querySelectorAll('input[type="checkbox"]').forEach(ch => { ch.checked = checked; });
            }
            if (btnAll) btnAll.addEventListener('click', () => setAll(true));
            if (btnClear) btnClear.addEventListener('click', () => setAll(false));
        </script>
        <script>
            const form = document.querySelector("form");
            form.addEventListener("submit", function (e) {
                const checked = form.querySelectorAll('input[name="permissions[]"]:checked');
                if (checked.length === 0) {
                    e.preventDefault();
                    alert("Debes seleccionar al menos un permiso para crear el rol.");
                }
            });
        </script>
        </main>
        <x-footer/>
    </div>
</body>
</html>
