<div class="bwp-wrap">
    @if(session('bwp_success'))
        <div class="bwp-alert bwp-alert--success">✓ {{ session('bwp_success') }}</div>
    @endif

    <div class="bwp-header">
        <h2 class="bwp-title">Accesos</h2>
        <a href="{{ route('bwp.accesses.create') }}" class="bwp-btn bwp-btn--primary">+ Nuevo acceso</a>
    </div>

    <div style="display:flex;gap:0.75rem;margin-bottom:1rem;flex-wrap:wrap;">
        <div class="bwp-search">
            <span class="bwp-search-icon">⌕</span>
            <input wire:model.live.debounce.300ms="search" class="bwp-input" placeholder="Buscar...">
        </div>
        <select wire:model.live="roleFilter" class="bwp-select" style="width:auto;">
            <option value="0">Todos los roles</option>
            @foreach($roles as $role)
                <option value="{{ $role->id }}">{{ $role->public_name }}</option>
            @endforeach
        </select>
        <select wire:model.live="routeFilter" class="bwp-select" style="width:auto;">
            <option value="0">Todas las rutas</option>
            @foreach($routes as $route)
                <option value="{{ $route->id }}">{{ $route->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="bwp-table-wrap">
        <table class="bwp-table">
            <thead>
                <tr>
                    <th>Rol</th>
                    <th>Ruta</th>
                    <th>Permiso</th>
                    <th>Bits activos</th>
                    <th style="width:100px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($accesses as $access)
                    <tr>
                        <td style="color:var(--bwp-text);font-weight:500;">{{ $access->role->public_name }}</td>
                        <td><code style="font-size:0.75rem;color:var(--bwp-accent);">{{ $access->route->name }}</code></td>
                        <td><span class="bwp-badge bwp-badge--muted">{{ $access->permission->name }}</span></td>
                        <td>
                            <div class="bwp-bits">
                                @foreach($bits as $bitName => $bitValue)
                                    @if(($access->permission->access & $bitValue) === $bitValue)
                                        <span class="bwp-bit bwp-bit--active">{{ $bitName }}</span>
                                    @endif
                                @endforeach
                            </div>
                        </td>
                        <td>
                            <div style="display:flex;gap:0.35rem;">
                                <a href="{{ route('bwp.accesses.edit', $access) }}" class="bwp-btn bwp-btn--ghost bwp-btn--sm">Editar</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--bwp-dim);">Sin accesos configurados</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="bwp-pagination">{{ $accesses->links() }}</div>
    </div>
</div>
