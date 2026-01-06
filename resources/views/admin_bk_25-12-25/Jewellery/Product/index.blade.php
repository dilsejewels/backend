@extends('admin.layouts.master')

@section('main_section')
<style>
    table.dataTable td.dt-control:before {
        background: #337ab7;
    }
    .sku-filter {
        max-width: 200px;
    }
</style>
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4 class="mb-0">Product Management</h4>
      <div class="d-flex gap-2">
        <input type="text" id="skuFilter" class="form-control sku-filter" placeholder="Filter by SKU...">
        <a href="{{ route('product.create') }}" class="btn btn-primary">Add New Product</a>
      </div>
    </div>
    <div class="card-body table-responsive text-nowrap">
      <table id="productTable" class="table table-hover">
        <thead class="bg-light">
          <tr>
            <th></th> 
            <th>#</th> 
            <th>Image</th>
            <th>Name</th> 
            <th>Category</th>      
            <th>SKU</th>
            <th>Status</th>      
            <th>Added Date</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</div>

<script>
    const productEditUrl = "{{ url('/admin/') }}";
    
    function format(rowData) {
        const deleteUrl = "{{ route('product.destroy', ['id' => ':id']) }}".replace(':id', rowData.products_id);
        return `
        <div class="d-flex">
            <a href="${productEditUrl}/${rowData.products_id}/edit" class="btn btn-sm btn-primary me-2">Edit</a>
            <button data-url="${deleteUrl}" class="btn btn-sm btn-danger deleteBtn">Delete</button>
        </div>
        `;
    }

    $(document).ready(function () {
        var table = $('#productTable').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: '{{ route("product.index") }}',
                data: function (d) {
                    d.sku_filter = $('#skuFilter').val();
                }
            },
            columns: [
                {
                    className: 'dt-control',
                    orderable: false,
                    searchable: false,
                    data: null,
                    defaultContent: '',
                },
                {
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'product_image',
                    name: 'product_image',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'products_name',
                    name: 'products_name',
                    render: function (data) {
                        return data?.length > 15 ? data.substring(0, 15) + '...' : data;
                    }
                },
                {
                    data: 'category_name',
                    name: 'category_name'
                },
                {
                    data: 'sku',
                    name: 'sku'
                },
                {
                    data: 'products_status',
                    name: 'products_status'
                },
                {
                    data: 'date_added',
                    name: 'date_added'
                },
            ],
            order: [[1, 'asc']],
            columnDefs: [
                {
                    targets: 2, // Image column
                    render: function (data) {
                        return data;
                    }
                },
                {
                    targets: 5, // SKU column
                    render: function (data) {
                        return data;
                    }
                },
                {
                    targets: 6, // Status
                    render: function (data) {
                        return data;
                    }
                },
                {
                    targets: 7, // Date
                    render: function (data) {
                        return data ? new Date(data).toLocaleDateString('en-GB', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric'
                        }) : '';
                    }
                }
            ]
        });

        // SKU filter functionality
        $('#skuFilter').on('keyup', function() {
            table.ajax.reload();
        });

        // Add event listener for opening and closing details
        $('#productTable tbody').on('click', 'td.dt-control', function () {
            var tr = $(this).closest('tr');
            var row = table.row(tr);

            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
            } else {
                row.child(format(row.data())).show();
                tr.addClass('shown');
            }
        });

        // Delete button click handler
        $('#productTable tbody').on('click', '.deleteBtn', function() {
            const url = $(this).data('url'); 

            if(confirm('Are you sure you want to delete this product?')) {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: {_token: '{{ csrf_token() }}'},
                    success: function(response) {
                        // Show toast notification
                        toastr[response.type](response.message);
                        
                        // Reload datatable
                        table.ajax.reload(null, false); 
                    },
                    error: function(xhr) {
                        // Show error toast
                        const message = xhr.responseJSON?.message || 'Failed to delete product';
                        toastr.error(message);
                    }
                });
            }
        });
    });
</script>

@endsection