@extends('admin.layouts.master')

@section('main_section')
<!-- DataTable CSS CDN -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">

<style>
  .contact-card {
    border-left: 4px solid #007bff;
  }
  .stats-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    border-left: 4px solid;
  }
  .stats-card.total { border-left-color: #007bff; }
  .stats-card.pending { border-left-color: #ffc107; }
  .stats-card.week { border-left-color: #17a2b8; }
  .stats-card.month { border-left-color: #6f42c1; }
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
  .badge-pending {
    background: #dc3545;
    color: white;
  }
  .badge-responded {
    background: #28a745;
    color: white;
  }
  .response-item {
    border-left: 3px solid #007bff;
    padding-left: 15px;
    margin-bottom: 15px;
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
  }
  .response-form {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 25px;
    border-radius: 10px;
    color: white;
  }
  .action-buttons .btn {
    margin: 2px;
  }
  .customer-details {
    background: #e7f3ff;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
  }
  .dataTables_wrapper {
    position: relative;
  }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
  <!-- Statistics Cards -->
  <div class="row mb-4">
    <div class="col-xl-3 col-md-6">
      <div class="stats-card total">
        <div class="d-flex justify-content-between">
          <div>
            <h5 class="text-muted">Total Queries</h5>
            <h2 id="totalContacts" class="text-primary">0</h2>
          </div>
          <div class="align-self-center">
            <i class="fa fa-envelope fa-2x text-primary"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-md-6">
      <div class="stats-card pending">
        <div class="d-flex justify-content-between">
          <div>
            <h5 class="text-muted">Pending Responses</h5>
            <h2 id="pendingContacts" class="text-warning">0</h2>
          </div>
          <div class="align-self-center">
            <i class="fa fa-clock fa-2x text-warning"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-md-6">
      <div class="stats-card week">
        <div class="d-flex justify-content-between">
          <div>
            <h5 class="text-muted">This Week</h5>
            <h2 id="weekContacts" class="text-info">0</h2>
          </div>
          <div class="align-self-center">
            <i class="fa fa-calendar-week fa-2x text-info"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-md-6">
      <div class="stats-card month">
        <div class="d-flex justify-content-between">
          <div>
            <h5 class="text-muted">This Month</h5>
            <h2 id="monthContacts" class="text-purple">0</h2>
          </div>
          <div class="align-self-center">
            <i class="fa fa-calendar-alt fa-2x text-purple"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4 class="mb-0">📩 Contact Us Queries Management</h4>
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
              <th>Status</th>
              <th>Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- View Details & Response Modal -->
<div class="modal fade" id="viewContactModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">
          <i class="fa fa-reply me-2"></i>Contact Query Details & Response
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="contactDetails">
        <!-- Details will be loaded here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="fa fa-times me-1"></i> Close
        </button>
        <button type="button" class="btn btn-danger" id="deleteFromModalBtn">
          <i class="fa fa-trash me-1"></i> Delete Query
        </button>
      </div>
    </div>
  </div>
</div>

<!-- DataTable JS CDN -->
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
// Wait for jQuery and DataTable to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Check if DataTable is available
    if (typeof $.fn.DataTable === 'undefined') {
        console.error('DataTable library is not loaded. Please check the CDN links.');
        return;
    }

    initializeDataTable();
});

let dataTable = null;
let currentContactId = null;

function initializeDataTable() {
    // Destroy existing DataTable instance if it exists
    if (dataTable !== null) {
        dataTable.destroy();
        $('#contactsTable').empty();
    }

    // Initialize DataTable
    dataTable = $('#contactsTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: "{{ route('admin.contactus.fetch') }}",
            dataSrc: 'data',
            error: function(xhr, error, thrown) {
                console.error('Error loading data:', error);
                toastr.error('Error loading contact data');
            }
        },
        columns: [
            { 
                data: 'id',
                className: 'fw-bold'
            },
            {
                data: 'name',
                render: function(data, type, row) {
                    return `<div class="d-flex align-items-center">
                                <strong>${data}</strong>
                            </div>`;
                }
            },
            {
                data: 'email',
                render: function(data) {
                    return `<a href="mailto:${data}" class="text-primary">${data}</a>`;
                }
            },
            {
                data: 'phone',
                render: function(data) {
                    return data ? 
                        `<a href="tel:${data}" class="text-success">${data}</a>` : 
                        '<span class="text-muted">N/A</span>';
                }
            },
            {
                data: 'topic',
                render: function(data) {
                    return `<span class="badge badge-topic px-2 py-1">${data}</span>`;
                }
            },
            {
                data: 'question',
                render: function(data) {
                    const shortQuestion = data.length > 80 ? data.substring(0, 80) + '...' : data;
                    return `<div class="question-text" title="${data.replace(/"/g, '&quot;')}">${shortQuestion}</div>`;
                }
            },
            {
                data: 'latest_response',
                render: function(data, type, row) {
                    if (data) {
                        return `<span class="badge badge-responded px-2 py-1">
                                    <i class="fa fa-check-circle me-1"></i>Responded
                                </span>`;
                    } else {
                        return `<span class="badge badge-pending px-2 py-1">
                                    <i class="fa fa-clock me-1"></i>Pending
                                </span>`;
                    }
                }
            },
            {
                data: 'created_at',
                render: function(data) {
                    const date = new Date(data);
                    return `<div>
                                <small class="text-muted">${date.toLocaleDateString()}</small>
                                <br>
                                <small class="text-muted">${date.toLocaleTimeString()}</small>
                            </div>`;
                }
            },
            {
                data: null,
                render: function(data) {
                    return `
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-success view-btn" data-id="${data.id}" title="Reply to Customer">
                                <i class="fa fa-reply"></i>
                            </button>
                            <button class="btn btn-sm btn-info view-details-btn" data-id="${data.id}" title="View Details">
                                <i class="fa fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-danger delete-btn" data-id="${data.id}" title="Delete">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    `;
                },
                orderable: false,
                searchable: false
            }
        ],
        order: [[0, 'asc']],
        language: {
            emptyTable: "No contact queries found",
            zeroRecords: "No matching contact queries found",
            loadingRecords: "Loading...",
            processing: "Processing..."
        },
        createdRow: function(row, data, dataIndex) {
            if (data.latest_response == null) {
                $(row).addClass('table-warning');
            }
        },
        initComplete: function() {
            console.log('DataTable initialized successfully');
        }
    });

    // Load statistics
    loadStatistics();

    // Event handlers
    $(document).on('click', '.view-btn', function() {
        const contactId = $(this).data('id');
        viewContactDetails(contactId, true);
    });

    $(document).on('click', '.view-details-btn', function() {
        const contactId = $(this).data('id');
        viewContactDetails(contactId, false);
    });

    $(document).on('click', '.delete-btn', function() {
        const contactId = $(this).data('id');
        deleteContact(contactId);
    });

    $('#deleteFromModalBtn').click(function() {
        if (currentContactId) {
            $('#viewContactModal').modal('hide');
            deleteContact(currentContactId);
        }
    });
}

