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
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="menu-icon"></iconify-icon>
                    <span>Dashboard</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('dashboard') }}"><i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> dash1</a>
                    </li>
                    <li>
                        <a href="#"><i class="ri-circle-fill circle-icon text-warning-600 w-auto"></i> dash2</a>
                    </li>
                </ul>
            </li>
            <li class="sidebar-menu-group-title">Backoffice</li>
            <li>
                <a href="{{ route('workcenter') }}">
                    <iconify-icon icon="mdi:cog" class="menu-icon"></iconify-icon>
                    <span>Workcenter</span>
                </a>
            </li>
            <li>
                <a href="{{ route('characteristic.index') }}">
                    <iconify-icon icon="mdi:shape" class="menu-icon"></iconify-icon>
                    <span>Characteristic</span>
                </a>
            </li>
            <li>
                    <a href="{{ route('characteristic-group.index') }}">
                    <iconify-icon icon="mdi:account-group" class="menu-icon"></iconify-icon>
                    <span>Characteristic Group</span>
                </a>
            </li>
            <li>
                <a href="{{ route('workcenter-part.index') }}">
                    <iconify-icon icon="mdi:database-edit" class="menu-icon"></iconify-icon>
                    <span>Inventory Part X Workcenter</span>
                </a>
            </li>

            <li class="sidebar-menu-group-title">User Area</li>
            <li>
                    <a href="{{ route('backprint.index') }}">
                    <iconify-icon icon="mdi:printer" class="menu-icon"></iconify-icon>
                    <span>Backprint</span>
                    </a>
            </li>

            <li>
                    <a href="{{ route('production-area.index') }}">
                    <iconify-icon icon="mdi:tools" class="menu-icon"></iconify-icon>
                    <span>Production Area</span>
                </a>
            </li>

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
