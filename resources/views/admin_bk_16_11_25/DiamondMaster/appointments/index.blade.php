@extends('admin.layouts.master')

@section('main_section')
<style>
  .appointment-virtual {
    border-left: 4px solid #007bff;
  }
  .appointment-showroom {
    border-left: 4px solid #28a745;
  }
  .badge-virtual {
    background: #007bff;
    color: white;
  }
  .badge-showroom {
    background: #28a745;
    color: white;
  }
  .stats-card {
    background: white;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
  }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
  <!-- Statistics Cards -->
  <div class="row mb-4">
    <div class="col-md-3">
      <div class="stats-card">
        <div class="d-flex justify-content-between">
          <div>
            <h5 class="text-muted">Total</h5>
            <h3 id="totalAppointments">0</h3>
          </div>
          <div class="align-self-center">
            <i class="fa fa-calendar fa-2x text-primary"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stats-card">
        <div class="d-flex justify-content-between">
          <div>
            <h5 class="text-muted">Virtual</h5>
            <h3 id="virtualAppointments">0</h3>
          </div>
          <div class="align-self-center">
            <i class="fa fa-video fa-2x text-info"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stats-card">
        <div class="d-flex justify-content-between">
          <div>
            <h5 class="text-muted">Showroom</h5>
            <h3 id="showroomAppointments">0</h3>
          </div>
          <div class="align-self-center">
            <i class="fa fa-store fa-2x text-success"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stats-card">
        <div class="d-flex justify-content-between">
          <div>
            <h5 class="text-muted">Today</h5>
            <h3 id="todayAppointments">0</h3>
          </div>
          <div class="align-self-center">
            <i class="fa fa-clock fa-2x text-warning"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4 class="mb-0">Appointment Management</h4>
      <div>
        <button class="btn btn-primary" onclick="refreshData()">
          <i class="fa fa-refresh me-1"></i> Refresh
        </button>
      </div>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover" id="appointmentsTable">
          <thead class="bg-light">
            <tr>
              <th>ID</th>
              <th>Type</th>
              <th>Date & Time</th>
              <th>Time Zone</th>
              <th>Customer</th>
              <th>Contact</th>
              <th>Category</th>
              <th>Booking Time</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- View Details Modal -->
<div class="modal fade" id="viewAppointmentModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Appointment Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="appointmentDetails">
        <!-- Details will be loaded here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
  // Initialize DataTable
  const table = $('#appointmentsTable').DataTable({
    processing: true,
    serverSide: false,
    ajax: {
      url: "{{ route('admin.appointments.fetch') }}",
      dataSrc: 'data'
    },
    columns: [
      { data: 'id' },
      {
        data: 'appointment_type',
        render: function(data) {
          const badgeClass = data === 'virtual' ? 'badge-virtual' : 'badge-showroom';
          const icon = data === 'virtual' ? '💻' : '🏢';
          return `<span class="badge ${badgeClass}">${icon} ${data.charAt(0).toUpperCase() + data.slice(1)}</span>`;
        }
      },
      {
        data: null,
        render: function(data) {
          return `
            <div>
              <strong>${data.appointment_date}</strong><br>
              <small class="text-muted">${data.appointment_time}</small>
            </div>
          `;
        }
      },
      { data: 'time_zone' },
      {
        data: null,
        render: function(data) {
          return `
            <div>
              <strong>${data.name}</strong><br>
              <small class="text-muted">${data.email}</small>
              ${data.guest_email ? `<br><small class="text-info">Guest: ${data.guest_email}</small>` : ''}
            </div>
          `;
        }
      },
      {
        data: 'contact_number',
        render: function(data) {
          return data ? `<a href="tel:${data}">${data}</a>` : 'N/A';
        }
      },
      {
        data: null,
        render: function(data) {
          return data.other_category ? `${data.category} (${data.other_category})` : data.category;
        }
      },
      {
        data: 'today_time',
        render: function(data) {
          return data ? new Date(data).toLocaleString() : 'N/A';
        }
      },
      {
        data: null,
        render: function(data) {
          return `
            <div class="btn-group">
              <button class="btn btn-sm btn-info view-btn" data-id="${data.id}" title="View Details">
                <i class="fa fa-eye"></i>
              </button>
              <button class="btn btn-sm btn-danger delete-btn" data-id="${data.id}" title="Delete">
                <i class="fa fa-trash"></i>
              </button>
            </div>
          `;
        }
      }
    ],
    order: [[0, 'desc']]
  });

  // Load statistics
  loadStatistics();

  // View appointment details
  $(document).on('click', '.view-btn', function() {
    const appointmentId = $(this).data('id');
    viewAppointmentDetails(appointmentId);
  });

  // Delete appointment
  $(document).on('click', '.delete-btn', function() {
    const appointmentId = $(this).data('id');
    deleteAppointment(appointmentId);
  });
});

