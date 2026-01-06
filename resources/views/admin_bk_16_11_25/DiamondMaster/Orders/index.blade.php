@extends('admin.layouts.master')

@section('main_section')
<div class="container-xxl flex-grow-1 container-p-y">
  <div id="messageContainer" class="position-fixed top-20 end-20 z-9999" style="display:none;">
    <div class="alert alert-dismissible fade show" role="alert">
      <span id="messageText"></span>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  </div>
  
  <div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4 class="mb-0">Diamond & Jewelry Orders</h4>
      <span class="badge bg-primary">Total Orders: {{ \App\Models\Order::count() }}</span>
    </div>
    <div class="card-body table-responsive">
      <table class="table table-hover" id="orderTable">
        <thead class="bg-light">
          <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Type</th>
            <th>Quantity</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Coupon Code</th>
            <th>Discount</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="orderTableBody"></tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="invoiceModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Order Invoice - <span id="modalOrderId"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0">
        <iframe id="invoiceFrame" width="100%" height="600px" frameborder="0" style="border: none;"></iframe>
      </div>
    </div>
  </div>
</div>

<script>
$(function() {
    // Initialize DataTable with server-side processing
    const dataTable = $('#orderTable').DataTable({
        order: [[8, 'desc']],
        pageLength: 10,
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('orders.fetch') }}",
            type: 'GET'
        },
        columns: [
            { 
                data: 'order_id', 
                name: 'order_id',
                render: function(data) {
                    return `<strong>${data}</strong>`;
                }
            },
            { 
                data: 'user_name', 
                name: 'user_name',
                render: function(data, type, row) {
                    return `${data}<br><small class="text-muted">${row.contact_number}</small>`;
                }
            },
            { 
                data: 'product_type', 
                name: 'product_type',
                render: function(data) {
                    const typeClass = {
                        'diamond': 'warning',
                        'jewelry': 'info',
                        'mixed': 'primary',
                        'combo': 'success'
                    }[data] || 'secondary';
                    
                    return `<span class="badge bg-${typeClass}">${data.charAt(0).toUpperCase() + data.slice(1)}</span>`;
                }
            },
            { 
                data: 'total_quantity', 
                name: 'total_quantity',
                className: 'text-center',
                render: function(data) {
                    return `<span class="badge bg-dark">${data}</span>`;
                }
            },
            { 
                data: 'total_price', 
                name: 'total_price',
                render: function(data, type, row) {
                    const grandTotal = parseFloat(data) + parseFloat(row.shipping_cost || 0) - parseFloat(row.discount || 0);
                    return `$${parseFloat(data).toFixed(2)}<br><small class="text-success">Total: $${grandTotal.toFixed(2)}</small>`;
                }
            },
            {
                data: 'order_status', 
                name: 'order_status',
                render: function(data) {
                    const statusClass = {
                        pending: 'secondary',
                        confirmed: 'primary',
                        shipped: 'info',
                        delivered: 'success',
                        cancelled: 'danger',
                        returned: 'warning'
                    }[data] || 'secondary';
                    
                    const label = data.charAt(0).toUpperCase() + data.slice(1);
                    return `<span class="badge bg-${statusClass}">${label}</span>`;
                }
            },
            { 
                data: 'coupon_code', 
                name: 'coupon_code',
                render: function(data) {
                    return data ? `<span class="badge bg-light text-dark">${data}</span>` : 'N/A';
                }
            },
            { 
                data: 'coupon_discount', 
                name: 'coupon_discount',
                render: function(data, type, row) {
                    const totalDiscount = parseFloat(data || 0) + parseFloat(row.discount || 0);
                    return totalDiscount > 0 ? 
                        `<span class="text-danger">-$${totalDiscount.toFixed(2)}</span>` : 
                        '<span class="text-muted">$0.00</span>';
                }
            },
            { 
                data: 'created_at', 
                name: 'created_at',
                render: function(data) {
                    return `<small>${new Date(data).toLocaleDateString()}<br>${new Date(data).toLocaleTimeString()}</small>`;
                }
            },
            {
                data: 'id',
                name: 'actions',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return `
                        <div class="btn-group">
                            <button class="btn btn-sm btn-primary preview-invoice" data-id="${data}" title="View Invoice">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-info download-invoice" data-id="${data}" title="Download Invoice">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        createdRow: function(row, data, dataIndex) {
            if (data.order_status === 'pending') {
                $(row).addClass('table-warning');
            }
            if (data.order_status === 'cancelled') {
                $(row).addClass('table-danger');
            }
        }
    });

    // View invoice in modal using iframe to avoid CSS conflicts
    $('#orderTable').on('click', '.preview-invoice', function() {
        const id = $(this).data('id');
        const invoiceUrl = `{{ url('admin/orders') }}/${id}`;
        $('#invoiceFrame').attr('src', invoiceUrl);
        $('#modalOrderId').text('Loading...');
        
        // Update title when iframe loads
        $('#invoiceFrame').on('load', function() {
            try {
                const iframeDoc = this.contentDocument || this.contentWindow.document;
                const title = iframeDoc.querySelector('.invoice-title p');
                if (title) {
                    $('#modalOrderId').text(title.textContent);
                }
            } catch (e) {
                $('#modalOrderId').text('Order Invoice');
            }
        });
        
        const invoiceModal = new bootstrap.Modal(document.getElementById('invoiceModal'));
        invoiceModal.show();
    });

    // Download invoice
    $('#orderTable').on('click', '.download-invoice', function() {
        const id = $(this).data('id');
        window.open(`{{ url('admin/orders') }}/${id}/invoice/download`, '_blank');
    });

    // Auto-refresh data every 30 seconds
    setInterval(function() {
        dataTable.ajax.reload(null, false);
    }, 30000);
});
</script>
@endsection