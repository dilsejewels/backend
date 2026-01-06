<style>
    #menuSearch {
        border-radius: 8px;
        padding: 6px 10px;
        font-size: 14px;
    }
</style>
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="index.html" class="app-brand-link">
            <span class="app-brand-logo demo">
                <img src="{{ asset('api/assets/img/admin/logo-black.png') }}" alt="Logo" width="200">
            </span>
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm d-flex align-items-center justify-content-center"></i>
        </a>
    </div>

    <!-- Search Bar -->
    <div class="p-3">
        <input type="text" id="menuSearch" class="form-control form-control-sm" placeholder="Search menu..." />
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- DASHBOARD SECTION -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Dashboard</span>
        </li>
        <li class="menu-item {{ request()->is('/') ? 'active open' : '' }}">
            <a href="{{ route('admin.dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-smile"></i>
                <div class="text-truncate" data-i18n="Dashboard">Dashboard</div>
            </a>
        </li>

        <!-- DIAMOND SECTION -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Diamond</span>
        </li>
        <li class="menu-item {{ request()->is('admin/shades*') || request()->is('admin/shapes*') || request()->is('admin/sizes*') || request()->is('admin/clarity*') || request()->is('admin/diamond*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-diamond"></i>
                <div class="text-truncate">Diamond Masters</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('shades.index') ? 'active' : '' }}">
                    <a href="{{ route('shades.index') }}" class="menu-link">Shades</a>
                </li>
                <li class="menu-item {{ request()->routeIs('shapes.index') ? 'active' : '' }}">
                    <a href="{{ route('shapes.index') }}" class="menu-link">Shape</a>
                </li>
                <li class="menu-item {{ request()->routeIs('sizes.index') ? 'active' : '' }}">
                    <a href="{{ route('sizes.index') }}" class="menu-link">Size</a>
                </li>
                <li class="menu-item {{ request()->routeIs('clarity.index') ? 'active' : '' }}">
                    <a href="{{ route('clarity.index') }}" class="menu-link">Clarity</a>
                </li>
                <li class="menu-item {{ request()->routeIs('color.index') ? 'active' : '' }}">
                    <a href="{{ route('color.index') }}" class="menu-link">Color</a>
                </li>
                <li class="menu-item {{ request()->routeIs('cut.index') ? 'active' : '' }}">
                    <a href="{{ route('cut.index') }}" class="menu-link">Cut</a>
                </li>
                <li class="menu-item {{ request()->routeIs('girdle.index') ? 'active' : '' }}">
                    <a href="{{ route('girdle.index') }}" class="menu-link">Girdle</a>
                </li>
                <li class="menu-item {{ request()->routeIs('culet.index') ? 'active' : '' }}">
                    <a href="{{ route('culet.index') }}" class="menu-link">Culet</a>
                </li>
                <li class="menu-item {{ request()->routeIs('fancyColor.index') ? 'active' : '' }}">
                    <a href="{{ route('fancyColor.index') }}" class="menu-link">Fancy Color Overtones</a>
                </li>
                <li class="menu-item {{ request()->routeIs('fancy-color-intensity.index') ? 'active' : '' }}">
                    <a href="{{ route('fancy-color-intensity.index') }}" class="menu-link">Fancy Color Intensity</a>
                </li>
                <li class="menu-item {{ request()->routeIs('diamond-weight-groups.index') ? 'active' : '' }}">
                    <a href="{{ route('diamond-weight-groups.index') }}" class="menu-link">Weight Group</a>
                </li>
                <li class="menu-item {{ request()->routeIs('diamondpolish.index') ? 'active' : '' }}">
                    <a href="{{ route('diamondpolish.index') }}" class="menu-link">Polish</a>
                </li>
                <li class="menu-item {{ request()->routeIs('diamondlab.index') ? 'active' : '' }}">
                    <a href="{{ route('diamondlab.index') }}" class="menu-link">Lab</a>
                </li>
                <li class="menu-item {{ request()->routeIs('keytosymbols.index') ? 'active' : '' }}">
                    <a href="{{ route('keytosymbols.index') }}" class="menu-link">Key to Symbols</a>
                </li>
            </ul>
        </li>
        <li class="menu-item {{ request()->is('diamond-master*') ? 'active open' : '' }}">
            <a href="{{ route('diamond-master.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-list-ul"></i>
                <div class="text-truncate">Diamond List</div>
            </a>
        </li>

        <!-- JEWELLERY SECTION -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Jewellery</span>
        </li>
        <li class="menu-item {{ request()->is('product*') || request()->is('category*') || request()->is('metaltype*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon fa fa-ring"></i>
                <div class="text-truncate"> Jewellery</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('product.index') ? 'active' : '' }}">
                    <a href="{{ route('product.index') }}" class="menu-link">Products</a>
                </li>
                <li class="menu-item {{ request()->routeIs('category.index') ? 'active' : '' }}">
                    <a href="{{ route('category.index') }}" class="menu-link">Category</a>
                </li>
                <li class="menu-item {{ request()->routeIs('metaltype.index') ? 'active' : '' }}">
                    <a href="{{ route('metaltype.index') }}" class="menu-link">Metal Type</a>
                </li>
                <li class="menu-item {{ request()->routeIs('diamondqualitygroup.index') ? 'active' : '' }}">
                    <a href="{{ route('diamondqualitygroup.index') }}" class="menu-link">Quality Group</a>
                </li>
                <li class="menu-item {{ request()->routeIs('ProductClarity.index') ? 'active' : '' }}">
                    <a href="{{ route('ProductClarity.index') }}" class="menu-link">Product Clarity</a>
                </li>
                <li class="menu-item {{ request()->routeIs('product-color.index') ? 'active' : '' }}">
                    <a href="{{ route('product-color.index') }}" class="menu-link">Product Color</a>
                </li>
                <li class="menu-item {{ request()->routeIs('product-cut.index') ? 'active' : '' }}">
                    <a href="{{ route('product-cut.index') }}" class="menu-link">Product Cut</a>
                </li>
                <li class="menu-item {{ request()->routeIs('tax-classes.index') ? 'active' : '' }}">
                    <a href="{{ route('tax-classes.index') }}" class="menu-link">Shop Tax Class</a>
                </li>
                <li class="menu-item {{ request()->routeIs('tax-rates.index') ? 'active' : '' }}">
                    <a href="{{ route('tax-rates.index') }}" class="menu-link">Shop Tax Rate</a>
                </li>
                <li class="menu-item {{ request()->routeIs('product-style-category.index') ? 'active' : '' }}">
                    <a href="{{ route('product-style-category.index') }}" class="menu-link">Product Style Category</a>
                </li>
                <li class="menu-item {{ request()->routeIs('collections.index') ? 'active' : '' }}">
                    <a href="{{ route('collections.index') }}" class="menu-link">Product Collection</a>
                </li>
                <li class="menu-item {{ request()->routeIs('style-groups.index') ? 'active' : '' }}">
                    <a href="{{ route('style-groups.index') }}" class="menu-link">Product Style Group</a>
                </li>
            </ul>
        </li>

        <!-- Coupon -->
        <li class="menu-item {{ request()->routeIs('admin.coupons.index') ? 'active' : '' }}">
            <a href="{{ route('admin.coupons.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-purchase-tag-alt"></i>
                <div class="text-truncate">Coupon</div>
            </a>
        </li>

        <!-- ORDERS -->
        <li class="menu-item {{ request()->routeIs('orders.index') ? 'active' : '' }}">
            <a href="{{ route('orders.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-cart"></i>
                <div class="text-truncate">Orders</div>
            </a>
        </li>

        <!-- BLOG -->
        <li class="menu-item {{ request()->routeIs('admin.blogs.index') ? 'active' : '' }}">
            <a href="{{ route('admin.blogs.index') }}" class="menu-link">
                <i class="menu-icon fa fa-blog"></i>
                <div class="text-truncate">Blog</div>
            </a>
        </li>

        <!-- ORDERS -->
        <li class="menu-item {{ request()->routeIs('metal-prices.index') ? 'active' : '' }}">
            <a href="{{ route('metal-prices.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-coin"></i>
                <div class="text-truncate">Metal Price</div>
            </a>
        </li>

        <!-- APPOINTMENT -->
        <li class="menu-item {{ request()->routeIs('admin.appointments.index') ? 'active' : '' }}">
            <a href="{{ route('admin.appointments.index') }}" class="menu-link">
                <i class="menu-icon fa fa-calendar-alt"></i>
                <div class="text-truncate">Appointment</div>
            </a>
        </li>


        <!-- VENDORS -->
        <li class="menu-item {{ request()->routeIs('vendor.index') ? 'active' : '' }}">
            <a href="{{ route('vendor.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-store"></i>
                <div class="text-truncate">Vendors</div>
            </a>
        </li>

        <!-- ENQUIRY -->
        <li class="menu-item {{ request()->routeIs('enquiries.index') ? 'active' : '' }}">
            <a href="{{ route('enquiries.index') }}" class="menu-link">
                <i class="menu-icon fa fa-comment-dots"></i>
                <div class="text-truncate">Enquiries</div>
            </a>
        </li>

        <!-- CONTACT US -->
        <li class="menu-item {{ request()->routeIs('admin.contactus.index') ? 'active' : '' }}">
            <a href="{{ route('admin.contactus.index') }}" class="menu-link">
                <i class="menu-icon fa fa-envelope"></i>
                <div class="text-truncate">Contact Us</div>
            </a>
        </li>
    </ul>
