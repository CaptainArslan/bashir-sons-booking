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
                  <li> <a href="{{ route('admin.roles.index') }}"><i class='bx bx-radio-circle'></i>Employees</a></li>
                  <li> <a href="{{ route('admin.roles.create') }}"><i class='bx bx-radio-circle'></i>Create Employee</a>
                  </li>
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
      </ul>
      <!--end navigation-->
  </div>
  <!--end sidebar wrapper -->
