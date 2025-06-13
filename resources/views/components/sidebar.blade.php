<aside class="sidebar">
    <button type="button" class="sidebar-close-btn !mt-4">
        <iconify-icon icon="radix-icons:cross-2"></iconify-icon>
    </button>
    <div>
        <a href="{{ route('dashboard') }}" class="sidebar-logo">
            <img src="{{ asset('assets/images/borgstena_logo.png') }}" alt="site logo" class="light-logo">
            <img src="{{ asset('assets/images/borgstena_logo_black.svg') }}" alt="site logo" class="dark-logo">
            <img src="{{ asset('assets/images/favicon2.png') }}" alt="site logo" class="logo-icon" style="width: 40px; height: 40px;">
        </a>
    </div>
    <div class="sidebar-menu-area">
        <ul class="sidebar-menu" id="sidebar-menu">
            @foreach ($menus as $menu)
            @if ($menu->childs->isEmpty())
                @if ($menu->route == '')
                    <li class="sidebar-menu-group-title">{{ $menu->title }}</li>
                    <li></li>
                @else
                    <li>
                        <a href="{{ route($menu->route) }}">
                            <iconify-icon icon="{{ $menu->icon }}" class="menu-icon"></iconify-icon>
                            <span>{{ $menu->title }}</span>
                        </a>
                    </li>
                @endif
            @else
                <li class="sidebar-menu-group-title">{{ $menu->title }}</li>
                @foreach ($menu->childs as $child)
                    <li>
                        <a href="{{ route($child->route) }}">
                            <iconify-icon icon="{{ $child->icon }}" class="menu-icon"></iconify-icon>
                            <span>{{ $child->title }}</span>
                        </a>
                    </li>
                @endforeach
            @endif
            @endforeach


            <li class="sidebar-menu-group-title">Session</li>

            <li>
                <form method="POST" action="{{ route('logout') }}" >
                    @csrf
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                        <iconify-icon icon="lucide:power" class="menu-icon"></iconify-icon>
                        <span>{{ __('Log Out') }}</span>
                    </a>
                </form>
            </li>

        </ul>
    </div>
</aside>
