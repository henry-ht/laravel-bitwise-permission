<div class="bwp-wrap">
    @if(session('bwp_success'))
        <div class="bwp-alert bwp-alert--success">✓ {{ session('bwp_success') }}</div>
    @endif

    <div class="bwp-header">
        <h2 class="bwp-title">Menús</h2>
        <a href="{{ route('bwp.menus.create') }}" class="bwp-btn bwp-btn--primary">+ Nuevo ítem</a>
    </div>

    <ul class="bwp-menu-tree">
        @forelse($menus as $menu)
            <li class="bwp-menu-item">
                <div class="bwp-menu-item__row">
                    {{-- @if(isset($menu->icon) && $menu->icon)
                        <span style="color:var(--bwp-accent);width:16px;text-align:center;">{{ $menu->icon }}</span>
                    @endif --}}
                    <span style="font-weight:600;color:var(--bwp-text);flex:1;">{{ $menu->public_name }}</span>
                    <code style="font-size:0.72rem;color:var(--bwp-dim);">{{ $menu->patch }}</code>
                    <span style="font-size:0.72rem;color:var(--bwp-dim);">orden: {{ $menu->order }}</span>
                    <div style="display:flex;gap:0.35rem;">
                        <a href="{{ route('bwp.menus.edit', $menu) }}" class="bwp-btn bwp-btn--ghost bwp-btn--sm">Editar</a>
                        <button wire:click="confirmDelete({{ $menu->id }})" class="bwp-btn bwp-btn--ghost bwp-btn--sm" style="color:var(--bwp-danger);">Eliminar</button>
                    </div>
                </div>
                @if($menu->childrenOrdered->count())
                    <ul class="bwp-menu-item__children">
                        @foreach($menu->childrenOrdered as $child)
                            <li class="bwp-menu-item" style="border:none;border-bottom:1px solid var(--bwp-border);border-radius:0;margin:0;">
                                <div class="bwp-menu-item__row">
                                    <span style="color:var(--bwp-dim);">└</span>
                                    <span style="flex:1;color:var(--bwp-muted);">{{ $child->public_name }}</span>
                                    <code style="font-size:0.72rem;color:var(--bwp-dim);">{{ $child->patch }}</code>
                                    <div style="display:flex;gap:0.35rem;">
                                        <a href="{{ route('bwp.menus.edit', $child) }}" class="bwp-btn bwp-btn--ghost bwp-btn--sm">Editar</a>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </li>
        @empty
            <li style="text-align:center;padding:2rem;color:var(--bwp-dim);">Sin ítems de menú</li>
        @endforelse
    </ul>

    @if($deleteId)
        <div class="bwp-modal-backdrop">
            <div class="bwp-modal">
                <p class="bwp-modal__title">¿Eliminar ítem?</p>
                <p class="bwp-modal__text">Los ítems hijos quedarán sin padre y se eliminarán también.</p>
                <div class="bwp-modal__actions">
                    <button wire:click="cancelDelete" class="bwp-btn bwp-btn--secondary">Cancelar</button>
                    <button wire:click="delete" class="bwp-btn bwp-btn--danger">Eliminar</button>
                </div>
            </div>
        </div>
    @endif
</div>
