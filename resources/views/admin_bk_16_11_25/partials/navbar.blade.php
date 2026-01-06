<nav
    class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
    id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="bx bx-menu bx-sm"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
    <!-- Search -->
    <div class="navbar-nav align-items-center">
        <div class="nav-item d-flex align-items-center">
        <i class="bx bx-search fs-4 lh-0"></i>
        <input
            type="text"
            class="form-control border-0 shadow-none"
            placeholder="Search... "
            aria-label="Search..."
        />
        </div>
    </div>
    <!-- /Search -->

    <ul class="navbar-nav flex-row align-items-center ms-auto">
        <!-- Place this tag where you want the button to render. -->
        

                        <!-- User -->
                        <li class="nav-item navbar-dropdown dropdown-user dropdown">
                            <a
                              class="nav-link dropdown-toggle hide-arrow p-0"
                              href="javascript:void(0);"
                              data-bs-toggle="dropdown">
                              {{-- <div class="avatar avatar-online">
                                <img src="{{ auth()->user()->image ? asset('storage/profile/' . auth()->user()->image) : asset('api/assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle" />
                              </div> --}}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                              <li>
                                <a class="dropdown-item" href="#">
                                  <div class="d-flex">
                                    <div class="flex-shrink-0 me-3">
                                      <div class="avatar avatar-online">
                                        <img src="../assets/img/avatars/1.png" alt class="w-px-40 h-auto rounded-circle" />
                                      </div>
                                    </div>
                                    <div class="flex-grow-1">
                                      <h6 class="mb-0">{{ ucfirst(Auth::user()->name ?? 'Jhone Doe') }}</h6>
                                      <small class="text-muted">{{ Auth()->user()->role->name ?? 'Admin' }}</small>
                                    </div>
                                  </div>
                                </a>
                              </li>
                              <li>
                                <div class="dropdown-divider my-1"></div>
                              </li>
                              <li>
                                <a class="dropdown-item" href="{{ route('admin.profile') }}">
                                  <i class="bx bx-user bx-md me-3"></i><span>My Profile</span>
                                </a>
                              </li>
                              <li>
                                <a class="dropdown-item" href="#"> <i class="bx bx-cog bx-md me-3"></i><span>Settings</span> </a>
                              </li>
                              <li>
                                <div class="dropdown-divider my-1"></div>
                              </li>
                              <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                            
                            <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="mdi mdi-logout text-muted fs-lg align-middle me-1"></i>
                                <span class="align-middle" data-key="t-logout">Logout</span>
                            </a>
                              
                              </li>
                            </ul>
                          </li>
                          <!--/ User -->
    </ul>
    </div>
</nav>