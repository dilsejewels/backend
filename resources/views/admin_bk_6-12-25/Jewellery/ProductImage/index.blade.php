@extends('admin.layouts.master')

@section('main_section')
<style>
table.dataTable td.dt-control:before {
  background: #317cb1;
}
</style>
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4 class="mb-0">Product Images</h4>
      <button class="btn btn-primary" id="addImgBtn">New Image Add</button>
    </div>

    <div class="card-body table-responsive text-nowrap">
      <table class="table table-hover" id="imgTable">
        <thead class="bg-light">
          <tr>
            <th>Action</th>
            <th>ID</th>
            <th>Product Name</th>
            <th>Thumbnail</th>
            <th>Featured</th>
            <th>Upload Date</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="imgModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="imgForm" class="modal-content" enctype="multipart/form-data">
      @csrf
      <input type="hidden" id="img_id" name="img_id">

      <div class="modal-header">
        <h5 class="modal-title">Image Upload</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Select Product <span class="text-danger">*</span></label>
          <select name="products_id" id="products_id" class="form-select">
            <option value="">-- Select Product --</option>
            @foreach($products as $product)
              <option value="{{ $product->products_id }}">{{ $product->products_name }}</option>
            @endforeach
          </select>
          <small class="text-danger error-products_id"></small>
        </div>

        <div class="mb-3">
          <label class="form-label">Image File <span class="text-danger">*</span></label>
          <input type="file" name="image_path" id="image_path" class="form-control" accept="image/*">
          <small class="text-danger error-image_path"></small>
        </div>

        <div class="mb-3">
          <label class="form-label">Featured Image</label>
          <input type="checkbox" name="is_featured" id="is_featured" value="1">
        </div>

        <div id="previewBox" class="mt-2" style="display: none;">
          <p>Old Image Preview:</p>
          <img id="oldImgPreview" src="" class="img-thumbnail" width="100">
        </div>
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
function format(row) {
  return `
    <div class="p-2">
      <button class="btn btn-sm btn-info editImgBtn" data-id="${row.id}">
        <i class="fa fa-edit"></i> Edit
      </button>
      <button class="btn btn-sm btn-danger deleteImgBtn" data-id="${row.id}">
        <i class="fa fa-trash"></i> Delete
      </button>
    </div>
  `;
}

$(document).ready(function() {
  const imgTable = $('#imgTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: '{{ route("product-image.index") }}',
    columns: [
      {
        className: 'dt-control',
        orderable: false,
        data: null,
        defaultContent: '',
      },
      { data: 'DT_RowIndex', name: 'DT_RowIndex' },
      { 
        data: 'product.products_name', 
        name: 'product.products_name', 
        defaultContent: 'N/A' 
      },
      { data: 'thumbnail', name: 'thumbnail', orderable: false, searchable: false },
      { 
        data: 'is_featured',
        render: function (data) {
          return data == 1 ? 'Yes' : 'No';
        }
      },
      { data: 'created_at', name: 'created_at' },
    ]
  });

  $('#imgTable tbody').on('click', 'td.dt-control', function () {
    const tr = $(this).closest('tr');
    const row = imgTable.row(tr);

    if (row.child.isShown()) {
      row.child.hide();
      tr.removeClass('shown');
    } else {
      row.child(format(row.data())).show();
      tr.addClass('shown');
    }
  });

  const imgModal = new bootstrap.Modal(document.getElementById('imgModal'));

  $('#addImgBtn').click(function () {
    $('#imgForm')[0].reset();
    $('#img_id').val('');
    $('#previewBox').hide();
    $('.text-danger').text('');
    imgModal.show();
  });

  $('#imgForm').on('submit', function (e) {
    e.preventDefault();
    $('.text-danger').text('');
    let id = $('#img_id').val();
    let url = id ? '{{ url("admin/product-image/update") }}/' + id : '{{ route("product-image.store") }}';
    let formData = new FormData(this);
    if (id) formData.append('_method', 'POST');

    $.ajax({
      url: url,
      method: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        toastr.success(response.message);
        imgTable.ajax.reload();
        imgModal.hide();
      },
      error: function (xhr) {
        if (xhr.status === 422) {
          let errors = xhr.responseJSON.errors;
          $.each(errors, function (key, val) {
            $('.error-' + key).text(val[0]);
          });
        } else {
          toastr.error('Something went wrong.');
        }
      }
    });
  });

  $(document).on('click', '.editImgBtn', function () {
    let id = $(this).data('id');
    $.get('{{ url("admin/product-image/edit") }}/' + id, function (data) {
      $('#img_id').val(data.id);
      $('#products_id').val(data.products_id);
      $('#is_featured').prop('checked', data.is_featured == 1);
      $('#image_path').val('');
      $('#previewBox').show();
      $('#oldImgPreview').attr('src', '{{ asset("storage") }}/' + data.image_path);
      imgModal.show();
    });
  });

  $(document).on('click', '.deleteImgBtn', function () {
    if (!confirm('Are you sure you want to delete this image?')) return;
    let id = $(this).data('id');
    $.ajax({
      url: '{{ url("admin/product-image/delete") }}/' + id,
      method: 'DELETE',
      data: { _token: '{{ csrf_token() }}' },
      success: function (response) {
        toastr.success(response.message);
        imgTable.ajax.reload();
      },
      error: function () {
        toastr.error('Something went wrong.');
      }
    });
  });
});
</script>
@endsection
