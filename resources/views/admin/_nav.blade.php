 <ul class="nav nav-tabs mb-3">
     <li class="nav-item"><a href="{{ route('admin.home') }}" class="nav-link {{ session('active') == 'home' ? 'active' : '' }}">Dashboard</a></li>
     <li class="nav-item"><a href="{{ route('admin.users.index') }}" class="nav-link {{ session('active') == 'users' ? 'active' : '' }}">Users</a></li>
     <li class="nav-item"><a href="{{ route('admin.regions.index') }}" class="nav-link {{ session('active') == 'regions' ? 'active' : '' }}">Regions</a></li>
     <li class="nav-item"><a href="{{ route('admin.adverts.categories.index') }}" class="nav-link {{ session('active') == 'advert.categories' ? 'active' : '' }}">Categories</a></li>
 </ul>