<!-- Dashboard -->
<li class="nav-item">
    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <i class="nav-icon fas fa-tachometer-alt"></i>
        <p>Dashboard</p>
    </a>
</li>

<!-- Usuários -->
@if(Route::has('admin.users.index'))
<li class="nav-item">
    <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-users"></i>
        <p>Usuários</p>
    </a>
</li>
@endif

<!-- Campeonatos -->
@if(Route::has('admin.championships.index'))
<li class="nav-item">
    <a href="{{ route('admin.championships.index') }}" class="nav-link {{ request()->routeIs('admin.championships.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-trophy"></i>
        <p>Campeonatos</p>
    </a>
</li>
@endif

<!-- Configurações -->
@if(Route::has('admin.settings.index'))
<li class="nav-item">
    <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-cog"></i>
        <p>Configurações</p>
    </a>
</li>
@endif

<!-- Textos do Sistema -->
@if(Route::has('admin.system-texts.index'))
<li class="nav-item">
    <a href="{{ route('admin.system-texts.index') }}" class="nav-link {{ request()->routeIs('admin.system-texts.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-language"></i>
        <p>Textos do Sistema</p>
    </a>
</li>
@endif

<!-- Relatórios -->
@if(Route::has('admin.reports.index'))
<li class="nav-item">
    <a href="{{ route('admin.reports.index') }}" class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-chart-bar"></i>
        <p>Relatórios</p>
    </a>
</li>
@endif