function loadStatistics() {
    $.ajax({
        url: "{{ route('admin.contactus.stats') }}",
        type: 'GET',
        success: function(response) {
            if (response.success) {
                $('#totalContacts').text(response.data.total);
                $('#pendingContacts').text(response.data.pending_responses);
                $('#weekContacts').text(response.data.this_week);
                $('#monthContacts').text(response.data.this_month);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading statistics:', error);
        }
    });
}

function viewContactDetails(id, showResponseForm = true) {
    currentContactId = id;
    
    $.ajax({
        url: `{{ url('admin/contact-us/show') }}/${id}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const contact = response.data;
                const modal = $('#viewContactModal');
                const details = $('#contactDetails');
                
                // Build responses history HTML
                let responsesHtml = '';
                if (contact.responses && contact.responses.length > 0) {
                    responsesHtml = `
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5><i class="fa fa-history me-2 text-primary"></i>Response History</h5>
                                <div class="border rounded">
                                    <div class="list-group list-group-flush">
                                        ${contact.responses.map((response, index) => `
                                            <div class="list-group-item response-item">
                                                <div class="d-flex w-100 justify-content-between align-items-start">
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <small class="text-primary">
                                                                <i class="fa fa-user me-1"></i>
                                                                ${response.responder ? response.responder.name : 'Admin'}
                                                            </small>
                                                            <small class="text-muted">
                                                                <i class="fa fa-clock me-1"></i>
                                                                ${new Date(response.created_at).toLocaleString()}
                                                            </small>
                                                        </div>
                                                        <p class="mb-1" style="white-space: pre-wrap; background: white; padding: 10px; border-radius: 5px;">${response.message}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    responsesHtml = `
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="alert alert-warning d-flex align-items-center">
                                    <i class="fa fa-exclamation-triangle me-2 fa-lg"></i>
                                    <div>
                                        <strong>No responses sent yet.</strong><br>
                                        <small class="text-muted">This customer is waiting for your response.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }

                // Build response form HTML
                let responseFormHtml = '';
                if (showResponseForm) {
                    responseFormHtml = `
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5><i class="fa fa-paper-plane me-2 text-success"></i>Send Response</h5>
                                <div class="response-form">
                                    <form id="responseForm">
                                        <div class="mb-3">
                                            <label class="form-label text-white"><strong>Response Message *</strong></label>
                                            <textarea class="form-control" id="responseMessage" rows="5" 
                                                        placeholder="Type your detailed response here... This will be sent to ${contact.email}"
                                                        required></textarea>
                                            <div class="form-text text-white">
                                                <i class="fa fa-info-circle me-1"></i>
                                                This response will be emailed to <strong>${contact.email}</strong> and saved in the response history.
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-success btn-lg">
                                                <i class="fa fa-paper-plane me-2"></i> Send Response & Email
                                            </button>
                                            <button type="button" class="btn btn-light btn-lg" onclick="previewResponse()">
                                                <i class="fa fa-eye me-2"></i> Preview
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    `;
                }

                const detailsHtml = `
                    <div class="customer-details">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fa fa-user me-2 text-primary"></i>Customer Information</h6>
                                <p><strong>Name:</strong> ${contact.name}</p>
                                <p><strong>Email:</strong> <a href="mailto:${contact.email}">${contact.email}</a></p>
                                <p><strong>Phone:</strong> ${contact.phone ? `<a href="tel:${contact.phone}">${contact.phone}</a>` : 'N/A'}</p>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fa fa-info-circle me-2 text-primary"></i>Query Information</h6>
                                <p><strong>Topic:</strong> <span class="badge badge-topic">${contact.topic}</span></p>
                                <p><strong>Submitted:</strong> ${new Date(contact.created_at).toLocaleString()}</p>
                                <p><strong>Status:</strong> ${contact.responses && contact.responses.length > 0 ? 
                                    '<span class="badge badge-responded">Responded</span>' : 
                                    '<span class="badge badge-pending">Pending Response</span>'}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <h5><i class="fa fa-question-circle me-2 text-primary"></i>Original Message</h5>
                            <div class="border p-4 bg-light rounded">
                                <p class="mb-0" style="white-space: pre-wrap; font-size: 16px; line-height: 1.6;">${contact.question}</p>
                            </div>
                        </div>
                    </div>

                    ${responsesHtml}
                    ${responseFormHtml}
                `;
                
                details.html(detailsHtml);
                modal.modal('show');
                
                // Auto-focus on response textarea
                if (showResponseForm) {
                    setTimeout(() => {
                        $('#responseMessage').focus();
                    }, 500);
                }
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading contact details:', error);
            toastr.error('Error loading contact details');
        }
    });
}

// Response form submission
$(document).on('submit', '#responseForm', function(e) {
    e.preventDefault();
    
    const message = $('#responseMessage').val().trim();
    if (!message) {
        toastr.error('Please enter a response message');
        return;
    }

    if (message.length < 10) {
        toastr.error('Response message should be at least 10 characters long');
        return;
    }

    if (message.length > 2000) {
        toastr.error('Response message should not exceed 2000 characters');
        return;
    }

    // Show loading state
    const submitBtn = $(this).find('button[type="submit"]');
    const originalText = submitBtn.html();
    submitBtn.html('<i class="fa fa-spinner fa-spin me-2"></i>Sending...').prop('disabled', true);

    $.ajax({
        url: `{{ url('admin/contact-us/respond') }}/${currentContactId}`,
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            message: message
        },
        success: function(response) {
            submitBtn.html(originalText).prop('disabled', false);
            if (response.success) {
                toastr.success('🎉 Response sent successfully and email notification delivered!');
                $('#responseMessage').val('');
                // Reload the table and stats
                refreshData();
                // Reload the modal to show new response
                viewContactDetails(currentContactId, true);
            } else {
                toastr.error(response.message);
            }
        },
        error: function(xhr, status, error) {
            submitBtn.html(originalText).prop('disabled', false);
            if (xhr.responseJSON && xhr.responseJSON.message) {
                toastr.error('❌ ' + xhr.responseJSON.message);
            } else {
                toastr.error('❌ Error sending response. Please try again.');
            }
        }
    });
});

