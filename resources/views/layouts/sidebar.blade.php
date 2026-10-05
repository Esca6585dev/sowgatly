@php
    $l = app()->getLocale();
    $on = fn (...$patterns) => collect($patterns)->contains(fn ($p) => request()->is($p)) ? 'on' : '';
    $counts = $sidebarCounts ?? [];
    $item = function ($route, $icon, $label, $active, $count = 0, $params = []) use ($l) {
        return compact('route', 'icon', 'label', 'active', 'count', 'params');
    };
    $groups = [
        null => [
            $item('admin.dashboard', 'home', __('Dashboard'), $on('*/admin/dashboard')),
        ],
        __('Sales') => [
            $item('order.index', 'orders', __('Orders'), $on('*/admin/order*'), $counts['orders'] ?? 0),
            $item('product.index', 'box', __('Products'), $on('*/admin/product*')),
            $item('shop.index', 'shop', __('Shops'), $on('*/admin/shop', '*/admin/shop/*')),
            $item('shop-application.index', 'inbox', __('Shop applications'), $on('*/admin/shop-application*'), $counts['applications'] ?? 0),
            $item('chat.index', 'chat', __('Chats'), $on('*/admin/chat*'), $counts['chats'] ?? 0),
            $item('cart.index', 'cart', __('Carts'), $on('*/admin/cart*')),
        ],
        __('Catalog') => [
            $item('category.index', 'grid', __('Categories'), $on('*/admin/*/category*'), 0, ['all']),
            $item('attribute.index', 'list', __('Attributes'), $on('*/admin/attribute*')),
            $item('banner.index', 'image', __('Banners'), $on('*/admin/banner*')),
            $item('region.index', 'map', __('Regions'), $on('*/admin/region*')),
            $item('address.index', 'pin', __('Addresses'), $on('*/admin/address*')),
        ],
        __('People') => [
            $item('user.index', 'users', __('Users'), $on('*/admin/user*')),
            $item('message.index', 'mail', __('Messages'), $on('*/admin/message*')),
            $item('admin.index', 'user', __('Admins'), $on('*/admin/admin', '*/admin/admin/*')),
            $item('role.index', 'shield', __('Roles'), $on('*/admin/role*')),
            $item('permission.index', 'key', __('Permissions'), $on('*/admin/permission*')),
        ],
    ];
@endphp
<aside class="side" aria-label="{{ __('Menu') }}">
    <div class="brand">
        <img src="{{ asset('img/logo/logo-rounded.svg') }}" alt="Sowgatly">
        <div class="t"><b>sowgatly</b><small>{{ __('Admin panel') }}</small></div>
        <button class="collapse-btn" type="button" data-collapse title="{{ __('Collapse menu') }}"><x-admin.icon name="sidebar" class="i-sm" /></button>
    </div>
    <nav class="nav">
        @foreach($groups as $title => $links)
            @if($title)<h6><span class="t">{{ $title }}</span></h6>@endif
            @foreach($links as $link)
                @continue(! Route::has($link['route']))
                <a class="{{ $link['active'] }}" href="{{ route($link['route'], array_merge([$l], $link['params'])) }}" data-tip="{{ $link['label'] }}">
                    <x-admin.icon :name="$link['icon']" /><span class="t">{{ $link['label'] }}</span>
                    @if($link['count'] > 0)<span class="cnt">{{ $link['count'] > 99 ? '99+' : $link['count'] }}</span>@endif
                </a>
                @if($link['route'] === 'category.index' && $link['active'])
                <div class="sub t">
                    @foreach(['all' => __('All categories'), 'parent' => __('Parent categories'), 'sub' => __('Subcategories')] as $type => $name)
                    <a class="{{ $on('*/admin/' . $type . '/category*') }}" href="{{ route('category.index', [$l, $type]) }}">{{ $name }}</a>
                    @endforeach
                </div>
                @endif
            @endforeach
        @endforeach
    </nav>
    <div class="me">
        <span class="avatar">{{ mb_strtoupper(mb_substr($adminName ?? 'A', 0, 1)) }}</span>
        <div class="who t"><b>{{ $adminName ?? 'Admin' }}</b><small>{{ $admin->username ?? '' }}</small></div>
        <form class="t" method="post" action="{{ route('admin.logout', $l) }}">@csrf
            <button type="submit" title="{{ __('Log out') }}"><x-admin.icon name="logout" class="i-sm" /></button>
        </form>
    </div>
</aside>
