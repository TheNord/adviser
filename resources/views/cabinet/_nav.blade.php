<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a href="{{ route('cabinet.home') }}" class="nav-link {{ session('active') == 'home' ? 'active' : '' }}">Dashboard</a></li>
    <li class="nav-item"><a href="{{ route('cabinet.adverts.index') }}" class="nav-link {{ session('active') == 'adverts' ? 'active' : '' }}">Adverts</a></li>
    <li class="nav-item"><a href="{{ route('cabinet.profile.home') }}" class="nav-link {{ session('active') == 'profile' ? 'active' : '' }}">Profile</a></li>
</ul>