function loadStatistics() {
  $.ajax({
    url: "{{ route('admin.appointments.fetch') }}",
    type: 'GET',
    success: function(response) {
      if (response.success) {
        const appointments = response.data;
        
        const total = appointments.length;
        const virtual = appointments.filter(a => a.appointment_type === 'virtual').length;
        const showroom = appointments.filter(a => a.appointment_type === 'showroom').length;
        
        const today = new Date().toISOString().split('T')[0];
        const todayCount = appointments.filter(a => a.appointment_date === today).length;

        $('#totalAppointments').text(total);
        $('#virtualAppointments').text(virtual);
        $('#showroomAppointments').text(showroom);
        $('#todayAppointments').text(todayCount);
      }
    }
  });
}

function viewAppointmentDetails(id) {
  $.ajax({
    url: `{{ url('admin/appointments/show') }}/${id}`,
    type: 'GET',
    success: function(response) {
      if (response.success) {
        const appointment = response.data;
        const modal = $('#viewAppointmentModal');
        const details = $('#appointmentDetails');
        
        const detailsHtml = `
          <div class="row">
            <div class="col-md-6">
              <h6>Appointment Information</h6>
              <p><strong>Type:</strong> <span class="badge ${appointment.appointment_type === 'virtual' ? 'badge-virtual' : 'badge-showroom'}">${appointment.appointment_type.charAt(0).toUpperCase() + appointment.appointment_type.slice(1)}</span></p>
              <p><strong>Date:</strong> ${appointment.appointment_date}</p>
              <p><strong>Time:</strong> ${appointment.appointment_time}</p>
              <p><strong>Time Zone:</strong> ${appointment.time_zone || 'N/A'}</p>
              <p><strong>Booking Time:</strong> ${appointment.today_time ? new Date(appointment.today_time).toLocaleString() : 'N/A'}</p>
            </div>
            <div class="col-md-6">
              <h6>Customer Information</h6>
              <p><strong>Name:</strong> ${appointment.name}</p>
              <p><strong>Email:</strong> ${appointment.email}</p>
              <p><strong>Guest Email:</strong> ${appointment.guest_email || 'N/A'}</p>
              <p><strong>Contact:</strong> ${appointment.contact_number || 'N/A'}</p>
            </div>
          </div>
          <div class="row mt-3">
            <div class="col-12">
              <h6>Category Details</h6>
              <p><strong>Category:</strong> ${appointment.category}</p>
              ${appointment.other_category ? `<p><strong>Custom Category:</strong> ${appointment.other_category}</p>` : ''}
            </div>
          </div>
          ${appointment.additional_information ? `
          <div class="row mt-3">
            <div class="col-12">
              <h6>Additional Information</h6>
              <div class="border p-3 bg-light">
                ${appointment.additional_information}
              </div>
            </div>
          </div>
          ` : ''}
        `;
        
        details.html(detailsHtml);
        modal.modal('show');
      }
    },
    error: function() {
      toastr.error('Error loading appointment details');
    }
  });
}

function deleteAppointment(id) {
  if (!confirm('Are you sure you want to delete this appointment? This action cannot be undone.')) {
    return;
  }

  $.ajax({
    url: `{{ url('admin/appointments/delete') }}/${id}`,
    type: 'DELETE',
    data: {
      _token: '{{ csrf_token() }}'
    },
    success: function(response) {
      if (response.success) {
        toastr.success('Appointment deleted successfully');
        $('#appointmentsTable').DataTable().ajax.reload();
        loadStatistics(); // Refresh stats
      }
    },
    error: function() {
      toastr.error('Error deleting appointment');
    }
  });
}

function refreshData() {
  $('#appointmentsTable').DataTable().ajax.reload();
  loadStatistics();
  toastr.success('Data refreshed successfully');
}

// Auto refresh every 30 seconds
setInterval(function() {
  refreshData();
}, 30000);
</script>
@endsection