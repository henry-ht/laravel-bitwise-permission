<div class="bwp-wrap">
    <div class="bwp-header">
        <h2 class="bwp-title">Permisos</h2>
    </div>

    <div class="bwp-search" style="margin-bottom:1rem;">
        <span class="bwp-search-icon">⌕</span>
        <input wire:model.live.debounce.300ms="search" class="bwp-input" placeholder="Buscar permisos...">
    </div>

    <div class="bwp-table-wrap">
        <table class="bwp-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Valor</th>
                    <th>Bits activos</th>
                </tr>
            </thead>
            <tbody>
                @forelse($permissions as $permission)
                    <tr>
                        <td style="color:var(--bwp-text);font-weight:500;">{{ $permission->name }}</td>
                        <td><span class="bwp-badge bwp-badge--muted">{{ $permission->access }}</span></td>
                        <td>
                            <div class="bwp-bits">
                                @foreach($bits as $bitName => $bitValue)
                                    <span class="bwp-bit {{ ($permission->access & $bitValue) === $bitValue ? 'bwp-bit--active' : 'bwp-bit--inactive' }}">
                                        {{ $bitName }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" style="text-align:center;padding:2rem;color:var(--bwp-dim);">Sin permisos</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="bwp-pagination">{{ $permissions->links() }}</div>
    </div>
</div>
