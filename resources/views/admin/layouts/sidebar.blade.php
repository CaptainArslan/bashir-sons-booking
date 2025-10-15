  <!--sidebar wrapper -->
  <div class="sidebar-wrapper" data-simplebar="true">
      <div class="sidebar-header">
          <div>
              <img src="{{ asset('frontend/assets/img/logo 1.png') }}" alt="logo icon">
          </div>
          {{-- <div>
              <h4 class="logo-text">Rocker</h4>
          </div> --}}
          <div class="toggle-icon ms-auto"><i class='bx bx-arrow-back'></i>
          </div>
      </div>
      <!--navigation-->
      <ul class="metismenu" id="menu">
          <li>
              <a href="{{ route('admin.dashboard') }}">
                  <div class="parent-icon"><i class='bx bx-cookie'></i>
                  </div>
                  <div class="menu-title">Dashboard</div>
              </a>
          </li>

          <li class="menu-label">User Management</li>
          <li>
              <a href="javascript:;" class="has-arrow">
                  <div class="parent-icon"><i class='bx bx-shield-quarter'></i>
                  </div>
                  <div class="menu-title">Roles Management</div>
              </a>
              <ul>
                  <li> <a href="{{ route('admin.roles.index') }}"><i class='bx bx-radio-circle'></i>Roles</a></li>
                  <li> <a href="{{ route('admin.roles.create') }}"><i class='bx bx-radio-circle'></i>Create Role</a>
                  </li>
              </ul>
          </li>
          <li>
              <a href="javascript:;" class="has-arrow">
                  <div class="parent-icon"><i class='bx bx-user'></i>
                  </div>
                  <div class="menu-title">User Management</div>
              </a>
              <ul>
                  <li> <a href="{{ route('admin.users.index') }}"><i class='bx bx-radio-circle'></i>All Users</a></li>
                  <li> <a href="{{ route('admin.users.create') }}"><i class='bx bx-radio-circle'></i>Create User</a></li>
              </ul>
          </li>
          <li class="menu-label">Cities Management</li>
          <li>
              <a href="javascript:;" class="has-arrow">
                  <div class="parent-icon"><i class='bx bx-building'></i>
                  </div>
                  <div class="menu-title">Cities Management</div>
              </a>
              <ul>
                  <li> <a href="{{ route('admin.cities.index') }}"><i class='bx bx-radio-circle'></i>Cities</a></li>
                  <li> <a href="{{ route('admin.cities.create') }}"><i class='bx bx-radio-circle'></i>Create City</a>
                  </li>
              </ul>
          </li>
          <li class="menu-label">Transport Management</li>
          <li>
              <a href="javascript:;" class="has-arrow">
                  <div class="parent-icon"><i class='bx bx-chair'></i>
                  </div>
                  <div class="menu-title">Counter/Terminal Management</div>
              </a>
              <ul>
                  <li> <a href="{{ route('admin.counter-terminals.index') }}"><i class='bx bx-radio-circle'></i>Terminals</a></li>
                  <li> <a href="{{ route('admin.counter-terminals.create') }}"><i class='bx bx-radio-circle'></i>Create Counter</a>
                  </li>
              </ul>
          </li>
          <li>
              <a href="javascript:;" class="has-arrow">
                  <div class="parent-icon"><i class='bx bx-bus'></i>
                  </div>
                  <div class="menu-title">Bus Management</div>
              </a>
              <ul>
                  <li> <a href="{{ route('admin.buses.index') }}"><i class='bx bx-radio-circle'></i>All Buses</a></li>
                  <li> <a href="{{ route('admin.buses.create') }}"><i class='bx bx-radio-circle'></i>Add New Bus</a></li>
              </ul>
          </li>
          <li>
              <a href="javascript:;" class="has-arrow">
                  <div class="parent-icon"><i class='bx bx-category'></i>
                  </div>
                  <div class="menu-title">Bus Configuration</div>
              </a>
              <ul>
                  <li> <a href="{{ route('admin.bus-types.index') }}"><i class='bx bx-radio-circle'></i>Bus Types</a></li>
                  <li> <a href="{{ route('admin.bus-layouts.index') }}"><i class='bx bx-radio-circle'></i>Bus Layouts</a></li>
                  <li> <a href="{{ route('admin.facilities.index') }}"><i class='bx bx-radio-circle'></i>Facilities</a></li>
              </ul>
          </li>
          <li class="menu-label">Content Management</li>
          <li>
              <a href="javascript:;" class="has-arrow">
                  <div class="parent-icon"><i class='bx bx-image'></i>
                  </div>
                  <div class="menu-title">Banner Management</div>
              </a>
              <ul>
                  <li> <a href="{{ route('admin.banners.index') }}"><i class='bx bx-radio-circle'></i>All Banners</a></li>
                  <li> <a href="{{ route('admin.banners.create') }}"><i class='bx bx-radio-circle'></i>Add New Banner</a></li>
              </ul>
          </li>
          <li>
              <a href="{{ route('admin.general-settings.index') }}">
                  <div class="parent-icon"><i class='bx bx-cog'></i>
                  </div>
                  <div class="menu-title">General Settings</div>
              </a>
          </li>
          <li class="menu-label">Customer Support</li>
          <li>
              <a href="{{ route('admin.enquiries.index') }}">
                  <div class="parent-icon"><i class='bx bx-message-dots'></i>
                  </div>
                  <div class="menu-title">Customer Enquiries</div>
              </a>
          </li>
      </ul>
      <!--end navigation-->
  </div>
  <!--end sidebar wrapper -->
