@foreach ($topMenuPages as $page)

    @if ($page->hasChildren())
        <li class="nav-item dropdown">
            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                {{ $page->getMenuTitle() }}<span class="caret"></span>
            </a>
            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                @foreach ($morePages = $page->children()->get() as $page)
                    <a class="dropdown-item"
                       href="{{ route('page', page_path($page)) }}">{{ $page->getMenuTitle() }}</a>
                @endforeach
            </div>
        </li>
    @endif

    @if ($page->isRoot())
        <li><a class="nav-link" href="{{ route('page', page_path($page)) }}">{{ $page->getMenuTitle() }}</a></li>
    @endif

@endforeach