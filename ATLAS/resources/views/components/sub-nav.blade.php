<!-- Sub Navigation Tabs -->
<div class="bg-white border-bottom px-4">
    <div class="container-fluid">
        <ul class="nav nav-tabs border-0" role="tablist">
            @foreach($tabs as $tab)
                <li class="nav-item" role="presentation">
                    <button 
                        class="nav-link {{ $active === $tab['id'] ? 'active' : '' }}"
                        id="{{ $tab['id'] }}-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#{{ $tab['id'] }}"
                        type="button"
                        role="tab"
                        aria-controls="{{ $tab['id'] }}"
                        aria-selected="{{ $active === $tab['id'] ? 'true' : 'false' }}">
                        @if(isset($tab['icon']))
                            <i class="bi {{ $tab['icon'] }}"></i>
                        @endif
                        {{ $tab['title'] }}
                    </button>
                </li>
            @endforeach
        </ul>
    </div>
</div>
