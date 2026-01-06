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
      <div>
        <span class="badge bg-primary me-3">Total Orders: {{ \App\Models\Order::count() }}</span>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createOrderModal">
          <i class="fas fa-plus me-2"></i>Create New Order
        </button>
      </div>
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
            <th>Payment</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="orderTableBody"></tbody>
      </table>
    </div>
  </div>
</div>

<!-- Create Order Modal -->
<div class="modal fade" id="createOrderModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Create New Order</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="createOrderForm">
          <!-- Customer Search Section -->
          <div class="row mb-4">
            <div class="col-12">
              <h6 class="mb-3">Customer Information</h6>
              <div class="input-group">
                <input type="text" class="form-control" id="userSearch" placeholder="Search users by name, email or ID...">
                <button class="btn btn-outline-primary" type="button" id="searchUserBtn">
                  <i class="fas fa-search"></i>
                </button>
              </div>
              <div id="userResults" class="mt-2" style="display: none;"></div>
              <div id="selectedUser" class="mt-2 p-3 border rounded" style="display: none;"></div>
            </div>
          </div>

          <!-- Product Search Section -->
          <div class="row mb-4">
            <div class="col-12">
              <h6 class="mb-3">Add Products & Diamonds</h6>
              <ul class="nav nav-tabs" id="searchTabs" role="tablist">
                <li class="nav-item" role="presentation">
                  <button class="nav-link active" id="products-tab" data-bs-toggle="tab" data-bs-target="#products" type="button">
                    <i class="fas fa-ring me-2"></i>Jewelry
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="diamonds-tab" data-bs-toggle="tab" data-bs-target="#diamonds" type="button">
                    <i class="fas fa-gem me-2"></i>Diamonds
                  </button>
                </li>
              </ul>
              
              <div class="tab-content mt-3">
                <!-- Products Tab -->
                <div class="tab-pane fade show active" id="products" role="tabpanel">
                  <div class="input-group mb-3">
                    <input type="text" class="form-control" id="productSearch" placeholder="Search jewelry by name, SKU...">
                    <button class="btn btn-outline-primary" type="button" id="searchProductBtn">
                      <i class="fas fa-search"></i>
                    </button>
                  </div>
                  <div id="productResults" class="row"></div>
                </div>
                
                <!-- Diamonds Tab -->
                <div class="tab-pane fade" id="diamonds" role="tabpanel">
                  <div class="input-group mb-3">
                    <input type="text" class="form-control" id="diamondSearch" placeholder="Search diamonds by type, certificate, stock number...">
                    <button class="btn btn-outline-primary" type="button" id="searchDiamondBtn">
                      <i class="fas fa-search"></i>
                    </button>
                  </div>
                  <div id="diamondResults" class="row"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Selected Items Section -->
          <div class="row mb-4">
            <div class="col-12">
              <h6 class="mb-3">Selected Items</h6>
              <div id="selectedItems" class="table-responsive">
                <table class="table table-sm">
                  <thead>
                    <tr>
                      <th>Type</th>
                      <th>Name</th>
                      <th>Details</th>
                      <th>Quantity</th>
                      <th>Price</th>
                      <th>Total</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody id="selectedItemsBody">
                    <tr id="noItemsRow">
                      <td colspan="7" class="text-center text-muted py-4">No items selected</td>
                    </tr>
                  </tbody>
                  <tfoot id="orderSummary" style="display: none;">
                    <tr>
                      <td colspan="5" class="text-end"><strong>Subtotal:</strong></td>
                      <td><strong id="subtotal">$0.00</strong></td>
                      <td></td>
                    </tr>
                    <tr>
                      <td colspan="5" class="text-end">Shipping:</td>
                      <td>
                        <input type="number" class="form-control form-control-sm" id="shippingCost" name="shipping_cost" value="0" min="0" step="0.01">
                      </td>
                      <td></td>
                    </tr>
                    <tr>
                      <td colspan="5" class="text-end">Discount:</td>
                      <td>
                        <input type="number" class="form-control form-control-sm" id="discount" name="discount" value="0" min="0" step="0.01">
                      </td>
                      <td></td>
                    </tr>
                    <tr>
                      <td colspan="5" class="text-end"><strong>Grand Total:</strong></td>
                      <td><strong id="grandTotal">$0.00</strong></td>
                      <td></td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>

          <!-- Hidden fields for order details -->
          <input type="hidden" name="payment_mode" value="cod">
          <input type="hidden" name="payment_status" value="pending">
          <input type="hidden" name="order_status" value="pending">
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="createOrderBtn">Create Order</button>
      </div>
    </div>
  </div>
