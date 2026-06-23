<div class="bwp-wrap">
    @if(session('bwp_success'))
        <div class="bwp-alert bwp-alert--success">✓ {{ session('bwp_success') }}</div>
    @endif

    <div class="bwp-header">
        <h2 class="bwp-title">Roles</h2>
        <a href="{{ route('bwp.roles.create') }}" class="bwp-btn bwp-btn--primary">+ Nuevo rol</a>
    </div>

    <div class="bwp-search" style="margin-bottom:1rem;">
        <span class="bwp-search-icon">⌕</span>
        <input wire:model.live.debounce.300ms="search" class="bwp-input" placeholder="Buscar roles...">
    </div>

    <div class="bwp-table-wrap">
        <table class="bwp-table">
            <thead>
                <tr>
                    <th wire:click="sort('name')">Nombre</th>
                    <th wire:click="sort('public_name')">Nombre público</th>
                    <th>Base</th>
                    <th style="width:120px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($roles as $role)
                    <tr>
                        <td style="color:var(--bwp-text);font-weight:500;">{{ $role->name }}</td>
                        <td>{{ $role->public_name }}</td>
                        <td>
                            @if($role->is_base_role)
                                <span class="bwp-badge bwp-badge--primary">Base</span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;gap:0.35rem;">
                                <a href="{{ route('bwp.roles.edit', $role) }}" class="bwp-btn bwp-btn--ghost bwp-btn--sm">Editar</a>
                                <button wire:click="confirmDelete({{ $role->id }})" class="bwp-btn bwp-btn--ghost bwp-btn--sm" style="color:var(--bwp-danger);">Eliminar</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="text-align:center;padding:2rem;color:var(--bwp-dim);">Sin resultados</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="bwp-pagination">{{ $roles->links() }}</div>
    </div>

    {{-- Confirm delete --}}
    @if($deleteId)
        <div class="bwp-modal-backdrop">
            <div class="bwp-modal">
                <p class="bwp-modal__title">¿Eliminar rol?</p>
                <p class="bwp-modal__text">Esta acción no se puede deshacer. Se eliminarán todos los accesos asociados.</p>
                <div class="bwp-modal__actions">
                    <button wire:click="cancelDelete" class="bwp-btn bwp-btn--secondary">Cancelar</button>
                    <button wire:click="delete" class="bwp-btn bwp-btn--danger">Eliminar</button>
                </div>
            </div>
        </div>
    @endif
</div>
