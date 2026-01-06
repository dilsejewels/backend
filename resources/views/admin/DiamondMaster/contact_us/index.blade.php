@extends('admin.layouts.master')

@section('main_section')
<style>
  .contact-card {
    border-left: 4px solid #007bff;
  }
  .stats-card {
    background: white;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
  }
  .question-text {
    max-height: 60px;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
  }
  .badge-topic {
    background: #6c757d;
    color: white;
  }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
  <!-- Statistics Cards -->
  <div class="row mb-4">
    <div class="col-md-3">
      <div class="stats-card">
        <div class="d-flex justify-content-between">
          <div>
            <h5 class="text-muted">Total Queries</h5>
            <h3 id="totalContacts">0</h3>
          </div>
          <div class="align-self-center">
            <i class="fa fa-envelope fa-2x text-primary"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stats-card">
        <div class="d-flex justify-content-between">
          <div>
            <h5 class="text-muted">Today</h5>
            <h3 id="todayContacts">0</h3>
          </div>
          <div class="align-self-center">
            <i class="fa fa-calendar-day fa-2x text-success"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stats-card">
        <div class="d-flex justify-content-between">
          <div>
            <h5 class="text-muted">This Week</h5>
            <h3 id="weekContacts">0</h3>
          </div>
          <div class="align-self-center">
            <i class="fa fa-calendar-week fa-2x text-info"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stats-card">
        <div class="d-flex justify-content-between">
          <div>
            <h5 class="text-muted">This Month</h5>
            <h3 id="monthContacts">0</h3>
          </div>
          <div class="align-self-center">
            <i class="fa fa-calendar-alt fa-2x text-warning"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4 class="mb-0">Contact Us Queries</h4>
      <div>
        <button class="btn btn-primary" onclick="refreshData()">
          <i class="fa fa-refresh me-1"></i> Refresh
        </button>
      </div>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover" id="contactsTable">
          <thead class="bg-light">
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Topic</th>
              <th>Question</th>
              <th>Submitted At</th>
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
<div class="modal fade" id="viewContactModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Contact Query Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="contactDetails">
        <!-- Details will be loaded here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-danger" id="deleteFromModalBtn">
          <i class="fa fa-trash me-1"></i> Delete
        </button>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
  let currentContactId = null;

  // Initialize DataTable
  const table = $('#contactsTable').DataTable({
    processing: true,
    serverSide: false,
    ajax: {
      url: "{{ route('admin.contactus.fetch') }}",
      dataSrc: 'data'
    },
    columns: [
      { data: 'id' },
      {
        data: 'name',
        render: function(data, type, row) {
          return `<strong>${data}</strong>`;
        }
      },
      {
        data: 'email',
        render: function(data) {
          return `<a href="mailto:${data}">${data}</a>`;
        }
      },
      {
        data: 'phone',
        render: function(data) {
          return data ? `<a href="tel:${data}">${data}</a>` : '<span class="text-muted">N/A</span>';
        }
      },
      {
        data: 'topic',
        render: function(data) {
          return `<span class="badge badge-topic">${data}</span>`;
        }
      },
      {
        data: 'question',
        render: function(data) {
          const shortQuestion = data.length > 100 ? data.substring(0, 100) + '...' : data;
          return `<div class="question-text" title="${data}">${shortQuestion}</div>`;
        }
      },
      {
        data: 'created_at',
        render: function(data) {
          return new Date(data).toLocaleString();
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
    order: [[0, 'desc']],
    language: {
      emptyTable: "No contact queries found",
      zeroRecords: "No matching contact queries found"
    }
  });

  // Load statistics
  loadStatistics();

  // View contact details
  $(document).on('click', '.view-btn', function() {
    const contactId = $(this).data('id');
    viewContactDetails(contactId);
  });

  // Delete contact
  $(document).on('click', '.delete-btn', function() {
    const contactId = $(this).data('id');
    deleteContact(contactId);
  });

  // Delete from modal
  $('#deleteFromModalBtn').click(function() {
    if (currentContactId) {
      $('#viewContactModal').modal('hide');
      deleteContact(currentContactId);
    }
  });
});

function loadStatistics() {
  $.ajax({
    url: "{{ route('admin.contactus.fetch') }}",
    type: 'GET',
    success: function(response) {
      if (response.success) {
        const contacts = response.data;
        const today = new Date().toISOString().split('T')[0];
        
        const total = contacts.length;
        const todayCount = contacts.filter(c => c.created_at.split('T')[0] === today).length;
        
        // Calculate week and month counts
        const now = new Date();
        const weekStart = new Date(now.setDate(now.getDate() - now.getDay()));
        const monthStart = new Date(now.getFullYear(), now.getMonth(), 1);
        
        const weekCount = contacts.filter(c => new Date(c.created_at) >= weekStart).length;
        const monthCount = contacts.filter(c => new Date(c.created_at) >= monthStart).length;

        $('#totalContacts').text(total);
        $('#todayContacts').text(todayCount);
        $('#weekContacts').text(weekCount);
        $('#monthContacts').text(monthCount);
      }
    }
  });
}

function viewContactDetails(id) {
  currentContactId = id;
  
  $.ajax({
    url: `{{ url('admin/contact-us/show') }}/${id}`,
    type: 'GET',
    success: function(response) {
      if (response.success) {
        const contact = response.data;
        const modal = $('#viewContactModal');
        const details = $('#contactDetails');
        
        const detailsHtml = `
          <div class="row">
            <div class="col-md-6">
              <h6>Personal Information</h6>
              <p><strong>Name:</strong> ${contact.name}</p>
              <p><strong>Email:</strong> <a href="mailto:${contact.email}">${contact.email}</a></p>
              <p><strong>Phone:</strong> ${contact.phone ? `<a href="tel:${contact.phone}">${contact.phone}</a>` : 'N/A'}</p>
            </div>
            <div class="col-md-6">
              <h6>Query Information</h6>
              <p><strong>Topic:</strong> <span class="badge badge-topic">${contact.topic}</span></p>
              <p><strong>Submitted:</strong> ${new Date(contact.created_at).toLocaleString()}</p>
              <p><strong>Last Updated:</strong> ${new Date(contact.updated_at).toLocaleString()}</p>
            </div>
          </div>
          <div class="row mt-3">
            <div class="col-12">
              <h6>Question / Message</h6>
              <div class="border p-3 bg-light rounded">
                <p class="mb-0" style="white-space: pre-wrap;">${contact.question}</p>
              </div>
            </div>
          </div>
        `;
        
        details.html(detailsHtml);
        modal.modal('show');
      }
    },
    error: function() {
      toastr.error('Error loading contact details');
    }
  });
}

function deleteContact(id) {
  if (!confirm('Are you sure you want to delete this contact query? This action cannot be undone.')) {
    return;
  }

  $.ajax({
    url: `{{ url('admin/contact-us/delete') }}/${id}`,
    type: 'DELETE',
    data: {
      _token: '{{ csrf_token() }}'
    },
    success: function(response) {
      if (response.success) {
        toastr.success('Contact query deleted successfully');
        $('#contactsTable').DataTable().ajax.reload();
        loadStatistics(); // Refresh stats
        currentContactId = null;
      }
    },
    error: function() {
      toastr.error('Error deleting contact query');
    }
  });
}

function refreshData() {
  $('#contactsTable').DataTable().ajax.reload();
  loadStatistics();
  toastr.success('Data refreshed successfully');
}

// Auto refresh every 30 seconds
setInterval(function() {
  refreshData();
}, 30000);
</script>
@endsection