</div>

<!-- Invoice Modal -->
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
    let selectedItems = [];
    let selectedUser = null;
    let selectedAddress = null;

    // Initialize DataTable
    const dataTable = $('#orderTable').DataTable({
        order: [[7, 'desc']],
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
                data: 'payment_mode', 
                name: 'payment_mode',
                render: function(data) {
                    return `<span class="badge bg-info">${data.toUpperCase()}</span>`;
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

    // Search Users
    $('#searchUserBtn').click(function() {
        searchUsers();
    });

    $('#userSearch').on('keypress', function(e) {
        if (e.which === 13) {
            searchUsers();
        }
    });

    function searchUsers() {
        const searchTerm = $('#userSearch').val();
        if (searchTerm.length < 2) {
            alert('Please enter at least 2 characters to search');
            return;
        }

        $.get('{{ route("orders.search.users") }}', { search: searchTerm }, function(response) {
            let html = '';
            if (response.length > 0) {
                response.forEach(user => {
                    html += `
                        <div class="card mb-2">
                            <div class="card-body">
                                <h6 class="card-title">${user.name} (${user.email})</h6>
                                ${user.addresses.length > 0 ? `
                                    <div class="mt-2">
                                        <strong>Addresses:</strong>
                                        ${user.addresses.map(addr => `
                                            <div class="form-check mt-1">
                                                <input class="form-check-input" type="radio" name="userAddress" value="${addr.id}" 
                                                    data-user-id="${user.id}"
                                                    data-user-name="${user.name}"
                                                    data-user-email="${user.email}"
                                                    data-user-phone="${user.phone || ''}"
                                                    data-address-id="${addr.id}"
                                                    data-address-full="${addr.full_address.replace(/"/g, '&quot;')}"
                                                    data-address-phone="${addr.phone_number || ''}">
                                                <label class="form-check-label">
                                                    ${addr.full_address}
                                                </label>
                                            </div>
                                        `).join('')}
                                    </div>
                                ` : '<p class="text-muted">No addresses found</p>'}
                            </div>
                        </div>
                    `;
                });
            } else {
                html = '<p class="text-muted">No users found</p>';
            }
            $('#userResults').html(html).show();
        });
    }

    // Select User Address
    $(document).on('change', 'input[name="userAddress"]', function() {
        const userId = $(this).data('user-id');
        const userName = $(this).data('user-name');
        const userEmail = $(this).data('user-email');
        const userPhone = $(this).data('user-phone');
        const addressId = $(this).data('address-id');
        const addressFull = $(this).data('address-full');
        const addressPhone = $(this).data('address-phone');
        
        selectedUser = {
            id: userId,
            name: userName,
            email: userEmail,
            phone: userPhone
        };

        selectedAddress = {
            id: addressId,
            full_address: addressFull,
            phone_number: addressPhone
        };

        $('#selectedUser').html(`
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong>${userName}</strong><br>
                    <small class="text-muted">${userEmail}</small><br>
                    <small class="text-muted">${addressPhone || userPhone || 'No phone'}</small><br>
                    <small>${addressFull}</small>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger" id="removeUserBtn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `).show();

        $('#userResults').hide();
        $('#userSearch').val('');
    });

    // Remove Selected User
    $(document).on('click', '#removeUserBtn', function() {
        selectedUser = null;
        selectedAddress = null;
        $('#selectedUser').hide().html('');
    });

    // Search Products
    $('#searchProductBtn').click(function() {
        searchProducts();
    });

    $('#productSearch').on('keypress', function(e) {
        if (e.which === 13) {
            searchProducts();
        }
    });

    function searchProducts() {
        const searchTerm = $('#productSearch').val();
        if (searchTerm.length < 2) {
            alert('Please enter at least 2 characters to search');
            return;
        }

        $.get('{{ route("orders.search.products") }}', { search: searchTerm }, function(response) {
            let html = '';
            if (response.length > 0) {
                response.forEach(product => {
                    html += `
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="form-check">
                                        <input class="form-check-input product-checkbox" type="checkbox" 
                                               data-product-id="${product.id}"
                                               data-product-name="${product.name}"
                                               data-product-price="${product.price}"
                                               data-product-metal="${product.metal_color || ''}"
                                               data-product-shape="${product.shape || ''}"
                                               data-product-carat="${product.carat || ''}">
                                        <label class="form-check-label w-100">
                                            <strong>${product.name}</strong><br>
                                            <small class="text-muted">SKU: ${product.sku}</small><br>
                                            ${product.carat ? `<small>Carat: ${product.carat}</small><br>` : ''}
                                            ${product.metal_color ? `<small>Metal: ${product.metal_color}</small><br>` : ''}
                                            ${product.shape ? `<small>Shape: ${product.shape}</small><br>` : ''}
                                            <small>Stock: ${product.stock}</small><br>
                                            <strong class="text-primary">$${parseFloat(product.price).toFixed(2)}</strong>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
            } else {
                html = '<div class="col-12"><p class="text-muted">No products found</p></div>';
            }
            $('#productResults').html(html);
        });
    }

// Search Diamonds
$('#searchDiamondBtn').click(function() {
    searchDiamonds();
});

$('#diamondSearch').on('keypress', function(e) {
    if (e.which === 13) {
        searchDiamonds();
    }
});

function searchDiamonds() {
    const searchTerm = $('#diamondSearch').val();
    if (searchTerm.length < 2) {
        alert('Please enter at least 2 characters to search');
        return;
    }

    $.get('{{ route("orders.search.diamonds") }}', { search: searchTerm }, function(response) {
        let html = '';
        
        // Check if response is an array and has items
        if (Array.isArray(response) && response.length > 0) {
            response.forEach(diamond => {
                html += `
                    <div class="col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="form-check">
                                    <input class="form-check-input diamond-checkbox" type="checkbox" 
                                           data-diamond-id="${diamond.id}"
                                           data-diamond-name="${diamond.name}"
                                           data-diamond-price="${diamond.price}"
                                           data-diamond-cert="${diamond.certificate_number || ''}"
                                           data-diamond-carat="${diamond.carat_weight || ''}"
                                           data-diamond-shape="${diamond.shape || ''}"
                                           data-diamond-color="${diamond.color || ''}"
                                           data-diamond-clarity="${diamond.clarity || ''}">
                                    <label class="form-check-label w-100">
                                        <strong>${diamond.name}</strong><br>
                                        <small class="text-muted">Cert: ${diamond.certificate_number || 'N/A'}</small><br>
                                        <small>Shape: ${diamond.shape || 'N/A'}</small><br>
                                        <small>Carat: ${diamond.carat_weight || 'N/A'}</small><br>
                                        <small>Color: ${diamond.color || 'N/A'} | Clarity: ${diamond.clarity || 'N/A'}</small><br>
                                        <small>Stock: ${diamond.stock}</small><br>
                                        <strong class="text-primary">$${parseFloat(diamond.price).toFixed(2)}</strong>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
        } else {
            html = '<div class="col-12"><p class="text-muted">No diamonds found</p></div>';
        }
        $('#diamondResults').html(html);
    }).fail(function(xhr, status, error) {
        console.error('Diamond search failed:', error);
        $('#diamondResults').html('<div class="col-12"><p class="text-danger">Error searching diamonds. Please try again.</p></div>');
    });
}

    // Add Product to Selected Items
    $(document).on('change', '.product-checkbox', function() {
        const productId = $(this).data('product-id');
        const productName = $(this).data('product-name');
        const productPrice = parseFloat($(this).data('product-price'));
        const metalColor = $(this).data('product-metal');
        const shape = $(this).data('product-shape');
        const carat = $(this).data('product-carat');
        
        if ($(this).is(':checked')) {
            // Check if already added
            const existingIndex = selectedItems.findIndex(item => 
                item.type === 'jewelry' && item.id === productId
            );
            
            if (existingIndex === -1) {
                selectedItems.push({
                    type: 'jewelry',
                    id: productId,
                    name: productName,
                    price: productPrice,
                    quantity: 1,
                    metal_color: metalColor,
                    shape: shape,
                    carat: carat
                });
            }
        } else {
            // Remove if unchecked
            selectedItems = selectedItems.filter(item => 
                !(item.type === 'jewelry' && item.id === productId)
            );
        }
        
        updateSelectedItems();
    });

    // Add Diamond to Selected Items
    $(document).on('change', '.diamond-checkbox', function() {
        const diamondId = $(this).data('diamond-id');
        const diamondName = $(this).data('diamond-name');
        const diamondPrice = parseFloat($(this).data('diamond-price'));
        const certificate = $(this).data('diamond-cert');
        const caratWeight = $(this).data('diamond-carat');
        const shape = $(this).data('diamond-shape');
        const color = $(this).data('diamond-color');
        const clarity = $(this).data('diamond-clarity');
        
        if ($(this).is(':checked')) {
            // Check if already added
            const existingIndex = selectedItems.findIndex(item => 
                item.type === 'diamond' && item.id === diamondId
            );
            
            if (existingIndex === -1) {
                selectedItems.push({
                    type: 'diamond',
                    id: diamondId,
                    name: diamondName,
                    price: diamondPrice,
                    quantity: 1,
                    certificate_number: certificate,
                    carat_weight: caratWeight,
                    shape: shape,
                    color: color,
                    clarity: clarity
                });
            }
        } else {
            // Remove if unchecked
            selectedItems = selectedItems.filter(item => 
                !(item.type === 'diamond' && item.id === diamondId)
            );
        }
        
        updateSelectedItems();
    });

    // Update quantity
    $(document).on('change', '.item-quantity', function() {
        const index = $(this).data('index');
        const quantity = parseInt($(this).val());
        
        if (quantity > 0) {
            selectedItems[index].quantity = quantity;
            updateSelectedItems();
        }
    });

    // Remove item
    $(document).on('click', '.remove-item', function() {
        const index = $(this).data('index');
        selectedItems.splice(index, 1);
        
        // Also uncheck the corresponding checkbox
        const removedItem = selectedItems[index];
        if (removedItem) {
            if (removedItem.type === 'jewelry') {
                $(`.product-checkbox[data-product-id="${removedItem.id}"]`).prop('checked', false);
            } else if (removedItem.type === 'diamond') {
                $(`.diamond-checkbox[data-diamond-id="${removedItem.id}"]`).prop('checked', false);
            }
        }
        
        updateSelectedItems();
    });

    function updateSelectedItems() {
        const tbody = $('#selectedItemsBody');
        const noItemsRow = $('#noItemsRow');
        const orderSummary = $('#orderSummary');
        
        if (selectedItems.length === 0) {
            noItemsRow.show();
            orderSummary.hide();
            tbody.find('tr:not(#noItemsRow)').remove();
            return;
        }
        
        noItemsRow.hide();
        orderSummary.show();
        
        let html = '';
        let subtotal = 0;
        
        selectedItems.forEach((item, index) => {
            const itemTotal = item.price * item.quantity;
            subtotal += itemTotal;
            
            html += `
                <tr>
                    <td>
                        <span class="badge ${item.type === 'diamond' ? 'bg-warning' : 'bg-info'}">
                            ${item.type.charAt(0).toUpperCase() + item.type.slice(1)}
                        </span>
                    </td>
                    <td>${item.name}</td>
                    <td>
                        <small>
                            ${item.type === 'diamond' ? 
                                `Cert: ${item.certificate_number || 'N/A'}<br>${item.carat_weight || 'N/A'}ct, ${item.color || 'N/A'}/${item.clarity || 'N/A'}` : 
                                `${item.metal_color ? item.metal_color + '<br>' : ''}${item.carat ? item.carat + 'ct' : ''}`
                            }
                        </small>
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm item-quantity" 
                               value="${item.quantity}" min="1" max="99" data-index="${index}" style="width: 70px;">
                    </td>
                    <td>$${item.price.toFixed(2)}</td>
                    <td>$${itemTotal.toFixed(2)}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-item" data-index="${index}">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        
        tbody.html(html);
        updateOrderSummary(subtotal);
    }

    function updateOrderSummary(subtotal) {
        const shippingCost = parseFloat($('#shippingCost').val()) || 0;
        const discount = parseFloat($('#discount').val()) || 0;
        const grandTotal = subtotal + shippingCost - discount;
        
        $('#subtotal').text('$' + subtotal.toFixed(2));
        $('#grandTotal').text('$' + grandTotal.toFixed(2));
    }

    // Recalculate when shipping or discount changes
    $('#shippingCost, #discount').on('change', function() {
        const subtotal = selectedItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        updateOrderSummary(subtotal);
    });

    // Create Order
    $('#createOrderBtn').click(function() {
        if (!selectedUser || !selectedAddress) {
            alert('Please select a customer and address');
            return;
        }
        
        if (selectedItems.length === 0) {
            alert('Please select at least one product or diamond');
            return;
        }

        const formData = {
            user_id: selectedUser.id,
            user_name: selectedUser.name,
            contact_number: selectedAddress.phone_number || selectedUser.phone || 'N/A',
            items: selectedItems,
            total_price: selectedItems.reduce((sum, item) => sum + (item.price * item.quantity), 0),
            shipping_cost: parseFloat($('#shippingCost').val()) || 0,
            discount: parseFloat($('#discount').val()) || 0,
            address: selectedAddress
        };

        $.ajax({
            url: '{{ route("orders.store") }}',
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#createOrderModal').modal('hide');
                    showMessage('Order created successfully!', 'success');
                    dataTable.ajax.reload();
                    resetForm();
                }
            },
            error: function(xhr) {
                const errors = xhr.responseJSON?.errors;
                if (errors) {
                    let errorMsg = '';
                    for (const field in errors) {
                        errorMsg += errors[field][0] + '\n';
                    }
                    alert('Error: ' + errorMsg);
                } else {
                    alert('Error creating order');
                }
            }
        });
    });

    function resetForm() {
        selectedItems = [];
        selectedUser = null;
        selectedAddress = null;
        $('#selectedUser').hide().html('');
        $('#userResults').hide().html('');
        $('#productResults').html('');
        $('#diamondResults').html('');
        $('#selectedItemsBody').html('<tr id="noItemsRow"><td colspan="7" class="text-center text-muted py-4">No items selected</td></tr>');
        $('#orderSummary').hide();
        $('#createOrderForm')[0].reset();
        $('.product-checkbox, .diamond-checkbox').prop('checked', false);
    }

    function showMessage(message, type) {
        const messageContainer = $('#messageContainer');
        const messageText = $('#messageText');
        
        messageContainer.removeClass('alert-success alert-danger')
                       .addClass(`alert-${type === 'success' ? 'success' : 'danger'}`)
                       .show();
        messageText.text(message);
        
        setTimeout(() => {
            messageContainer.fadeOut();
        }, 5000);
    }

    // View invoice in modal
    $('#orderTable').on('click', '.preview-invoice', function() {
        const id = $(this).data('id');
        const invoiceUrl = `{{ url('admin/orders') }}/${id}`;
        $('#invoiceFrame').attr('src', invoiceUrl);
        $('#modalOrderId').text('Loading...');
        
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