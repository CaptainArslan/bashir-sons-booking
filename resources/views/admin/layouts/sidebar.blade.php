  <!--sidebar wrapper -->
  <div class="sidebar-wrapper" data-simplebar="true">
      <div class="sidebar-header" style="padding: 1rem 0.75rem;">
          <div>
              <img src="{{ asset('frontend/assets/img/logo 1.png') }}" alt="logo icon" style="height: 32px;">
          </div>
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
                  <a href="javascript:;" class="has-arrow" style="padding: 0.75rem 1rem; font-size: 0.875rem;">
                      <div class="parent-icon" style="width: 20px; height: 20px; font-size: 1rem;"><i
                              class='bx bx-user'></i>
                      </div>
                      <div class="menu-title" style="font-size: 0.875rem; font-weight: 500;">User Management</div>
                  </a>
                  <ul style="padding-left: 0;">

                      @can('view users')
                          <li> <a href="{{ route('admin.users.index') }}"
                                  style="padding: 0.5rem 1rem 0.5rem 2.5rem; font-size: 0.8rem;"><i class='bx bx-radio-circle'
                                      style="font-size: 0.7rem;"></i>All Users</a></li>
                      @endcan
                      @can('create users')
                          <li> <a href="{{ route('admin.users.create') }}"
                                  style="padding: 0.5rem 1rem 0.5rem 2.5rem; font-size: 0.8rem;"><i class='bx bx-radio-circle'
                                      style="font-size: 0.7rem;"></i>Create User</a>
                          </li>
                      @endcan
                      @can('view employees')
                          <li> <a href="{{ route('admin.employees.index') }}"
                                  style="padding: 0.5rem 1rem 0.5rem 2.5rem; font-size: 0.8rem;"><i class='bx bx-radio-circle'
                                      style="font-size: 0.7rem;"></i>Employees</a></li>
                      @endcan
                      @can('manage users')
                          <li> <a href="{{ route('admin.employees.create') }}"
                                  style="padding: 0.5rem 1rem 0.5rem 2.5rem; font-size: 0.8rem;"><i class='bx bx-radio-circle'
                                      style="font-size: 0.7rem;"></i>Create Employee</a>
                          </li>
                      @endcan
                  </ul>
              </li>
          @endcan

          @can('view cities')
              <li class="menu-label"
                  style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; padding: 0.5rem 1rem; color: #6c757d;">
                  Cities Management</li>
              <li>
                  <a href="javascript:;" class="has-arrow" style="padding: 0.75rem 1rem; font-size: 0.875rem;">
                      <div class="parent-icon" style="width: 20px; height: 20px; font-size: 1rem;"><i
                              class='bx bx-building'></i>
                      </div>
                      <div class="menu-title" style="font-size: 0.875rem; font-weight: 500;">Cities Management</div>
                  </a>
                  <ul style="padding-left: 0;">
                      <li> <a href="{{ route('admin.cities.index') }}"
                              style="padding: 0.5rem 1rem 0.5rem 2.5rem; font-size: 0.8rem;"><i class='bx bx-radio-circle'
                                  style="font-size: 0.7rem;"></i>Cities</a></li>
                      @can('create cities')
                          <li> <a href="{{ route('admin.cities.create') }}"
                                  style="padding: 0.5rem 1rem 0.5rem 2.5rem; font-size: 0.8rem;"><i class='bx bx-radio-circle'
                                      style="font-size: 0.7rem;"></i>Create City</a>
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
              'view timetables',
              'view schedules',
              ])
              <li class="menu-label"
                  style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; padding: 0.5rem 1rem; color: #6c757d;">
                  Transport Management</li>
          @endcanany

          @can('view terminals')
              <li>
                  <a href="javascript:;" class="has-arrow" style="padding: 0.75rem 1rem; font-size: 0.875rem;">
                      <div class="parent-icon" style="width: 20px; height: 20px; font-size: 1rem;"><i
                              class='bx bx-chair'></i>
                      </div>
                      <div class="menu-title" style="font-size: 0.875rem; font-weight: 500;">Counter/Terminal Management
                      </div>
                  </a>
                  <ul style="padding-left: 0;">
                      <li> <a href="{{ route('admin.counter-terminals.index') }}"
                              style="padding: 0.5rem 1rem 0.5rem 2.5rem; font-size: 0.8rem;"><i class='bx bx-radio-circle'
                                  style="font-size: 0.7rem;"></i>Terminals</a></li>
                      @can('create terminals')
                          <li> <a href="{{ route('admin.counter-terminals.create') }}"
                                  style="padding: 0.5rem 1rem 0.5rem 2.5rem; font-size: 0.8rem;"><i class='bx bx-radio-circle'
                                      style="font-size: 0.7rem;"></i>Create Counter</a></li>
                      @endcan
                  </ul>
              </li>
          @endcan

          @canany(['view bus types', 'view bus layouts', 'view facilities'])
              <li>
                  <a href="javascript:;" class="has-arrow" style="padding: 0.75rem 1rem; font-size: 0.875rem;">
                      <div class="parent-icon" style="width: 20px; height: 20px; font-size: 1rem;"><i
                              class='bx bx-category'></i>
                      </div>
                      <div class="menu-title" style="font-size: 0.875rem; font-weight: 500;">Bus Configuration</div>
                  </a>
                  <ul style="padding-left: 0;">
                      @can('view bus types')
                          <li> <a href="{{ route('admin.bus-types.index') }}"
                                  style="padding: 0.5rem 1rem 0.5rem 2.5rem; font-size: 0.8rem;"><i class='bx bx-radio-circle'
                                      style="font-size: 0.7rem;"></i>Bus Types</a>
                          </li>
                      @endcan
                      @can('view bus layouts')
                          <li> <a href="{{ route('admin.bus-layouts.index') }}"
                                  style="padding: 0.5rem 1rem 0.5rem 2.5rem; font-size: 0.8rem;"><i class='bx bx-radio-circle'
                                      style="font-size: 0.7rem;"></i>Bus Layouts</a></li>
                      @endcan
                      @can('view facilities')
                          <li> <a href="{{ route('admin.facilities.index') }}"
                                  style="padding: 0.5rem 1rem 0.5rem 2.5rem; font-size: 0.8rem;"><i class='bx bx-radio-circle'
                                      style="font-size: 0.7rem;"></i>Facilities</a>
                          </li>
                      @endcan
                  </ul>
              </li>
          @endcanany

          @can('view buses')
              <li>
                  <a href="javascript:;" class="has-arrow" style="padding: 0.75rem 1rem; font-size: 0.875rem;">
                      <div class="parent-icon" style="width: 20px; height: 20px; font-size: 1rem;"><i
                              class='bx bx-bus'></i>
                      </div>
                      <div class="menu-title" style="font-size: 0.875rem; font-weight: 500;">Bus Management</div>
                  </a>
                  <ul style="padding-left: 0;">
                      <li> <a href="{{ route('admin.buses.index') }}"
                              style="padding: 0.5rem 1rem 0.5rem 2.5rem; font-size: 0.8rem;"><i class='bx bx-radio-circle'
                                  style="font-size: 0.7rem;"></i>All Buses</a>
                      </li>
                      @can('create buses')
                          <li> <a href="{{ route('admin.buses.create') }}"
                                  style="padding: 0.5rem 1rem 0.5rem 2.5rem; font-size: 0.8rem;"><i class='bx bx-radio-circle'
                                      style="font-size: 0.7rem;"></i>Add New Bus</a>
                          </li>
                      @endcan
                  </ul>
              </li>
          @endcan

          @can('view routes')
              <li>
                  <a href="javascript:;" class="has-arrow" style="padding: 0.75rem 1rem; font-size: 0.875rem;">
                      <div class="parent-icon" style="width: 20px; height: 20px; font-size: 1rem;"><i
                              class='bx bx-map'></i>
                      </div>
                      <div class="menu-title" style="font-size: 0.875rem; font-weight: 500;">Route Management</div>
                  </a>
                  <ul style="padding-left: 0;">
                      <li> <a href="{{ route('admin.routes.index') }}"
                              style="padding: 0.5rem 1rem 0.5rem 2.5rem; font-size: 0.8rem;"><i class='bx bx-radio-circle'
                                  style="font-size: 0.7rem;"></i>All Routes</a>
                      </li>
                      @can('create routes')
                          <li> <a href="{{ route('admin.routes.create') }}"
                                  style="padding: 0.5rem 1rem 0.5rem 2.5rem; font-size: 0.8rem;"><i class='bx bx-radio-circle'
                                      style="font-size: 0.7rem;"></i>Add New Route</a>
                          </li>
                      @endcan
                      {{-- @can('view route stops')
                          <li> <a href="{{ route('admin.route-stops.index') }}"
                                  style="padding: 0.5rem 1rem 0.5rem 2.5rem; font-size: 0.8rem;"><i class='bx bx-radio-circle'
                                      style="font-size: 0.7rem;"></i>Route Stops</a></li>
                      @endcan --}}
                  </ul>
              </li>
          @endcan

          @can('view timetables')
              <li>
                  <a href="javascript:;" class="has-arrow" style="padding: 0.75rem 1rem; font-size: 0.875rem;">
                      <div class="parent-icon" style="width: 20px; height: 20px; font-size: 1rem;"><i
                              class='bx bx-time'></i>
                      </div>
                      <div class="menu-title" style="font-size: 0.875rem; font-weight: 500;">Timetable Management</div>
                  </a>
                  <ul style="padding-left: 0;">
                      <li> <a href="{{ route('admin.timetables.index') }}"
                              style="padding: 0.5rem 1rem 0.5rem 2.5rem; font-size: 0.8rem;"><i class='bx bx-radio-circle'
                                  style="font-size: 0.7rem;"></i>All Timetables</a>
                      </li>
                      @can('create timetables')
                          <li> <a href="{{ route('admin.timetables.create') }}"
                                  style="padding: 0.5rem 1rem 0.5rem 2.5rem; font-size: 0.8rem;"><i class='bx bx-radio-circle'
                                      style="font-size: 0.7rem;"></i>Generate Timetables</a>
                          </li>
                      @endcan
                  </ul>
              </li>
          @endcan

          @can('view fares')
              <li>
                  <a href="javascript:;" class="has-arrow" style="padding: 0.75rem 1rem; font-size: 0.875rem;">
                      <div class="parent-icon" style="width: 20px; height: 20px; font-size: 1rem;"><i
                              class='bx bx-money'></i>
                      </div>
                      <div class="menu-title" style="font-size: 0.875rem; font-weight: 500;">Fare Management</div>
                  </a>
                  <ul style="padding-left: 0;">
                      @can('view fares')
                          <li> <a href="{{ route('admin.fares.index') }}"
                                  style="padding: 0.5rem 1rem 0.5rem 2.5rem; font-size: 0.8rem;"><i class='bx bx-radio-circle'
                                      style="font-size: 0.7rem;"></i>All Fares</a></li>
                      @endcan
                      @can('create fares')
                          <li> <a href="{{ route('admin.fares.create') }}"
                                  style="padding: 0.5rem 1rem 0.5rem 2.5rem; font-size: 0.8rem;"><i class='bx bx-radio-circle'
                                      style="font-size: 0.7rem;"></i>Add New Fare</a></li>
                      @endcan
                  </ul>
              </li>
          @endcan

          <li>
              <a href="javascript:;" class="has-arrow" style="padding: 0.75rem 1rem; font-size: 0.875rem;">
                  <div class="parent-icon" style="width: 20px; height: 20px; font-size: 1rem;"><i
                          class='bx bx-book'></i>
                  </div>
                  <div class="menu-title" style="font-size: 0.875rem; font-weight: 500;">Bookings Management</div>
              </a>
              <ul style="padding-left: 0;">
                  @can('view bookings')
                      <li> <a href="{{ route('admin.bookings.index') }}"
                              style="padding: 0.5rem 1rem 0.5rem 2.5rem; font-size: 0.8rem;"><i class='bx bx-radio-circle'
                                  style="font-size: 0.7rem;"></i>All Bookings</a></li>
                  @endcan
                  @can('create bookings')
                      <li> <a href="{{ route('admin.bookings.console') }}"
                              style="padding: 0.5rem 1rem 0.5rem 2.5rem; font-size: 0.8rem;"><i class='bx bx-radio-circle'
                                  style="font-size: 0.7rem;"></i>Live Booking Console</a></li>
                  @endcan
                  @can('view bookings')
                      <li> <a href="{{ route('admin.terminal-reports.index') }}"
                              style="padding: 0.5rem 1rem 0.5rem 2.5rem; font-size: 0.8rem;"><i class='bx bx-radio-circle'
                                  style="font-size: 0.7rem;"></i> Terminal Reports</a></li>
                  @endcan
                  {{-- @can('view bookings')
                      <li> <a href="{{ route('admin.bus-assignments.index') }}"
                              style="padding: 0.5rem 1rem 0.5rem 2.5rem; font-size: 0.8rem;"><i class='bx bx-radio-circle'
                                  style="font-size: 0.7rem;"></i> Bus Assignments (Segments)</a></li>
                  @endcan --}}
              </ul>
          </li>




          @can('view discounts')
              <li>
                  <a href="javascript:;" class="has-arrow" style="padding: 0.75rem 1rem; font-size: 0.875rem;">
                      <div class="parent-icon" style="width: 20px; height: 20px; font-size: 1rem;"><i
                              class='bx bx-money'></i>
                      </div>
                      <div class="menu-title" style="font-size: 0.875rem; font-weight: 500;">Discount Management</div>
                  </a>
                  <ul style="padding-left: 0;">
                      @can('view discounts')
                          <li> <a href="{{ route('admin.discounts.index') }}"
                                  style="padding: 0.5rem 1rem 0.5rem 2.5rem; font-size: 0.8rem;"><i class='bx bx-radio-circle'
                                      style="font-size: 0.7rem;"></i>All Discounts</a></li>
                      @endcan
                      @can('create discounts')
                          <li> <a href="{{ route('admin.discounts.create') }}"
                                  style="padding: 0.5rem 1rem 0.5rem 2.5rem; font-size: 0.8rem;"><i class='bx bx-radio-circle'
                                      style="font-size: 0.7rem;"></i>Create Discount</a></li>
                      @endcan
                  </ul>
              </li>
          @endcan


          @canany(['view banners', 'view announcements', 'view general settings'])
              <li class="menu-label"
                  style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; padding: 0.5rem 1rem; color: #6c757d;">
                  Content Management</li>
          @endcanany

          @can('view banners')
              <li>
                  <a href="javascript:;" class="has-arrow" style="padding: 0.75rem 1rem; font-size: 0.875rem;">
                      <div class="parent-icon" style="width: 20px; height: 20px; font-size: 1rem;"><i
                              class='bx bx-image'></i>
                      </div>
                      <div class="menu-title" style="font-size: 0.875rem; font-weight: 500;">Banner Management</div>
                  </a>
                  <ul style="padding-left: 0;">
                      <li> <a href="{{ route('admin.banners.index') }}"
                              style="padding: 0.5rem 1rem 0.5rem 2.5rem; font-size: 0.8rem;"><i class='bx bx-radio-circle'
                                  style="font-size: 0.7rem;"></i>All Banners</a>
                      </li>
                      @can('create banners')
                          <li> <a href="{{ route('admin.banners.create') }}"
                                  style="padding: 0.5rem 1rem 0.5rem 2.5rem; font-size: 0.8rem;"><i class='bx bx-radio-circle'
                                      style="font-size: 0.7rem;"></i>Add New Banner</a></li>
                      @endcan
                  </ul>
              </li>
          @endcan

          @can('view announcements')
              <li>
                  <a href="javascript:;" class="has-arrow" style="padding: 0.75rem 1rem; font-size: 0.875rem;">
                      <div class="parent-icon" style="width: 20px; height: 20px; font-size: 1rem;"><i
                              class='bx bx-calendar-check'></i>
                      </div>
                      <div class="menu-title" style="font-size: 0.875rem; font-weight: 500;">Announcement Management</div>
                  </a>
                  <ul style="padding-left: 0;">
                      <li> <a href="{{ route('admin.announcements.index') }}"
                              style="padding: 0.5rem 1rem 0.5rem 2.5rem; font-size: 0.8rem;"><i class='bx bx-radio-circle'
                                  style="font-size: 0.7rem;"></i>All Announcements</a>
                      </li>
                      @can('create announcements')
                          <li> <a href="{{ route('admin.announcements.create') }}"
                                  style="padding: 0.5rem 1rem 0.5rem 2.5rem; font-size: 0.8rem;"><i class='bx bx-radio-circle'
                                      style="font-size: 0.7rem;"></i>Create Announcement</a></li>
                      @endcan
                  </ul>
              </li>
          @endcan

          @can('view general settings')
              <li>
                  <a href="{{ route('admin.general-settings.index') }}"
                      style="padding: 0.75rem 1rem; font-size: 0.875rem;">
                      <div class="parent-icon" style="width: 20px; height: 20px; font-size: 1rem;"><i
                              class='bx bx-cog'></i>
                      </div>
                      <div class="menu-title" style="font-size: 0.875rem; font-weight: 500;">General Settings</div>
                  </a>
              </li>
          @endcan

          @can('edit general settings')
              <li>
                  <a href="{{ route('admin.advance-booking.index') }}"
                      style="padding: 0.75rem 1rem; font-size: 0.875rem;">
                      <div class="parent-icon" style="width: 20px; height: 20px; font-size: 1rem;"><i
                              class='bx bx-calendar-check'></i>
                      </div>
                      <div class="menu-title" style="font-size: 0.875rem; font-weight: 500;">Advance Booking</div>
                  </a>
              </li>
          @endcan

          @can('view enquiries')
              <li class="menu-label"
                  style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; padding: 0.5rem 1rem; color: #6c757d;">
                  Customer Support</li>
              <li>
                  <a href="{{ route('admin.enquiries.index') }}" style="padding: 0.75rem 1rem; font-size: 0.875rem;">
                      <div class="parent-icon" style="width: 20px; height: 20px; font-size: 1rem;"><i
                              class='bx bx-message-dots'></i>
                      </div>
                      <div class="menu-title" style="font-size: 0.875rem; font-weight: 500;">Customer Enquiries</div>
                  </a>
              </li>
          @endcan
      </ul>
      <!--end navigation-->
  </div>
  <!--end sidebar wrapper -->
