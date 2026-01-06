<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $order->order_id }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Scoped CSS to prevent conflicts */
        .invoice-container {
            --primary: #5a67d8;
            --primary-dark: #4c51bf;
            --secondary: #718096;
            --success: #38a169;
            --danger: #e53e3e;
            --warning: #d69e2e;
            --info: #3182ce;
            --light: #f7fafc;
            --dark: #2d3748;
            --sidebar-bg: #f8fafc;
            --border: #e2e8f0;
            --header-bg: #4c51bf;
            --table-header: #5a67d8;
            --accent: #6b46c1;
        }
        
        .invoice-container * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        
        .invoice-container {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            color: var(--dark);
            line-height: 1.6;
            padding: 20px;
            min-height: 100vh;
        }
        
        .invoice-wrapper {
            max-width: 1280px;
            margin: 0 auto;
            display: flex;
            gap: 25px;
            align-items: flex-start;
        }
        
        .invoice-main {
            flex: 1;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        
        .invoice-sidebar {
            width: 350px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        
        .invoice-header {
            background: linear-gradient(120deg, var(--header-bg), var(--accent));
            color: white;
            padding: 5px;
            position: relative;
            overflow: hidden;
        }
        
        .header-pattern {
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            opacity: 0.05;
        }
        
        .invoice-title {
            text-align: center;
            position: relative;
            z-index: 1;
        }
        
        .invoice-title p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .invoice-body {
            padding: 0px;
        }
        
        .info-sections {
            display: inline;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 35px;
        }
        
        .info-card {
            background: var(--light);
            border-radius: 0px;
            padding: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            border: 1px solid var(--border);
        }
        
        .info-card h3 {
            color: var(--primary);
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--primary);
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-card h3 i {
            font-size: 20px;
        }
        
        .info-card p {
            margin-bottom: 12px;
            display: flex;
        }
        
        .info-card strong {
            min-width: 120px;
            display: inline-block;
            color: var(--dark);
        }
        
        .products-section {
            margin-bottom: 35px;
        }
        
        .section-title {
            background: linear-gradient(to right, var(--primary), var(--accent));
            color: white;
            padding: 5px 5px;
            border-radius: 0px 0px 0 0;
            margin-bottom: 0;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 11px;
        }
        
        .section-title i {
            font-size: 24px;
        }
        
        .table-responsive {
            border-radius: 0 0 12px 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border);
            border-top: none;
        }
        
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .invoice-table th {
            background-color: var(--table-header);
            color: white;
            padding: 0px 5px;
            text-align: left;
            font-weight: 600;
            font-size: 15px;
        }
        
        .invoice-table td {
            padding: 16px 15px;
            border-bottom: 1px solid var(--border);
        }
        
        .invoice-table tr:last-child td {
            border-bottom: none;
        }
        
        .invoice-table tr:nth-child(even) {
            background-color: #f9fafe;
        }
        
        .invoice-table tr:hover {
            background-color: #f1f5ff;
        }
        
        .text-end {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .summary-section {
            background: var(--light);
            border-radius: 0px;
            padding: 15px;
            margin-top: 6px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            border: 1px solid var(--border);
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px dashed var(--border);
        }
        
        .grand-total {
            font-size: 15px;
            font-weight: 700;
            color: var(--primary);
            border-bottom: none;
            border-top: 2px solid var(--primary);
            padding-top: 11px;
            margin-top: 5px;
        }
        
        .sidebar-header {
            background: linear-gradient(120deg, var(--primary), var(--accent));
            color: white;
            padding: 5px;
            text-align: center;
        }
        
        .sidebar-header h3 {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .sidebar-header p {
            opacity: 0.9;
        }
        
        .sidebar-content {
            padding: 25px;
        }
        
        .action-card {
            background: var(--light);
            border-radius: 0px;
            padding: 5px;
            margin-bottom: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            border: 1px solid var(--border);
        }
        
        .action-card:last-child {
            margin-bottom: 0;
        }
        
        .action-card h3 {
            color: var(--primary);
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--primary);
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .action-card h3 i {
            font-size: 20px;
        }
        
        .form-select {
            width: 100%;
            padding: 14px 18px;
            border: 1px solid var(--border);
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 15px;
            background: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
        }
        
        .form-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(90, 103, 216, 0.1);
        }
        
        .btn {
            padding: 16px 25px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            width: 100%;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 12px rgba(90, 103, 216, 0.25);
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(90, 103, 216, 0.3);
        }
        
        .btn-success {
            background: var(--success);
            color: white;
            box-shadow: 0 4px 12px rgba(56, 161, 105, 0.25);
        }
        
        .btn-success:hover {
            background: #2f855a;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(56, 161, 105, 0.3);
        }
        
        .status-display {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 15px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .badge {
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .bg-secondary { background-color: var(--secondary); color: white; }
        .bg-primary { background-color: var(--primary); color: white; }
        .bg-success { background-color: var(--success); color: white; }
        .bg-danger { background-color: var(--danger); color: white; }
        .bg-warning { background-color: var(--warning); color: white; }
        .bg-info { background-color: var(--info); color: white; }
        
        #statusMessage, #actionMessage {
            padding: 12px;
            border-radius: 8px;
            margin-top: 15px;
            text-align: center;
            font-weight: 500;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .invoice-footer {
            background: var(--light);
            padding: 25px;
            text-align: center;
            border-top: 1px solid var(--border);
            color: var(--secondary);
            font-size: 14px;
        }
        
        @media (max-width: 1024px) {
            .invoice-wrapper {
                flex-direction: column;
            }
            
            .invoice-sidebar {
                width: 100%;
                margin-top: 25px;
            }
        }
        
        @media (max-width: 768px) {
            .invoice-header {
                flex-direction: column;
                text-align: center;
            }
            
            .company-info {
                margin-bottom: 25px;
                justify-content: center;
            }
            
            .invoice-title {
                text-align: center;
            }
            
            .info-sections {
                grid-template-columns: 1fr;
            }
            
            .invoice-table {
                display: block;
                overflow-x: auto;
            }
        }
        
        @media print {
            .invoice-container {
                background: white;
                padding: 0;
            }
            
            .invoice-wrapper {
                flex-direction: column;
            }
            
            .invoice-sidebar {
                display: none;
            }
            
            .invoice-main, .invoice-sidebar {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
<div class="invoice-container">
@php
    $itemDetails = json_decode($order->item_details, true);
    $processedItems = [];
    $totalCalculated = 0;
    $totalQuantity = 0; // Total quantity calculation
    
    // Handle different JSON structures
    if (isset($itemDetails['diamond'])) {
        foreach ($itemDetails['diamond'] as $diamond) {
            $price = $diamond['price'] ?? 0;
            $quantity = $diamond['quantity'] ?? 1;
            $totalQuantity += $quantity;
            
            $processedItems[] = [
                'type' => 'diamond',
                'id' => $diamond['id'] ?? null,
                'name' => $diamond['diamond_name'] ?? $diamond['name'] ?? 'Diamond',
                'quantity' => $quantity,
                'price' => $price,
                'certificate_number' => $diamond['certificate_number'] ?? $diamond['certificate_no'] ?? null
            ];
            $totalCalculated += $price * $quantity;
        }
    }
    
    if (isset($itemDetails['jewelry'])) {
        foreach ($itemDetails['jewelry'] as $jewelry) {
            $price = $jewelry['price'] ?? 0;
            $quantity = $jewelry['quantity'] ?? 1;
            $totalQuantity += $quantity;
            
            $processedItems[] = [
                'type' => 'jewelry',
                'id' => $jewelry['id'] ?? null,
                'name' => $jewelry['jewelry_name'] ?? $jewelry['name'] ?? 'Jewelry',
                'quantity' => $quantity,
                'price' => $price,
                'metal_type' => $jewelry['metal_type'] ?? $jewelry['metal'] ?? null,
                'metal_color' => $jewelry['metal_color'] ?? $jewelry['color'] ?? null,
                'metal_purity' => $jewelry['metal_purity'] ?? $jewelry['purity'] ?? null,
                'size' => $jewelry['size'] ?? $jewelry['ring_size'] ?? null
            ];
            $totalCalculated += $price * $quantity;
        }
    }
    
    // Handle combo items specifically
    if (isset($itemDetails['combo'])) {
        foreach ($itemDetails['combo'] as $combo) {
            $price = $combo['price'] ?? 0;
            $quantity = $combo['quantity'] ?? 1;
            $totalQuantity += $quantity;
            
            $processedItems[] = [
                'type' => 'combo',
                'id' => $combo['id'] ?? null,
                'name' => $combo['name'] ?? 'Combo Package',
                'quantity' => $quantity,
                'price' => $price,
                'size' => $combo['size'] ?? null,
            ];
            $totalCalculated += $price * $quantity;
        }
    }
    
    // Handle items array structure (for both single products and combo)
    if (isset($itemDetails['items'])) {
        foreach ($itemDetails['items'] as $item) {
            $productType = $item['productType'] ?? 'jewelry';
            $quantity = $item['itemQuantity'] ?? 1;
            $totalQuantity += $quantity;
            
            if ($productType === 'combo') {
                $ringPrice = $item['ring']['price'] ?? 0;
                $diamondPrice = $item['diamond']['price'] ?? 0;
                $comboPrice = $ringPrice + $diamondPrice;
                
                $processedItems[] = [
                    'type' => 'combo',
                    'id' => $item['ring']['id'] ?? null,
                    'name' => $item['ring']['name'] ?? 'Combo Package',
                    'quantity' => $quantity,
                    'price' => $comboPrice,
                    'size' => $item['size'] ?? null,
                    'ring_price' => $ringPrice,
                    'diamond_price' => $diamondPrice,
                    'diamond_certificate' => $item['diamond']['certificate_number'] ?? null,
                    'metal_type' => $item['ring']['metal_color']['name'] ?? null
                ];
                $totalCalculated += $comboPrice * $quantity;
            } else {
                if (isset($item['ring'])) {
                    $price = $item['ring']['price'] ?? 0;
                    $processedItems[] = [
                        'type' => 'jewelry',
                        'id' => $item['ring']['id'] ?? null,
                        'name' => $item['ring']['name'] ?? 'Jewelry',
                        'quantity' => $quantity,
                        'price' => $price,
                        'metal_type' => $item['ring']['metal_color']['name'] ?? null,
                        'size' => $item['size'] ?? null
                    ];
                    $totalCalculated += $price * $quantity;
                } elseif (isset($item['diamond'])) {
                    $price = $item['diamond']['price'] ?? 0;
                    $diamondName = isset($item['diamond']['shape']['name']) ? 
                                  $item['diamond']['shape']['name'] . ' Diamond' : 'Diamond';
                                  
                    $processedItems[] = [
                        'type' => 'diamond',
                        'id' => $item['diamond']['diamondid'] ?? null,
                        'name' => $diamondName,
                        'quantity' => $quantity,
                        'price' => $price,
                        'certificate_number' => $item['diamond']['certificate_number'] ?? null,
                        'carat_weight' => $item['diamond']['carat_weight'] ?? null,
                        'color' => $item['diamond']['color']['name'] ?? null,
                        'clarity' => $item['diamond']['clarity']['name'] ?? null
                    ];
                    $totalCalculated += $price * $quantity;
                } elseif (isset($item['productType'])) {
                    $price = $item['price'] ?? 0;
                    $processedItems[] = [
                        'type' => $item['productType'],
                        'id' => $item['id'] ?? null,
                        'name' => $item['name'] ?? ucfirst($item['productType']),
                        'quantity' => $quantity,
                        'price' => $price,
                        'size' => $item['size'] ?? null
                    ];
                    $totalCalculated += $price * $quantity;
                }
            }
        }
    }
    
    // Use total_quantity from database if available, otherwise calculate
    $displayQuantity = $order->total_quantity ?? $totalQuantity;
    
    // If no items were processed but total price exists, create a generic item
    if (empty($processedItems)) {
        $processedItems[] = [
            'type' => 'unknown',
            'id' => null,
            'name' => 'Product',
            'quantity' => 1,
            'price' => $order->total_price,
            'certificate_number' => null
        ];
        $totalCalculated = $order->total_price;
        $displayQuantity = 1;
    }
    
    // Separate items by type
    $diamondItems = array_filter($processedItems, fn($item) => $item['type'] === 'diamond');
    $jewelryItems = array_filter($processedItems, fn($item) => $item['type'] === 'jewelry');
    $comboItems = array_filter($processedItems, fn($item) => $item['type'] === 'combo');
@endphp

<div class="invoice-wrapper">
    <div class="invoice-main">
        <div class="invoice-header">
            <div class="header-pattern"></div>
            <div class="invoice-title">
                <p>Order #{{ $order->order_id }} | Date: {{ $order->created_at->format('M d, Y') }}</p>
            </div>
        </div>
        
        <div class="invoice-body">
            <div class="info-sections">
                <div class="info-card">
                    <h3><i class="fas fa-user"></i> Customer Information</h3>
                    <p><strong>Name:</strong> {{ $order->user_name }}</p>
                    <p><strong>Email:</strong> {{ $order->user->email ?? ($order->address['email'] ?? 'N/A') }}</p>
                    <p><strong>Contact:</strong> {{ $order->contact_number }}</p>
                    <p><strong>Total Items:</strong> {{ $displayQuantity }}</p>
                </div>
                
                <div class="info-card">
                    <h3><i class="fas fa-truck"></i> Shipping Address</h3>
                    <p>{{ $order->formatted_address }}</p>
                </div>
                
                <div class="info-card">
                    <h3><i class="fas fa-info-circle"></i> Order Information</h3>
                    <p><strong>Status:</strong> 
                        <span class="badge bg-{{ 
                            $order->order_status === 'pending' ? 'secondary' : 
                            ($order->order_status === 'confirmed' ? 'primary' : 
                            ($order->order_status === 'shipped' ? 'info' : 
                            ($order->order_status === 'delivered' ? 'success' : 
                            ($order->order_status === 'returned' ? 'warning' : 'danger'))))
                        }}">
                            {{ ucfirst($order->order_status) }}
                        </span>
                    </p>
                    <p><strong>Payment Method:</strong> {{ ucfirst($order->payment_mode) }}</p>
                    <p><strong>Payment Status:</strong> {{ ucfirst($order->payment_status) }}</p>
                    <p><strong>Total Quantity:</strong> {{ $displayQuantity }} items</p>
                </div>
            </div>
            
            <!-- Rest of your invoice content remains the same -->
            <!-- ... (your existing diamond, jewelry, combo sections) ... -->
            
            <div class="summary-section">
                <div class="summary-row">
                    <span>Total Quantity:</span>
                    <span>{{ $displayQuantity }} items</span>
                </div>
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>${{ number_format($order->total_price, 2) }}</span>
                </div>
                @if($order->coupon_code)
                    <div class="summary-row">
                        <span>Coupon Code:</span>
                        <span>{{ $order->coupon_code }}</span>
                    </div>
                    <div class="summary-row">
                        <span>Coupon Discount:</span>
                        <span class="text-danger">-${{ number_format($order->coupon_discount, 2) }}</span>
                    </div>
                @endif
                <div class="summary-row">
                    <span>Shipping:</span>
                    <span>${{ number_format($order->shipping_cost, 2) }}</span>
                </div>
                <div class="summary-row">
                    <span>Discount:</span>
                    <span class="text-danger">-${{ number_format($order->discount, 2) }}</span>
                </div>
                <div class="summary-row grand-total">
                    <span>Grand Total:</span>
                    <span>${{ number_format($order->total_price + $order->shipping_cost - $order->discount, 2) }}</span>
                </div>
            </div>
        </div>
        
        <div class="invoice-footer">
            <p>Thank you for your business! | Dilse Jewels © {{ date('Y') }} | <a href="https://dilsejewels.com/">www.dilsejewels.com</a></p>
        </div>
    </div>
    
    <div class="invoice-sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-cog"></i> Order Management</h3>
            <p>Manage order status and invoice actions</p>
        </div>
        
        <div class="sidebar-content">
            <div class="action-card">
                <h3><i class="fas fa-tasks"></i> Order Status</h3>
                <select id="statusSelect" class="form-select" {{ in_array($order->order_status, ['cancelled', 'returned']) ? 'disabled' : '' }}>
                    <option value="" disabled selected>Select status</option>
                    <option value="confirmed" {{ $order->order_status === 'pending' ? '' : 'disabled' }}>Confirmed</option>
                    <option value="shipped" {{ $order->order_status === 'confirmed' ? '' : 'disabled' }}>Shipped</option>
                    <option value="delivered" {{ $order->order_status === 'shipped' ? '' : 'disabled' }}>Delivered</option>
                    <option value="returned" {{ $order->order_status === 'delivered' ? '' : 'disabled' }}>Returned</option>
                    <option value="cancelled">Cancel</option>
                </select>
                
                <div class="status-display">
                    <strong>Current Status:</strong>
                    <span class="badge bg-{{ 
                        $order->order_status === 'pending' ? 'secondary' : 
                        ($order->order_status === 'confirmed' ? 'primary' : 
                        ($order->order_status === 'shipped' ? 'info' : 
                        ($order->order_status === 'delivered' ? 'success' : 
                        ($order->order_status === 'returned' ? 'warning' : 'danger'))))
                    }}">
                        {{ ucfirst($order->order_status) }}
                    </span>
                </div>
                
                <button id="updateStatusBtn" class="btn btn-sm btn-primary" {{ in_array($order->order_status, ['cancelled', 'returned']) ? 'disabled' : '' }}>
                    <i class="fas fa-sync-alt"></i> Update Status
                </button>
                <div id="statusMessage" style="display: none;"></div>
            </div>

            <div class="action-card">
                <h3><i class="fas fa-file-invoice"></i> Invoice Actions</h3>
                <select id="actionSelect" class="form-select">
                    <option selected disabled>Select Action</option>
                    <option value="download">Download Invoice</option>
                    <option value="send_user">Send to Customer</option>
                    <option value="send_admin">Send to Admin</option>
                </select>
                <button id="performActionBtn" class="btn btn-success">
                    <i class="fas fa-paper-plane"></i> Execute Action
                </button>
                <div id="actionMessage" style="display: none;"></div>
            </div>
        </div>
</div>
</div>

<script>
    $(function(){
        const orderId = {{ $order->id }};
        const currentStatus = "{{ $order->order_status }}";
        const downloadUrl  = '{{ route("orders.invoice.download", $order->id) }}';
        const sendUrlBase  = '{{ route("orders.invoice.send", $order->id) }}';
        const statusUrl    = '{{ route("orders.changeStatus", $order->id) }}';

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            }
        });

        function showMessage(elementId, message, type) {
            const element = $('#' + elementId);
            element.removeClass().addClass(type === 'success' ? 'alert-success' : 'alert-danger')
                   .text(message).show().delay(3000).fadeOut();
        }

        $('#updateStatusBtn').on('click', function(){
            const newStatus = $('#statusSelect').val();
            const btn = $(this);
            
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');
            
            $.ajax({
                url: statusUrl,
                method: 'PATCH',
                data: { 
                    order_status: newStatus,
                    _token: "{{ csrf_token() }}"
                },
                success: function() {
                    showMessage('statusMessage', 'Status updated successfully!', 'success');
                    setTimeout(() => location.reload(), 1500);
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Update Status');
                    showMessage('statusMessage', xhr.responseJSON?.message || 'Error updating status', 'danger');
                }
            });
        });

        $('#performActionBtn').on('click', function(){
            const action = $('#actionSelect').val();
            const btn = $(this);
            
            if (!action) {
                showMessage('actionMessage', 'Please select an action first', 'danger');
                return;
            }
            
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
            
            if (action === 'download') {
                window.open(downloadUrl, '_blank');
                showMessage('actionMessage', 'Download started!', 'success');
                btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Execute Action');
            }
            else {
                const to = action === 'send_user' ? 'user' : 'admin';
                
                $.get(sendUrlBase + '?to=' + to, function(res) {
                    showMessage('actionMessage', res.message || 'Invoice sent successfully!', 'success');
                    btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Execute Action');
                }).fail(function(xhr) {
                    showMessage('actionMessage', xhr.responseJSON?.message || 'Sending failed. Please try again.', 'danger');
                    btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Execute Action');
                });
            }
        });
    });
</script>
</body>
</html>