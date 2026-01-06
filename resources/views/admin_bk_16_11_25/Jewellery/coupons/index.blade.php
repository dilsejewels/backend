@extends('admin.layouts.master')

@section('main_section')
  <meta name="csrf-token" content="{{ csrf_token() }}">
  
  <style>
    .dt-thumbnail {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 4px;
    }

    table.dataTable td.dt-control:before {
        content: '\f0fe';
        font-family: FontAwesome;
        color: #317cb1;
        margin-right: 10px;
        cursor: pointer;
    }
    
    table.dataTable td.dt-control:before {
        height: 1em;
        width: 1em;
        margin-top: -9px;
        display: inline-block;
        color: white;
        border: .15em solid white;
        border-radius: 1em;
        box-shadow: 0 0 .2em #444;
        box-sizing: content-box;
        text-align: center;
        text-indent: 0 !important;
        font-family: "Courier New", Courier, monospace;
        line-height: 1em;
        content: "+";
        background-color: #337ab7;
    }

    .form-switch .form-check-input {
        width: 3em;
        height: 1.5em;
        cursor: pointer;
    }

    .max-discount-field {
        display: none;
    }
    
    .bg-success-light {
        background-color: rgba(40, 167, 69, 0.2) !important;
    }
    
    .bg-danger-light {
        background-color: rgba(220, 53, 69, 0.2) !important;
    }
    
    .bg-warning-light {
        background-color: rgba(255, 193, 7, 0.2) !important;
    }
    
    .badge-fixed {
        background-color: #6f42c1;
    }
    
    .badge-percent {
        background-color: #20c997;
    }
    
    .coupon-code {
        font-family: monospace;
        font-size: 1.1em;
        font-weight: bold;
        color: #d63384;
    }
    
    .table-responsive {
        min-height: 400px;
    }
  </style>
    
  <div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
      <div class="card-header d-flex justify-content-between">
        <h4 class="mb-0">Coupon Management</h4>
        <button class="btn btn-primary" id="createCouponBtn"><i class="fas fa-plus me-2"></i>Add New Coupon</button>
      </div>
      <div class="card-body table-responsive text-nowrap">
        <table id="couponsTable" class="table table-hover">
          <thead class="bg-light">
            <tr>
              <th></th>
              <th>ID</th>
              <th>Code</th>
              <th>Type</th>
              <th>Value</th>
              <th>Min. Cart</th>
              <th>Valid Until</th>
              <th>Usage</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($coupons as $coupon)
            <tr>
              <td class="dt-control"></td>
              <td>{{ $coupon->id }}</td>
              <td><span class="coupon-code">{{ $coupon->code }}</span></td>
              <td>
                <span class="badge {{ $coupon->type === 'fixed' ? 'badge-fixed' : 'badge-percent' }}">
                  {{ ucfirst($coupon->type) }}
                </span>
              </td>
              <td>{{ $coupon->type === 'fixed' ? '$' : '' }}{{ $coupon->value }}{{ $coupon->type === 'percent' ? '%' : '' }}</td>
              <td>{{ $coupon->min_cart_value ? '$' . $coupon->min_cart_value : '-' }}</td>
              <td>{{ \Carbon\Carbon::parse($coupon->valid_until)->format('d M Y') }}</td>
              <td>{{ $coupon->used_count }} / {{ $coupon->usage_limit }}</td>
              <td>
                <div class="form-check form-switch">
                  <input type="checkbox" class="form-check-input status-toggle" 
                    data-id="{{ $coupon->id }}" {{ $coupon->is_active ? 'checked' : '' }}>
                </div>
              </td>
              <td>
                <button class="btn btn-sm btn-info editCouponBtn" data-id="{{ $coupon->id }}">
                    
                  <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger deleteCouponBtn" data-id="{{ $coupon->id }}">
                  <i class="fas fa-trash"></i>
                </button>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Modal Form -->
  <div class="modal fade" id="couponModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <form id="couponForm" class="modal-content">
        @csrf
        <input type="hidden" name="id" id="couponId">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTitle">Coupon Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Coupon Code <span class="text-danger">*</span></label>
                <input type="text" name="code" id="code" class="form-control" placeholder="e.g. SUMMER25">
                <small class="text-danger error-code"></small>
              </div>

              <div class="mb-3">
                <label class="form-label">Discount Type <span class="text-danger">*</span></label>
                <select class="form-control" id="type" name="type">
                  <option value="">Select Type</option>
                  <option value="fixed">Fixed Amount</option>
                  <option value="percent">Percentage</option>
                </select>
                <small class="text-danger error-type"></small>
              </div>

              <div class="mb-3">
                <label class="form-label">Discount Value <span class="text-danger">*</span></label>
                <input type="number" step="0.01" min="0" name="value" id="value" class="form-control" placeholder="e.g. 50">
                <small class="text-danger error-value"></small>
              </div>

              <div class="mb-3">
                <label class="form-label">Minimum Cart Value</label>
                <input type="number" step="0.01" min="0" name="min_cart_value" id="min_cart_value" class="form-control" placeholder="e.g. 500">
                <small class="text-danger error-min_cart_value"></small>
              </div>
            </div>

            <div class="col-md-6">
              <div class="mb-3 max-discount-field">
                <label class="form-label">Maximum Discount (For Percentage)</label>
                <input type="number" step="0.01" min="0" name="max_discount" id="max_discount" class="form-control" placeholder="e.g. 1000">
                <small class="text-danger error-max_discount"></small>
              </div>

              <div class="mb-3">
                <label class="form-label">Valid From <span class="text-danger">*</span></label>
                <input type="date" name="valid_from" id="valid_from" class="form-control">
                <small class="text-danger error-valid_from"></small>
              </div>

              <div class="mb-3">
                <label class="form-label">Valid Until <span class="text-danger">*</span></label>
                <input type="date" name="valid_until" id="valid_until" class="form-control">
                <small class="text-danger error-valid_until"></small>
              </div>

              <div class="mb-3">
                <label class="form-label">Usage Limit <span class="text-danger">*</span></label>
                <input type="number" min="1" name="usage_limit" id="usage_limit" class="form-control" value="1">
                <small class="text-danger error-usage_limit"></small>
              </div>

              <div class="mb-3 form-check form-switch">
                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                <label class="form-check-label" for="is_active">Active Coupon</label>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    $(document).ready(function () {
      // CSRF Token setup for AJAX requests
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });

      // Initialize DataTable
      const couponsTable = $('#couponsTable').DataTable({
        order: [[1, 'desc']],
        createdRow: function(row, data, dataIndex) {
          const isActive = $(row).find('.status-toggle').is(':checked');
          if (!isActive) {
            $(row).addClass('bg-danger-light');
          }
          
          // Check if coupon is expired
          const validUntilText = $(row).find('td:eq(6)').text();
          if (validUntilText) {
            const validUntil = new Date(validUntilText);
            const today = new Date();
            if (validUntil < today) {
              $(row).addClass('bg-warning-light');
            }
          }
        }
      });

      // Format function for child rows
      function format(rowData) {
        const tr = $(rowData);
        const id = tr.find('td:eq(1)').text();
        const code = tr.find('td:eq(2)').text();
        const type = tr.find('td:eq(3)').text().trim();
        const value = tr.find('td:eq(4)').text();
        const minCart = tr.find('td:eq(5)').text();
        const validUntil = tr.find('td:eq(6)').text();
        const usage = tr.find('td:eq(7)').text();
        const status = tr.find('.status-toggle').is(':checked') ? 'Active' : 'Inactive';
        
        return `
          <div class="p-3 bg-light rounded">
            <div class="row">
              <div class="col-md-6">
                <strong>Code:</strong> ${code}<br>
                <strong>Type:</strong> ${type}<br>
                <strong>Value:</strong> ${value}<br>
                <strong>Min Cart Value:</strong> ${minCart}<br>
              </div>
              <div class="col-md-6">
                <strong>Valid Until:</strong> ${validUntil}<br>
                <strong>Usage:</strong> ${usage}<br>
                <strong>Status:</strong> ${status}<br>
                <strong>ID:</strong> ${id}<br>
              </div>
            </div>
          </div>
        `;
      }

      // Add event listener for opening and closing details
      $('#couponsTable tbody').on('click', 'td.dt-control', function () {
        const tr = $(this).closest('tr');
        const row = couponsTable.row(tr);

        if (row.child.isShown()) {
          row.child.hide();
          tr.removeClass('shown');
        } else {
          row.child(format(tr)).show();
          tr.addClass('shown');
        }
      });

      // Show/hide max discount field based on type selection
      $('#type').change(function() {
        if ($(this).val() === 'percent') {
          $('.max-discount-field').show();
        } else {
          $('.max-discount-field').hide();
        }
      });

      // Create new coupon
      $('#createCouponBtn').click(() => {
        $('#couponForm')[0].reset();
        $('#couponId').val('');
        $('.max-discount-field').hide();
        $('.text-danger').text('');
        
        // Set default dates
        const today = new Date().toISOString().split('T')[0];
        const nextMonth = new Date();
        nextMonth.setMonth(nextMonth.getMonth() + 1);
        const nextMonthFormatted = nextMonth.toISOString().split('T')[0];
        
        $('#valid_from').val(today);
        $('#valid_until').val(nextMonthFormatted);
        
        $('#modalTitle').text('Create New Coupon');
        $('#couponModal').modal('show');
      });

      // Edit coupon
