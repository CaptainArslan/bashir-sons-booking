  <!--sidebar wrapper -->
  <div class="sidebar-wrapper" data-simplebar="true">
      <div class="sidebar-header" style="padding: 1rem 0.75rem;">
          <div>
              <img src="{{ asset('frontend/assets/img/logo 1.png') }}" alt="logo icon" style="height: 32px;">
          </div>
          {{-- <div>
              <h4 class="logo-text">Rocker</h4>
          </div> --}}
          <div class="toggle-icon ms-auto"><i class='bx bx-arrow-back'></i>
          </div>
      </div>
      <!--navigation-->
      <ul class="metismenu" id="menu" style="padding: 0.5rem 0;">
          <li>
              <a href="{{ route('admin.dashboard') }}" style="padding: 0.75rem 1rem; font-size: 0.875rem;">
                  <div class="parent-icon" style="width: 20px; height: 20px; font-size: 1rem;"><i
                          class='bx bx-cookie'></i>
                  </div>
                  <div class="menu-title" style="font-size: 0.875rem; font-weight: 500;">Dashboard</div>
              </a>
          </li>

          @canany(['view roles', 'view permissions', 'view users'])
              <li class="menu-label"
                  style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; padding: 0.5rem 1rem; color: #6c757d;">
                  User Management</li>
          @endcanany

          @canany(['view roles', 'view permissions'])
              <li>
                  <a href="javascript:;" class="has-arrow" style="padding: 0.75rem 1rem; font-size: 0.875rem;">
                      <div class="parent-icon" style="width: 20px; height: 20px; font-size: 1rem;"><i
                              class='bx bx-shield-quarter'></i>
                      </div>
                      <div class="menu-title" style="font-size: 0.875rem; font-weight: 500;">Access Control</div>
                  </a>
                  <ul style="padding-left: 0;">
                      @can('view roles')
                          <li> <a href="{{ route('admin.roles.index') }}"
                                  style="padding: 0.5rem 1rem 0.5rem 2.5rem; font-size: 0.8rem;"><i class='bx bx-radio-circle'
                                      style="font-size: 0.7rem;"></i>Roles</a></li>
                      @endcan
                      @can('view permissions')
                          <li> <a href="{{ route('admin.permissions.index') }}"
                                  style="padding: 0.5rem 1rem 0.5rem 2.5rem; font-size: 0.8rem;"><i class='bx bx-radio-circle'
                                      style="font-size: 0.7rem;"></i>Permissions</a></li>
                      @endcan
                  </ul>
              </li>
          @endcanany

          @can('view users')
              <li>
                  <a href="javascript:;" class="has-arrow">
                      <div class="parent-icon"><i class='bx bx-user'></i>
                      </div>
                      <div class="menu-title">User Management</div>
                  </a>
                  <ul>
                      <li> <a href="{{ route('admin.users.index') }}"><i class='bx bx-radio-circle'></i>All Users</a></li>
                      @can('create users')
                          <li> <a href="{{ route('admin.users.create') }}"><i class='bx bx-radio-circle'></i>Create User</a>
                          </li>
                      @endcan
                  </ul>
              </li>
          @endcan
          @can('view cities')
              <li class="menu-label">Cities Management</li>
              <li>
                  <a href="javascript:;" class="has-arrow">
                      <div class="parent-icon"><i class='bx bx-building'></i>
                      </div>
                      <div class="menu-title">Cities Management</div>
                  </a>
                  <ul>
                      <li> <a href="{{ route('admin.cities.index') }}"><i class='bx bx-radio-circle'></i>Cities</a></li>
                      @can('create cities')
                          <li> <a href="{{ route('admin.cities.create') }}"><i class='bx bx-radio-circle'></i>Create City</a>
                          </li>
                      @endcan
                  </ul>
              </li>
          @endcan
          @canany([
              'view terminals',
              'view buses',
              'view bus types',
              'view bus layouts',
              'view facilities',
              'view
              routes',
              'view route stops',
              'view route timetables',
              ])
              <li class="menu-label">Transport Management</li>
          @endcanany

          @can('view terminals')
              <li>
                  <a href="javascript:;" class="has-arrow">
                      <div class="parent-icon"><i class='bx bx-chair'></i>
                      </div>
                      <div class="menu-title">Counter/Terminal Management</div>
                  </a>
                  <ul>
                      <li> <a href="{{ route('admin.counter-terminals.index') }}"><i
                                  class='bx bx-radio-circle'></i>Terminals</a></li>
                      @can('create terminals')
                          <li> <a href="{{ route('admin.counter-terminals.create') }}"><i class='bx bx-radio-circle'></i>Create
                                  Counter</a></li>
                      @endcan
                  </ul>
              </li>
          @endcan

          @canany(['view bus types', 'view bus layouts', 'view facilities'])
              <li>
                  <a href="javascript:;" class="has-arrow">
                      <div class="parent-icon"><i class='bx bx-category'></i>
                      </div>
                      <div class="menu-title">Bus Configuration</div>
                  </a>
                  <ul>
                      @can('view bus types')
                          <li> <a href="{{ route('admin.bus-types.index') }}"><i class='bx bx-radio-circle'></i>Bus Types</a>
                          </li>
                      @endcan
                      @can('view bus layouts')
                          <li> <a href="{{ route('admin.bus-layouts.index') }}"><i class='bx bx-radio-circle'></i>Bus
                                  Layouts</a></li>
                      @endcan
                      @can('view facilities')
                          <li> <a href="{{ route('admin.facilities.index') }}"><i class='bx bx-radio-circle'></i>Facilities</a>
                          </li>
                      @endcan
                  </ul>
              </li>
          @endcanany

          @can('view buses')
              <li>
                  <a href="javascript:;" class="has-arrow">
                      <div class="parent-icon"><i class='bx bx-bus'></i>
                      </div>
                      <div class="menu-title">Bus Management</div>
                  </a>
                  <ul>
                      <li> <a href="{{ route('admin.buses.index') }}"><i class='bx bx-radio-circle'></i>All Buses</a>
                      </li>
                      @can('create buses')
                          <li> <a href="{{ route('admin.buses.create') }}"><i class='bx bx-radio-circle'></i>Add New Bus</a>
                          </li>
                      @endcan
                  </ul>
              </li>
          @endcan

          @can('view routes')
              <li>
                  <a href="javascript:;" class="has-arrow">
                      <div class="parent-icon"><i class='bx bx-map'></i>
                      </div>
                      <div class="menu-title">Route Management</div>
                  </a>
                  <ul>
                      <li> <a href="{{ route('admin.routes.index') }}"><i class='bx bx-radio-circle'></i>All Routes</a>
                      </li>
                      @can('create routes')
                          <li> <a href="{{ route('admin.routes.create') }}"><i class='bx bx-radio-circle'></i>Add New Route</a>
                          </li>
                      @endcan
                      @can('view route stops')
                          <li> <a href="{{ route('admin.route-stops.index') }}"><i class='bx bx-radio-circle'></i>Route
                                  Stops</a></li>
                      @endcan
                      @can('view route fares')
                          <li> <a href="{{ route('admin.route-fares.index') }}"><i class='bx bx-radio-circle'></i>All Route
                                  Fares</a></li>
                      @endcan
                      @can('view route timetables')
                          <li> <a href="{{ route('admin.route-timetables.index') }}"><i class='bx bx-radio-circle'></i>Route
                                  Timetables</a></li>
                      @endcan
                      @can('create route timetables')
                          <li> <a href="{{ route('admin.route-timetables.create') }}"><i class='bx bx-radio-circle'></i>Add
                                  New
                                  Timetable</a></li>
                      @endcan
                  </ul>
              </li>
          @endcan

          @canany(['view banners', 'view general settings'])
              <li class="menu-label">Content Management</li>
          @endcanany

          @can('view banners')
              <li>
                  <a href="javascript:;" class="has-arrow">
                      <div class="parent-icon"><i class='bx bx-image'></i>
                      </div>
                      <div class="menu-title">Banner Management</div>
                  </a>
                  <ul>
                      <li> <a href="{{ route('admin.banners.index') }}"><i class='bx bx-radio-circle'></i>All Banners</a>
                      </li>
                      @can('create banners')
                          <li> <a href="{{ route('admin.banners.create') }}"><i class='bx bx-radio-circle'></i>Add New
                                  Banner</a></li>
                      @endcan
                  </ul>
              </li>
          @endcan

          @can('view general settings')
              <li>
                  <a href="{{ route('admin.general-settings.index') }}">
                      <div class="parent-icon"><i class='bx bx-cog'></i>
                      </div>
                      <div class="menu-title">General Settings</div>
                  </a>
              </li>
          @endcan

          @can('view enquiries')
              <li class="menu-label">Customer Support</li>
              <li>
                  <a href="{{ route('admin.enquiries.index') }}">
                      <div class="parent-icon"><i class='bx bx-message-dots'></i>
                      </div>
                      <div class="menu-title">Customer Enquiries</div>
                  </a>
              </li>
          @endcan
      </ul>
      <!--end navigation-->
  </div>
  <!--end sidebar wrapper -->