function previewResponse() {
    const message = $('#responseMessage').val().trim();
    if (!message) {
        toastr.error('Please enter a message to preview');
        return;
    }

    const previewWindow = window.open('', 'response_preview', 'width=800,height=600');
    previewWindow.document.write(`
        <html>
            <head>
                <title>Response Preview</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; }
                    .preview-box { border: 2px solid #007bff; padding: 20px; border-radius: 10px; background: #f8f9fa; }
                </style>
            </head>
            <body>
                <h2>Response Preview</h2>
                <div class="preview-box">
                    <p><strong>Your Response Message:</strong></p>
                    <div style="background: white; padding: 15px; border-radius: 5px; border-left: 4px solid #28a745;">
                        <p style="white-space: pre-wrap; margin: 0;">${message}</p>
                    </div>
                    <p style="margin-top: 15px; color: #666;">
                        <small>This is how your response will appear to the customer.</small>
                    </p>
                </div>
                <button onclick="window.print()" style="margin-top: 20px; padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
                    Print Preview
                </button>
                <button onclick="window.close()" style="margin-top: 20px; padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer;">
                    Close
                </button>
            </body>
        </html>
    `);
}

function deleteContact(id) {
    if (!confirm('⚠️ Are you sure you want to delete this contact query and all its responses? This action cannot be undone.')) {
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
                toastr.success('✅ Contact query and responses deleted successfully');
                refreshData();
                $('#viewContactModal').modal('hide');
                currentContactId = null;
            }
        },
        error: function(xhr, status, error) {
            toastr.error('❌ Error deleting contact query');
        }
    });
}

function refreshData() {
    if (dataTable !== null) {
        dataTable.ajax.reload(null, false); // false means don't reset paging
    } else {
        initializeDataTable();
    }
    loadStatistics();
    toastr.info('🔄 Data refreshed successfully');
}

// Auto refresh every 60 seconds
setInterval(function() {
    refreshData();
}, 60000);
</script>
@endsection