// Edit coupon
$(document).on('click', '.editCouponBtn', function () {
    const id = $(this).data('id');

    let url = "{{ route('admin.coupons.edit', ':id') }}"; // :id placeholder
    url = url.replace(':id', id);

    $.ajax({
        url: url,
        type: 'GET',
        success: function(response) {
            const coupon = response.coupon;

            $('#couponId').val(coupon.id);
            $('#code').val(coupon.code);
            $('#type').val(coupon.type);
            $('#value').val(coupon.value);
            $('#min_cart_value').val(coupon.min_cart_value);
            $('#max_discount').val(coupon.max_discount);
            $('#valid_from').val(coupon.valid_from.split(' ')[0]);
            $('#valid_until').val(coupon.valid_until.split(' ')[0]);
            $('#usage_limit').val(coupon.usage_limit);
            $('#is_active').prop('checked', coupon.is_active);

            if (coupon.type === 'percent') {
                $('.max-discount-field').show();
            } else {
                $('.max-discount-field').hide();
            }

            $('.text-danger').text('');
            $('#modalTitle').text('Edit Coupon');
            $('#couponModal').modal('show');
        },
        error: function() {
            toastr.error('Failed to fetch coupon data');
        }
    });
});


      // Save coupon form
      $('#couponForm').submit(function (e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        const id = $('#couponId').val();
        const url = id ? "{{ route('admin.coupons.update', '') }}/" + id : "{{ route('admin.coupons.store') }}";
        const method = id ? 'PUT' : 'POST';
        
        // Clear previous errors
        $('.text-danger').text('');
        
        $.ajax({
          url: url,
          type: method,
          data: formData,
          success: function(response) {
            $('#couponModal').modal('hide');
            toastr.success(response.success);
            
            // Reload the page to see changes
            setTimeout(() => {
              window.location.reload();
            }, 1000);
          },
          error: function(xhr) {
            if (xhr.status === 422) {
              const errors = xhr.responseJSON.errors;
              for (const field in errors) {
                $(`.error-${field}`).text(errors[field][0]);
              }
            } else {
              toastr.error('An error occurred. Please try again.');
            }
          }
        });
      });

      // Delete coupon
      $(document).on('click', '.deleteCouponBtn', function () {
        const id = $(this).data('id');
        
        if (confirm('Are you sure you want to delete this coupon?')) {
          $.ajax({
            url: "{{ route('admin.coupons.destroy', '') }}/" + id,
            type: 'DELETE',
            success: function(response) {
              toastr.success(response.success);
              
              // Reload the page to see changes
              setTimeout(() => {
                window.location.reload();
              }, 1000);
            },
            error: function() {
              toastr.error('Failed to delete coupon');
            }
          });
        }
      });

      // Toggle coupon status
      $(document).on('change', '.status-toggle', function () {
        const id = $(this).data('id');
        const status = this.checked ? 1 : 0;
        
        $.ajax({
          url: "{{ route('admin.coupons.status', '') }}/" + id,
          type: 'POST',
          data: {
            status: status
          },
          success: function(response) {
            toastr.success(response.message);
          },
          error: function() {
            toastr.error('Failed to update coupon status');
            // Revert the toggle
            $(this).prop('checked', !status);
          }
        });
      });
    });
  </script>
@endsection