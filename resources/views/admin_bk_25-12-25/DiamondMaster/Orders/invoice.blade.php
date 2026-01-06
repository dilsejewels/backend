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
        :root {
            --primary: #8B4513;
            --primary-light: #A0522D;
            --secondary: #CD853F;
            --accent: #D2691E;
            --gold: #D4AF37;
            --silver: #C0C0C0;
            --diamond: #B9F2FF;
            --natural: #4CAF50;
            --cvd: #2196F3;
            --text-dark: #2C1810;
            --text-light: #5D4037;
            --bg-cream: #FDF6E3;
            --bg-paper: #FFF8DC;
            --border: #DEB887;
            --success: #228B22;
            --warning: #FF8C00;
            --danger: #DC143C;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Georgia', 'Times New Roman', serif;
        }
        
        body {
            background: linear-gradient(135deg, #F5F5DC 0%, #FFF8DC 50%, #FDF5E6 100%);
            color: var(--text-dark);
            line-height: 1.6;
            padding: 15px;
            min-height: 100vh;
        }
        
        .invoice-container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .invoice-wrapper {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .invoice-main, .invoice-sidebar {
            background: var(--bg-paper);
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(139, 69, 19, 0.1);
            overflow: hidden;
            border: 1px solid var(--border);
        }
        
        .invoice-main::before, .summary-section::before {
            content: "";
            display: block;
            height: 4px;
            background: linear-gradient(90deg, var(--gold), var(--silver), var(--diamond));
        }
        
        /* Header Styles */
        .invoice-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            padding: 25px 20px;
            position: relative;
            overflow: hidden;
        }
        
        .company-brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .logo-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, var(--gold), var(--silver));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: var(--primary);
        }
        
        .brand-text h1 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .brand-text p {
            font-size: 13px;
            opacity: 0.9;
        }
        
        .invoice-title {
            text-align: center;
        }
        
        .invoice-title h2 {
            font-size: 26px;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .invoice-title p {
            font-size: 14px;
            opacity: 0.9;
        }
        
        /* Body Styles */
        .invoice-body {
            padding: 25px 20px;
        }
        
        .info-sections {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .info-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            padding: 20px;
            border: 1px solid var(--border);
            position: relative;
            overflow: hidden;
        }
        
        .info-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(to bottom, var(--gold), var(--accent));
        }
        
        .info-card h3 {
            color: var(--primary);
            margin-bottom: 15px;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--gold);
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-card p {
            margin-bottom: 10px;
            display: flex;
            flex-wrap: wrap;
            color: var(--text-light);
            font-size: 14px;
        }
        
        .info-card strong {
            min-width: 120px;
            color: var(--text-dark);
            margin-right: 10px;
        }
        
        /* Address Section */
        .address-section {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .address-card {
            flex: 1;
            min-width: 300px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            padding: 20px;
            border: 1px solid var(--border);
            position: relative;
        }
        
        .address-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(to bottom, var(--accent), var(--secondary));
        }
        
        .address-card h3 {
            color: var(--primary);
            margin-bottom: 15px;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--accent);
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .address-card .address-content {
            font-size: 14px;
            color: var(--text-light);
            line-height: 1.6;
        }
        
        .address-card .address-content p {
            margin-bottom: 8px;
        }
        
        /* Coupon Section */
        .coupon-section {
            background: linear-gradient(135deg, #E8F5E9, #C8E6C9);
            border-radius: 10px;
            padding: 15px 20px;
            margin: 15px 0 25px 0;
            border: 1px solid #81C784;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .coupon-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .coupon-badge {
            background: linear-gradient(135deg, #4CAF50, #388E3C);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            font-size: 14px;
        }
        
        .coupon-details {
            font-size: 15px;
        }
        
        .coupon-details strong {
            color: var(--primary);
        }
        
        /* Items Section */
        .items-section {
            margin-bottom: 25px;
        }
        
        .section-header {
            background: linear-gradient(135deg, var(--secondary), var(--accent));
            color: white;
            padding: 15px 20px;
            border-radius: 10px 10px 0 0;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 18px;
        }
        
        .table-container {
            border-radius: 0 0 10px 10px;
            overflow: hidden;
            border: 1px solid var(--border);
            border-top: none;
            overflow-x: auto;
        }
        
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            min-width: 600px;
        }
        
        .invoice-table th {
            background: linear-gradient(135deg, var(--primary-light), var(--primary));
            color: white;
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            white-space: nowrap;
        }
        
        .invoice-table td {
            padding: 12px;
            border-bottom: 1px solid var(--border);
            color: var(--text-light);
            font-size: 14px;
            vertical-align: top;
        }
        
        .invoice-table tr:last-child td {
            border-bottom: none;
        }
        
        /* Product Type Badges */
        .product-type-badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
        }
        
        .badge-diamond {
            background: linear-gradient(135deg, #B9F2FF, #7ED4FF);
            color: #0D47A1;
        }
        
        .badge-jewelry {
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: #5D4037;
        }
        
        .badge-combo {
            background: linear-gradient(135deg, #FFB6C1, #FF69B4);
            color: #880E4F;
        }
        
        .badge-mixed {
            background: linear-gradient(135deg, #D8BFD8, #9370DB);
            color: #4A148C;
        }
        
        .badge-natural {
            background: linear-gradient(135deg, var(--natural), #2E7D32);
            color: white;
        }
        
        .badge-cvd {
            background: linear-gradient(135deg, var(--cvd), #0D47A1);
            color: white;
        }
        
        /* Diamond Type Specific Styles */
        .natural-diamond-row {
            background: rgba(76, 175, 80, 0.05) !important;
            border-left: 4px solid var(--natural);
        }
        
        .cvd-diamond-row {
            background: rgba(33, 150, 243, 0.05) !important;
            border-left: 4px solid var(--cvd);
        }
        
        /* Special item type styling */
        .diamond-row {
            background: rgba(185, 242, 255, 0.1) !important;
            border-left: 4px solid var(--diamond);
        }
        
        .jewelry-row {
            background: rgba(212, 175, 55, 0.1) !important;
            border-left: 4px solid var(--gold);
        }
        
        .combo-row {
            background: rgba(205, 133, 63, 0.1) !important;
            border-left: 4px solid var(--accent);
        }
        
        .mixed-row {
            background: rgba(216, 191, 216, 0.1) !important;
            border-left: 4px solid #9370DB;
        }
        
        /* Specifications Styles */
        .item-specs {
            font-size: 11px;
        }
        
        .spec-item {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 3px;
            color: #555;
        }
        
        .spec-item i {
            width: 12px;
            color: #8B4513;
            font-size: 10px;
        }
        
        .spec-label {
            font-weight: 600;
            color: #333;
            min-width: 60px;
        }
        
        .spec-value {
            color: #666;
        }
        
        .diamond-specs-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 4px;
        }
        
        /* Certificate Badge */
        .certificate-badge {
            background: linear-gradient(135deg, #FF9800, #F57C00);
            color: white;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 10px;
            display: inline-block;
            margin-top: 5px;
        }
        
        /* Summary Section */
        .summary-section {
            background: linear-gradient(135deg, #FFF8DC, #F5F5DC);
            border-radius: 12px;
            padding: 25px 20px;
            margin-top: 25px;
            border: 2px solid var(--gold);
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px dashed var(--border);
            font-size: 15px;
        }
        
        .summary-row:last-child {
            border-bottom: none;
        }
        
        .grand-total {
            font-size: 18px;
            font-weight: bold;
            color: var(--primary);
            border-top: 2px solid var(--gold);
            padding-top: 15px;
            margin-top: 8px;
        }
        
        /* Sidebar Styles */
        .sidebar-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .sidebar-header h3 {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .sidebar-content {
            padding: 20px;
        }
        
        .action-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid var(--border);
            position: relative;
        }
        
        .action-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(to bottom, var(--accent), var(--secondary));
        }
        
        .action-card h3 {
            color: var(--primary);
            margin-bottom: 15px;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--accent);
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border);
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 14px;
            background: white;
        }
        
        .btn {
            padding: 14px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            width: 100%;
            font-size: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: white;
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--success), #32CD32);
            color: white;
        }
        
        .status-display {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
            padding: 15px;
            background: rgba(255, 248, 220, 0.7);
            border-radius: 8px;
            border: 1px solid var(--border);
            font-size: 14px;
        }
        
        .badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .bg-secondary { background: #6B7280; color: white; }
        .bg-primary { background: var(--primary); color: white; }
        .bg-success { background: var(--success); color: white; }
        .bg-danger { background: var(--danger); color: white; }
        .bg-warning { background: var(--warning); color: white; }
        .bg-info { background: #3B82F6; color: white; }
        
        #statusMessage, #actionMessage {
            padding: 12px;
            border-radius: 8px;
            margin-top: 12px;
            text-align: center;
            font-size: 14px;
        }
        
        .alert-success {
            background: rgba(34, 139, 34, 0.1);
            color: var(--success);
            border: 1px solid rgba(34, 139, 34, 0.2);
        }
        
        .alert-danger {
            background: rgba(220, 20, 60, 0.1);
            color: var(--danger);
            border: 1px solid rgba(220, 20, 60, 0.2);
        }
        
        .invoice-footer {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            padding: 25px 20px;
            text-align: center;
        }
        
        .invoice-footer p {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .invoice-footer a {
            color: var(--gold);
            text-decoration: none;
        }
        
        /* Desktop Styles */
        @media (min-width: 992px) {
            body {
                padding: 20px;
            }
            
            .invoice-wrapper {
                flex-direction: row;
                align-items: flex-start;
            }
            
            .invoice-main {
                flex: 1;
            }
            
            .invoice-sidebar {
                width: 350px;
            }
            
            .company-brand {
                flex-direction: row;
                justify-content: space-between;
                text-align: left;
            }
            
            .info-sections {
                flex-direction: row;
            }
            
            .info-card {
                flex: 1;
            }
        }
        
        @media (min-width: 768px) {
            .invoice-header {
                padding: 30px 40px;
            }
            
            .invoice-body {
                padding: 40px;
            }
            
            .brand-text h1 {
                font-size: 28px;
            }
            
            .invoice-title h2 {
                font-size: 32px;
            }
            
            .sidebar-content {
                padding: 30px;
            }
        }
        
        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            
            .invoice-header {
                padding: 20px 15px;
            }
            
            .invoice-body {
                padding: 20px 15px;
            }
            
            .brand-text h1 {
                font-size: 22px;
            }
            
            .invoice-title h2 {
                font-size: 24px;
            }
            
            .info-card {
                padding: 15px;
            }
            
            .invoice-table th,
            .invoice-table td {
                padding: 10px 8px;
                font-size: 13px;
            }
            
            .summary-section {
                padding: 20px 15px;
            }
            
            .diamond-specs-grid {
                grid-template-columns: 1fr;
            }
        }
        
        /* Print Styles */
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .invoice-sidebar {
                display: none;
            }
            
            .invoice-main, .invoice-sidebar {
                box-shadow: none;
            }
            
            .btn {
                display: none;
            }
        }
    </style>
</head>
<body>
<div class="invoice-container">
    <div class="invoice-wrapper">
        <div class="invoice-main">
            <div class="invoice-header">
                <div class="company-brand">
                    <div class="brand-logo">
                        <div class="logo-icon">
                            <i class="fas fa-gem"></i>
                        </div>
                        <div class="brand-text">
                            <h1>Dilse Jewels</h1>
                            <p>Luxury Diamonds & Fine Jewelry</p>
                        </div>
                    </div>
                    <div class="invoice-title">
                        <h2>INVOICE</h2>
                        <p>Order #{{ $order->order_id }} | {{ $order->created_at->format('F d, Y') }}</p>
                    </div>
                </div>
            </div>
            
            <div class="invoice-body">
                <!-- Customer Information -->
                <div class="info-sections">
                    <div class="info-card">
                        <h3><i class="fas fa-user-circle"></i> Customer Information</h3>
                        <p><strong>Name:</strong> {{ $order->user_name ?? 'N/A' }}</p>
                        <p><strong>Email:</strong> {{ $order->user->email ?? ($order->address_email ?? 'N/A') }}</p>
                        <p><strong>Contact:</strong> {{ $order->contact_number ?? 'N/A' }}</p>
                    </div>
                    
                    <div class="info-card">
                        <h3><i class="fas fa-info-circle"></i> Order Information</h3>
                        <p><strong>Status:</strong> 
                            <span class="badge bg-{{ 
                                $order->order_status === 'pending' ? 'secondary' : 
                                ($order->order_status === 'confirmed' ? 'primary' : 
                                ($order->order_status === 'shipped' ? 'info' : 
                                ($order->order_status === 'delivered' ? 'success' : 'danger')))
                            }}">
                                {{ ucfirst($order->order_status ?? 'N/A') }}
                            </span>
                        </p>
                        <p><strong>Payment Method:</strong> {{ ucfirst($order->payment_mode ?? 'N/A') }}</p>
                        <p><strong>Order Date:</strong> {{ $order->created_at->format('M d, Y') ?? 'N/A' }}</p>
                    </div>
                </div>

                <!-- Shipping Address -->
                <div class="address-card">
                    <h3><i class="fas fa-truck"></i> Shipping Address</h3>
                    <div class="address-content">
                        @if(is_array($order->formatted_address))
                            @foreach($order->formatted_address as $line)
                                {{ $line }}<br>
                            @endforeach
                        @else
                            {!! nl2br(e($order->formatted_address ?? 'N/A')) !!}
                        @endif
                    </div>
                </div>

                <!-- Coupon Section - Show only if coupon used -->
                @if($order->coupon_code && $order->coupon_discount > 0)
                <div class="coupon-section">
                    <div class="coupon-info">
                        <span class="coupon-badge">
                            <i class="fas fa-tag"></i>
                            COUPON APPLIED
                        </span>
                        <div class="coupon-details">
                            <strong>Coupon Code:</strong> {{ $order->coupon_code }} |
                            <strong>Discount:</strong> -${{ number_format($order->coupon_discount, 2) }}
                        </div>
                    </div>
                </div>
                @endif

                <!-- Order Items Table -->
                @php
                    $itemsCount = isset($processedItems) && is_array($processedItems) ? count($processedItems) : 0;
                @endphp

                @if($itemsCount > 0)
                <div class="items-section">
                    <div class="section-header">
                        <i class="fas fa-shopping-bag"></i>
                        <span>Order Items ({{ $itemsCount }} items)</span>
                    </div>
                    <div class="table-container">
                        <table class="invoice-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Item Description</th>
                                    <th>Type</th>
                                    <th>Specifications</th>
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($processedItems as $index => $item)
                                @php
                                    // Determine row class based on diamond type
                                    $rowClass = '';
                                    if (($item['type'] ?? '') === 'diamond') {
                                        $diamondType = isset($item['diamond_type_label']) ? $item['diamond_type_label'] : 
                                                      (isset($item['diamond_type']) && $item['diamond_type'] == 2 ? 'CVD' : 'Natural');
                                        $rowClass = strtolower($diamondType) . '-diamond-row diamond-row';
                                    } else {
                                        $rowClass = ($item['type'] ?? '') . '-row';
                                    }
                                    
                                    // Generate item name based on type
                                    $itemName = $item['name'] ?? 'Product';
                                    if (($item['type'] ?? '') === 'diamond') {
                                        // Build descriptive name for diamond
                                        $parts = [];
                                        if (isset($item['shape']) && $item['shape'] !== 'N/A') {
                                            $parts[] = $item['shape'];
                                        }
                                        if (isset($item['carat_weight']) && $item['carat_weight'] !== 'N/A') {
                                            $parts[] = $item['carat_weight'] . 'ct';
                                        }
                                        if (isset($item['color']) && $item['color'] !== 'N/A') {
                                            $parts[] = $item['color'];
                                        }
                                        if (isset($item['clarity']) && $item['clarity'] !== 'N/A') {
                                            $parts[] = $item['clarity'];
                                        }
                                        
                                        // Add diamond type
                                        $diamondType = isset($item['diamond_type_label']) ? $item['diamond_type_label'] : 
                                                      (isset($item['diamond_type']) && $item['diamond_type'] == 2 ? 'CVD' : 'Natural');
                                        $parts[] = $diamondType . ' Diamond';
                                        
                                        if (!empty($parts)) {
                                            $itemName = implode(' ', $parts);
                                        }
                                    }
                                @endphp
                                <tr class="{{ $rowClass }}">
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $itemName }}</strong>
                                        @if(isset($item['certificate_number']) && $item['certificate_number'] !== 'N/A')
                                        <div class="certificate-badge">
                                            <i class="fas fa-certificate"></i> Cert: {{ $item['certificate_number'] }}
                                        </div>
                                        @endif
                                        
                                        @if(($item['type'] ?? '') === 'diamond')
                                            @php
                                                $diamondType = isset($item['diamond_type_label']) ? $item['diamond_type_label'] : 
                                                              (isset($item['diamond_type']) && $item['diamond_type'] == 2 ? 'CVD' : 'Natural');
                                            @endphp
                                            <div style="margin-top: 5px;">
                                                <span class="badge {{ strtolower($diamondType) == 'cvd' ? 'badge-cvd' : 'badge-natural' }}">
                                                    {{ $diamondType }} Diamond
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if(($item['type'] ?? '') === 'diamond')
                                            <span class="badge bg-warning">Diamond</span>
                                        @elseif(($item['type'] ?? '') === 'jewelry')
                                            <span class="badge bg-info">Jewelry</span>
                                        @elseif(($item['type'] ?? '') === 'gift')
                                            <span class="badge bg-secondary">Gift</span>
                                        @elseif(($item['type'] ?? '') === 'combo')
                                            <span class="badge bg-success">Combo</span>
                                        @else
                                            <span class="badge bg-primary">Product</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(($item['type'] ?? '') === 'diamond')
                                            <div class="diamond-specs-grid">
                                                @if(isset($item['diamond_type_label']) && $item['diamond_type_label'] !== 'N/A')
                                                <div class="spec-item">
                                                    <i class="fas fa-gem"></i>
                                                    <span class="spec-label">Type:</span>
                                                    <span class="spec-value">{{ $item['diamond_type_label'] }}</span>
                                                </div>
                                                @elseif(isset($item['diamond_type']) && $item['diamond_type'] == 2)
                                                <div class="spec-item">
                                                    <i class="fas fa-gem"></i>
                                                    <span class="spec-label">Type:</span>
                                                    <span class="spec-value">CVD</span>
                                                </div>
                                                @else
                                                <div class="spec-item">
                                                    <i class="fas fa-gem"></i>
                                                    <span class="spec-label">Type:</span>
                                                    <span class="spec-value">Natural</span>
                                                </div>
                                                @endif
                                                
                                                @if(isset($item['shape']) && $item['shape'] !== 'N/A')
                                                <div class="spec-item">
                                                    <i class="fas fa-cube"></i>
                                                    <span class="spec-label">Shape:</span>
                                                    <span class="spec-value">{{ $item['shape'] }}</span>
                                                </div>
                                                @endif
                                                
                                                @if(isset($item['carat_weight']) && $item['carat_weight'] !== 'N/A')
                                                <div class="spec-item">
                                                    <i class="fas fa-weight"></i>
                                                    <span class="spec-label">Carat:</span>
                                                    <span class="spec-value">{{ $item['carat_weight'] }} ct</span>
                                                </div>
                                                @endif
                                                
                                                @if(isset($item['color']) && $item['color'] !== 'N/A')
                                                <div class="spec-item">
                                                    <i class="fas fa-palette"></i>
                                                    <span class="spec-label">Color:</span>
                                                    <span class="spec-value">{{ $item['color'] }}</span>
                                                </div>
                                                @endif
                                                
                                                @if(isset($item['clarity']) && $item['clarity'] !== 'N/A')
                                                <div class="spec-item">
                                                    <i class="fas fa-eye"></i>
                                                    <span class="spec-label">Clarity:</span>
                                                    <span class="spec-value">{{ $item['clarity'] }}</span>
                                                </div>
                                                @endif
                                                
                                                @if(isset($item['cut']) && $item['cut'] !== 'N/A')
                                                <div class="spec-item">
                                                    <i class="fas fa-cut"></i>
                                                    <span class="spec-label">Cut:</span>
                                                    <span class="spec-value">{{ $item['cut'] }}</span>
                                                </div>
                                                @endif
                                                
                                                @if(isset($item['certificate_company']) && $item['certificate_company'] !== 'N/A')
                                                <div class="spec-item">
                                                    <i class="fas fa-certificate"></i>
                                                    <span class="spec-label">Lab:</span>
                                                    <span class="spec-value">{{ $item['certificate_company'] }}</span>
                                                </div>
                                                @endif
                                                
                                                @if(isset($item['polish']) && $item['polish'] !== 'N/A')
                                                <div class="spec-item">
                                                    <i class="fas fa-sparkles"></i>
                                                    <span class="spec-label">Polish:</span>
                                                    <span class="spec-value">{{ $item['polish'] }}</span>
                                                </div>
                                                @endif
                                                
                                                @if(isset($item['symmetry']) && $item['symmetry'] !== 'N/A')
                                                <div class="spec-item">
                                                    <i class="fas fa-ruler-combined"></i>
                                                    <span class="spec-label">Symmetry:</span>
                                                    <span class="spec-value">{{ $item['symmetry'] }}</span>
                                                </div>
                                                @endif
                                                
                                                @if(isset($item['fluorescence']) && $item['fluorescence'] !== 'N/A')
                                                <div class="spec-item">
                                                    <i class="fas fa-lightbulb"></i>
                                                    <span class="spec-label">Fluorescence:</span>
                                                    <span class="spec-value">{{ $item['fluorescence'] }}</span>
                                                </div>
                                                @endif
                                                
                                                @if(isset($item['measurements']) && $item['measurements'] !== 'N/A')
                                                <div class="spec-item">
                                                    <i class="fas fa-ruler"></i>
                                                    <span class="spec-label">Measurements:</span>
                                                    <span class="spec-value">{{ $item['measurements'] }}</span>
                                                </div>
                                                @endif
                                            </div>
                                            
                                        @elseif(($item['type'] ?? '') === 'jewelry')
                                            <div class="item-specs">
                                                @if(isset($item['metal_color']) && $item['metal_color'] !== 'N/A')
                                                <div class="spec-item">
                                                    <i class="fas fa-ring"></i>
                                                    <span class="spec-label">Metal:</span>
                                                    <span class="spec-value">{{ $item['metal_color'] }}</span>
                                                </div>
                                                @endif
                                                
                                                @if(isset($item['size']) && $item['size'] !== 'N/A')
                                                <div class="spec-item">
                                                    <i class="fas fa-ruler"></i>
                                                    <span class="spec-label">Size:</span>
                                                    <span class="spec-value">{{ $item['size'] }}</span>
                                                </div>
                                                @endif
                                                
                                                @if(isset($item['carat']) && $item['carat'] !== 'N/A')
                                                <div class="spec-item">
                                                    <i class="fas fa-weight"></i>
                                                    <span class="spec-label">Carat:</span>
                                                    <span class="spec-value">{{ $item['carat'] }} ct</span>
                                                </div>
                                                @endif
                                            </div>
                                            
                                        @elseif(($item['type'] ?? '') === 'gift')
                                            <div class="item-specs">
                                                @if(isset($item['metal_color']) && $item['metal_color'] !== 'N/A')
                                                <div class="spec-item">
                                                    <i class="fas fa-gift"></i>
                                                    <span class="spec-label">Metal:</span>
                                                    <span class="spec-value">{{ $item['metal_color'] }}</span>
                                                </div>
                                                @endif
                                                
                                                @if(isset($item['size']) && $item['size'] !== 'N/A')
                                                <div class="spec-item">
                                                    <i class="fas fa-ruler"></i>
                                                    <span class="spec-label">Size:</span>
                                                    <span class="spec-value">{{ $item['size'] }}</span>
                                                </div>
                                                @endif
                                            </div>
                                            
                                        @elseif(($item['type'] ?? '') === 'combo')
                                            <div class="item-specs">
                                                @if(isset($item['diamond_certificate']) && $item['diamond_certificate'] !== 'N/A')
                                                <div class="spec-item">
                                                    <i class="fas fa-certificate"></i>
                                                    <span class="spec-label">Diamond Cert:</span>
                                                    <span class="spec-value">{{ $item['diamond_certificate'] }}</span>
                                                </div>
                                                @endif
                                                
                                                @if(isset($item['diamond_type_label']) && $item['diamond_type_label'] !== 'N/A')
                                                <div class="spec-item">
                                                    <i class="fas fa-gem"></i>
                                                    <span class="spec-label">Diamond Type:</span>
                                                    <span class="spec-value">{{ $item['diamond_type_label'] }}</span>
                                                </div>
                                                @endif
                                                
                                                @if(isset($item['size']) && $item['size'] !== 'N/A')
                                                <div class="spec-item">
                                                    <i class="fas fa-ruler"></i>
                                                    <span class="spec-label">Size:</span>
                                                    <span class="spec-value">{{ $item['size'] }}</span>
                                                </div>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $item['quantity'] ?? 1 }}</td>
                                    <td>${{ number_format($item['price'] ?? 0, 2) }}</td>
                                    <td><strong>${{ number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 1), 2) }}</strong></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @else
                <div class="items-section">
                    <div class="section-header">
                        <i class="fas fa-shopping-bag"></i>
                        <span>Order Items</span>
                    </div>
                    <div class="table-container">
                        <div style="text-align: center; padding: 40px;">
                            <p>No items found in this order.</p>
                        </div>
                    </div>
                </div>
                @endif
                
                <!-- Summary Section -->
                <div class="summary-section">
                    <div class="summary-row">
                        <span>Total Quantity:</span>
                        <span>{{ $order->total_quantity ?? $itemsCount }} items</span>
                    </div>
                    
                    <!-- Subtotal -->
                    @php
                        $subtotal = 0;
                        foreach ($processedItems as $item) {
                            $subtotal += ($item['price'] ?? 0) * ($item['quantity'] ?? 1);
                        }
                    @endphp
                    
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span>${{ number_format($subtotal, 2) }}</span>
                    </div>
                    
                    <!-- Only show coupon discount -->
                    @if($order->coupon_discount > 0)
                    <div class="summary-row">
                        <span>Coupon Discount ({{ $order->coupon_code ?? 'Coupon' }}):</span>
                        <span class="text-danger">-${{ number_format($order->coupon_discount, 2) }}</span>
                    </div>
                    @endif
                    
                    <!-- Shipping cost -->
                    @if($order->shipping_cost > 0)
                    <div class="summary-row">
                        <span>Shipping:</span>
                        <span>${{ number_format($order->shipping_cost, 2) }}</span>
                    </div>
                    @endif
                    
                    <!-- Grand Total -->
                    <div class="summary-row grand-total">
                        <span>Grand Total:</span>
                        <span><strong>${{ number_format($order->grand_total, 2) }}</strong></span>
                    </div>
                </div>
            </div>
            
            <div class="invoice-footer">
                <p>Thank you for choosing Dilse Jewels for your luxury jewelry needs. We appreciate your business! © {{ date('Y') }} | <a href="https://dilsejewels.com/">www.dilsejewels.com</a></p>
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
                        <option value="cancelled">Cancel</option>
                    </select>
                    
                    <div class="status-display">
                        <strong>Current:</strong>
                        <span class="badge bg-{{ 
                            $order->order_status === 'pending' ? 'secondary' : 
                            ($order->order_status === 'confirmed' ? 'primary' : 
                            ($order->order_status === 'shipped' ? 'info' : 
                            ($order->order_status === 'delivered' ? 'success' : 'danger')))
                        }}">
                            {{ ucfirst($order->order_status) }}
                        </span>
                    </div>
                    
                    <button id="updateStatusBtn" class="btn btn-primary" {{ in_array($order->order_status, ['cancelled', 'returned']) ? 'disabled' : '' }}>
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
            
            if (!newStatus) {
                showMessage('statusMessage', 'Please select a status first', 'danger');
                return;
            }
            
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