</aside>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('menuSearch');
        const allMenuItems = document.querySelectorAll('#layout-menu .menu-item');

        searchInput.addEventListener('keyup', function () {
            const searchText = this.value.toLowerCase();
            if (searchText === '') {
                allMenuItems.forEach(item => {
                item.style.display = '';
                item.classList.remove('open');
                });
                return;
            }

            allMenuItems.forEach(item => {
                const linkText = item.textContent.toLowerCase();
                const submenu = item.querySelector('.menu-sub');
                if (submenu) {
                    const subItems = submenu.querySelectorAll('.menu-item');
                    let matchFound = false;
                    subItems.forEach(sub => {
                        const subText = sub.textContent.toLowerCase();
                        if (subText.includes(searchText)) {
                            sub.style.display = '';
                            matchFound = true;
                        } else {
                            sub.style.display = 'none';
                        }
                    });
                    if (matchFound) {
                        item.style.display = '';
                        item.classList.add('open');
                    } else if (linkText.includes(searchText)) {
                        item.style.display = '';
                        item.classList.remove('open');
                        subItems.forEach(sub => sub.style.display = 'none');
                    } else {
                        item.style.display = 'none';
                        item.classList.remove('open');
                    }

                } else {
                    if (linkText.includes(searchText)) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                }
            });
        });
    });
</script>

