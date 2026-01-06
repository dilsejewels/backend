@extends('admin.layouts.master')

@section('main_section')
    <div class="container py-4">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4>Diamond Master</h4>
                <a href="{{ route('diamond-master.create') }}" class="btn btn-primary">
                    Add New Diamond
                </a>
            </div>
            <div class="card-body">
                <table id="diamondTable" class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Vendor</th>
                            <th>Shape</th>
                            <th>Color</th>
                            <th>Clarity</th>
                            <th>Carat</th>
                            <th>Price/Carat</th>
                            <th>Date added</th>
                            <th>Date Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        $(function() {
            $('#diamondTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('diamond-master.data') }}',
                    type: 'GET'
                },
                 responsive: {
            details: {
                type: 'inline' 
            }
        },
        scrollX: false,
                columns: [{
                        data: 'diamondid'
                    },
                    {
                        data: 'vendor.vendor_name',
                        defaultContent: '—'
                    },
                    {
                        data: 'shape.name',
                        defaultContent: '—'
                    },
                    {
                        data: 'color.name',
                        defaultContent: '—'
                    },
                    {
                        data: 'clarity.name',
                        defaultContent: '—'
                    },
                    {
                        data: 'carat_weight'
                    },
                    {
                        data: 'price_per_carat'
                    },
                    {
                        data: 'date_added'
                    },
                     {
                        data: 'date_updated'
                    },
                    {
                        
                        data: 'diamondid',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                    <a href="/api/admin/diamond-master/${data}/edit" class="btn btn-sm btn-info"><i class="fa fa-edit"></i></a>
                    <button class="btn btn-sm btn-danger deleteBtn" data-id="${data}"><i class="fa fa-trash"></i></button>
                `;
                        }
                    }
                ]
            });


            // Delete handler
            $(document).ready(function() {
    let deleteId = null;

    // Show modal when delete button is clicked
    $(document).on('click', '.deleteBtn', function() {
        deleteId = $(this).data('id');
        $('.popup-modal.remove-modal').fadeIn(); // Show custom popup
    });

    // Cancel/Delete modal close
    $(document).on('click', '.close-pop', function() {
        $('.popup-modal.remove-modal').fadeOut();
    });

    // Confirm delete from modal
    $('#confirmDelete').on('click', function() {
        if (!deleteId) return;

        $.post(`/api/admin/diamond-master/${deleteId}`, {
            _method: 'DELETE',
            _token: '{{ csrf_token() }}'
        }).done(() => {
            $('#diamondTable').DataTable().ajax.reload(); // Refresh table
            toastr.success('Record deleted successfully!');
        }).fail(() => {
            toastr.error('Failed to delete the record.');
        }).always(() => {
            $('.popup-modal.remove-modal').fadeOut(); // Close modal always
        });
    });
});

        });
    </script>
@endsection
