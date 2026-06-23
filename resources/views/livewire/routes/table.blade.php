<div class="bwp-wrap">
    @if(session('bwp_success'))
        <div class="bwp-alert bwp-alert--success">✓ {{ session('bwp_success') }}</div>
    @endif

    <div class="bwp-header">
        <h2 class="bwp-title">Rutas</h2>
        <a href="{{ route('bwp.routes.create') }}" class="bwp-btn bwp-btn--primary">+ Nueva ruta</a>
    </div>

    <div style="display:flex;gap:0.75rem;margin-bottom:1rem;">
        <div class="bwp-search">
            <span class="bwp-search-icon">⌕</span>
            <input wire:model.live.debounce.300ms="search" class="bwp-input" placeholder="Buscar rutas...">
        </div>
        <select wire:model.live="typeFilter" class="bwp-select" style="width:auto;">
            <option value="">Todos los tipos</option>
            <option value="web">web</option>
            <option value="api">api</option>
        </select>
    </div>

    <div class="bwp-table-wrap">
        <table class="bwp-table">
            <thead>
                <tr><th>Nombre (wildcard)</th><th>Tipo</th><th>Path</th><th>Descripción</th><th style="width:120px;"></th></tr>
            </thead>
            <tbody>
                @forelse($routes as $route)
                    <tr>
                        <td><code style="font-size:0.75rem;color:var(--bwp-accent);">{{ $route->name }}</code></td>
                        <td><span class="bwp-badge bwp-badge--muted">{{ $route->type }}</span></td>
                        <td style="color:var(--bwp-dim);">{{ $route->patch }}</td>
                        <td style="color:var(--bwp-dim);">{{ $route->description }}</td>
                        <td>
                            <div style="display:flex;gap:0.35rem;">
                                <a href="{{ route('bwp.routes.edit', $route) }}" class="bwp-btn bwp-btn--ghost bwp-btn--sm">Editar</a>
                                <button wire:click="confirmDelete({{ $route->id }})" class="bwp-btn bwp-btn--ghost bwp-btn--sm" style="color:var(--bwp-danger);">Eliminar</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--bwp-dim);">Sin rutas registradas</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="bwp-pagination">{{ $routes->links() }}</div>
    </div>

    @if($deleteId)
        <div class="bwp-modal-backdrop">
            <div class="bwp-modal">
                <p class="bwp-modal__title">¿Eliminar ruta?</p>
                <p class="bwp-modal__text">Se eliminarán todos los accesos asociados a esta ruta.</p>
                <div class="bwp-modal__actions">
                    <button wire:click="cancelDelete" class="bwp-btn bwp-btn--secondary">Cancelar</button>
                    <button wire:click="delete" class="bwp-btn bwp-btn--danger">Eliminar</button>
                </div>
            </div>
        </div>
    @endif
</div>
