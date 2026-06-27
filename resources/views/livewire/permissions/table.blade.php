<div class="bwp-wrap">

    @if(session('bwp_success'))
        <div class="bwp-alert bwp-alert--success">✓ {{ session('bwp_success') }}</div>
    @endif

    <div class="bwp-header">
        <h2 class="bwp-title">Permisos</h2>
        <a href="{{ route('bwp.permissions.create') }}" class="bwp-btn bwp-btn--primary">+ Nuevo permiso</a>
    </div>

    <div class="bwp-search" style="margin-bottom:1rem;">
        <span class="bwp-search-icon">⌕</span>
        <input wire:model.live.debounce.300ms="search"
               class="bwp-input"
               placeholder="Buscar permisos...">
    </div>

    <div class="bwp-table-wrap">
        <table class="bwp-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Valor</th>
                    <th>Bits activos</th>
                    <th class="bwp-col-actions"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($permissions as $permission)
                    <tr>
                        <td style="color:var(--bwp-text);font-weight:500;">
                            {{ $permission->name }}
                        </td>
                        <td>
                            <span class="bwp-badge bwp-badge--muted">{{ $permission->access }}</span>
                        </td>
                        <td>
                            <div class="bwp-bits">
                                @if($permission->access === 0)
                                    <span class="bwp-bit bwp-bit--inactive">no access</span>
                                @else
                                    @foreach($bits as $bitName => $bitValue)
                                        @if(($permission->access & $bitValue) === $bitValue)
                                            <span class="bwp-bit bwp-bit--active">{{ $bitName }}</span>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="bwp-actions">
                                <a href="{{ route('bwp.permissions.edit', $permission) }}"
                                   class="bwp-btn bwp-btn--ghost bwp-btn--sm">
                                    Editar
                                </a>
                                <button wire:click="confirmDelete({{ $permission->id }})"
                                        class="bwp-btn bwp-btn--ghost bwp-btn--sm"
                                        style="color:var(--bwp-danger);">
                                    Eliminar
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="bwp-empty">Sin permisos registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="bwp-pagination">{{ $permissions->links() }}</div>
    </div>

    {{-- Confirm delete --}}
    @if($deleteId)
        <div class="bwp-modal-backdrop">
            <div class="bwp-modal">
                <p class="bwp-modal__title">¿Eliminar permiso?</p>
                <p class="bwp-modal__text">
                    Se eliminarán todos los accesos que usen este permiso.
                </p>
                <div class="bwp-modal__actions">
                    <button wire:click="cancelDelete" class="bwp-btn bwp-btn--secondary">Cancelar</button>
                    <button wire:click="delete"       class="bwp-btn bwp-btn--danger">Eliminar</button>
                </div>
            </div>
        </div>
    @endif

</div>