@extends('admin.layouts.master')

@section('main_section')
<style>
  .blog-image {
    width: 80px;
    height: 60px;
    object-fit: cover;
    border-radius: 4px;
  }
  .badge-category {
    font-size: 0.75em;
  }
  .image-preview {
    max-width: 200px;
    max-height: 150px;
    object-fit: cover;
    border-radius: 8px;
    margin-top: 10px;
  }
  .tox-tinymce {
    border-radius: 8px !important;
  }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h4 class="mb-0">Blog Management</h4>
      <div>
        <button class="btn btn-primary" id="addBlogBtn">
          <i class="fa fa-plus me-1"></i> Add New Blog
        </button>
      </div>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover" id="blogsTable">
          <thead class="bg-light">
            <tr>
              <th>ID</th>
              <th>Image</th>
              <th>Title</th>
              <th>Category</th>
              <th>Writer</th>
              <th>Read Time</th>
              <th>Publish Date</th>
              <th>Created At</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Blog Modal -->
<div class="modal fade" id="blogModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form id="blogForm" class="modal-content" enctype="multipart/form-data">
      @csrf
      <input type="hidden" id="blog_id" name="id">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Add New Blog</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Category *</label>
            <select name="category" id="category" class="form-select" required>
              <option value="">Select Category</option>
              <option value="Engagement Rings">Engagement Rings</option>
              <option value="Gemstone Insights">Gemstone Insights</option>
              <option value="Wedding Bands">Wedding Bands</option>
              <option value="Metal">Metal</option>
              <option value="Buying Guides">Buying Guides</option>
              <option value="Diamond">Diamond</option>
              <option value="Jewelry">Jewelry</option>
            </select>
            <small class="text-danger error-category"></small>
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label">Publish Date</label>
            <input type="date" name="publish_date" id="publish_date" class="form-control">
            <small class="text-danger error-publish_date"></small>
          </div>

          <div class="col-12 mb-3">
            <label class="form-label">Title *</label>
            <input type="text" name="title" id="title" class="form-control" placeholder="Enter blog title" required>
            <small class="text-danger error-title"></small>
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label">Writer Name</label>
            <input type="text" name="writer_name" id="writer_name" class="form-control" placeholder="Author name">
            <small class="text-danger error-writer_name"></small>
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label">Read Time</label>
            <input type="text" name="read_time" id="read_time" class="form-control" placeholder="e.g., 5 min read">
            <small class="text-danger error-read_time"></small>
          </div>

          <div class="col-12 mb-3">
            <label class="form-label">Blog Image</label>
            <input type="file" name="image" id="image" class="form-control" accept="image/*">
            <small class="text-muted">Recommended size: 800x400px, Max: 2MB</small>
            <div id="imagePreview" class="mt-2"></div>
            <small class="text-danger error-image"></small>
          </div>

          <div class="col-12 mb-3">
            <label class="form-label">Content *</label>
            <textarea name="paragraph" id="paragraph" class="form-control" rows="8" placeholder="Write your blog content here..." ></textarea>
            <small class="text-danger error-paragraph"></small>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">
          <i class="fa fa-save me-1"></i> Save Blog
        </button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Include TinyMCE -->
<!-- Local TinyMCE -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/8.1.2/tinymce.min.js"></script>

<script>
tinymce.init({
  selector: 'textarea', 
  license_key: 'gpl',        
  plugins: 'lists link image code',
  toolbar: 'undo redo | styles | bold italic | alignleft aligncenter alignright | code'
});
</script>
<script>
    // Reusable TinyMCE initialization
    function initializeTinyMCE(selector) {
        tinymce.init({
            selector: selector,
            width: '100%',
            height: 400,
            plugins: [
                'advlist', 'autolink', 'link', 'image', 'lists', 'charmap', 'preview', 'anchor',
                'pagebreak', 'searchreplace', 'wordcount', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'emoticons', 'template', 'codesample'
            ],
            toolbar: 'undo redo | styles | bold italic underline | alignleft aligncenter alignright alignjustify |' +
                'bullist numlist outdent indent | link image | print preview media fullscreen | ' +
                'forecolor backcolor emoticons',
            menu: {
                favs: {
                    title: 'menu',
                    items: 'code visualaid | searchreplace | emoticons'
                }
            },
            menubar: 'favs file edit view insert format tools table',
            content_style: 'body { font-family: Helvetica, Arial, sans-serif; font-size: 16px; }',
            setup: function (editor) {
                editorInstance = editor;
            }
        });
    }

    // Initialize TinyMCE when modal opens
    $('#blogModal').on('shown.bs.modal', function () {
        initializeTinyMCE('textarea#paragraph');
    });

    // Destroy TinyMCE when modal closes
    $('#blogModal').on('hidden.bs.modal', function () {
        if (editorInstance) {
            editorInstance.destroy();
            editorInstance = null;
        }
    });
</script>


<script>
// Global modal variables
let blogModalInstance = null;
let editorInstance = null;

$(document).ready(function() {
  // Initialize modal
  blogModalInstance = new bootstrap.Modal(document.getElementById('blogModal'));

  // Initialize DataTable
  const table = $('#blogsTable').DataTable({
    processing: true,
    serverSide: false,
    ajax: {
      url: "{{ route('admin.blogs.fetch') }}",
      dataSrc: 'data'
    },
    columns: [
      { data: 'id' },
      {
        data: 'image',
        render: function(data) {
          if (data) {
            return `<img src="{{ url('storage/blogs') }}/${data}" class="blog-image" alt="Blog Image">`;
          }
          return '<span class="text-muted">No Image</span>';
        }
      },
      {
        data: 'title',
        render: function(data, type, row) {
          return `<strong>${data}</strong>`;
        }
      },
      {
        data: 'category',
        render: function(data) {
          return `<span class="badge bg-primary badge-category">${data}</span>`;
        }
      },
      {
        data: 'writer_name',
        render: function(data) {
          return data || '<span class="text-muted">N/A</span>';
        }
      },
      {
        data: 'read_time',
        render: function(data) {
          return data || '<span class="text-muted">N/A</span>';
        }
      },
      {
        data: 'publish_date',
        render: function(data) {
          return data ? new Date(data).toLocaleDateString() : '<span class="text-muted">N/A</span>';
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
              <button class="btn btn-sm btn-warning edit-btn" data-id="${data.id}" title="Edit">
                <i class="fa fa-edit"></i>
              </button>
              <button class="btn btn-sm btn-danger delete-btn" data-id="${data.id}" title="Delete">
                <i class="fa fa-trash"></i>
              </button>
            </div>
          `;
        }
      }
    ],
    order: [[0, 'desc']]
  });

  // Add new blog
  $('#addBlogBtn').click(function() {
    resetBlogForm();
    $('#modalTitle').text('Add New Blog');
    blogModalInstance.show();
  });

  // Image preview
  $('#image').change(function() {
    const file = this.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function(e) {
        $('#imagePreview').html(`<img src="${e.target.result}" class="image-preview" alt="Preview">`);
      }
      reader.readAsDataURL(file);
    } else {
      $('#imagePreview').html('');
    }
  });

  // Form submission
  $('#blogForm').submit(function(e) {
    e.preventDefault();
    submitBlogForm();
  });

  // Event delegates for action buttons
  $(document).on('click', '.edit-btn', function() {
    const blogId = $(this).data('id');
    editBlog(blogId);
  });

  $(document).on('click', '.delete-btn', function() {
    const blogId = $(this).data('id');
    deleteBlog(blogId);
  });

  // Initialize TinyMCE when modal is shown
  $('#blogModal').on('shown.bs.modal', function() {
    initTinyMCE();
  });

  // Remove TinyMCE when modal is hidden
  $('#blogModal').on('hidden.bs.modal', function() {
    if (editorInstance) {
      editorInstance.destroy();
      editorInstance = null;
    }
  });
});

function initTinyMCE() {
  tinymce.init({
    selector: '#paragraph',
    plugins: 'advlist autolink lists link image charmap preview anchor pagebreak code',
    toolbar_mode: 'floating',
    toolbar: 'undo redo | styles | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image code | preview',
    height: 400,
    menubar: false,
    statusbar: true,
    branding: false,
    image_advtab: true,
    image_title: true,
    automatic_uploads: true,
    file_picker_types: 'image',
    paste_data_images: true,
    content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; }',
    setup: function(editor) {
      editorInstance = editor;
    }
  });
}

function resetBlogForm() {
  $('#blogForm')[0].reset();
  $('#blog_id').val('');
  $('#imagePreview').html('');
  $('.text-danger').text('');
  $('.form-control, .form-select').removeClass('is-invalid');
  
  // Reset TinyMCE content if editor exists
  if (editorInstance) {
    editorInstance.setContent('');
  }
}

function submitBlogForm() {
  // Get content from TinyMCE editor
  if (editorInstance) {
    const content = editorInstance.getContent();
    $('#paragraph').val(content);
  }

  const formData = new FormData(document.getElementById('blogForm'));
  const blogId = $('#blog_id').val();
  
const url = blogId 
  ? "{{ route('admin.blogs.update', ':id') }}".replace(':id', blogId)
  : "{{ route('admin.blogs.store') }}";

  const $btn = $('#blogForm').find('button[type="submit"]');
  $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Saving...');

  // Clear previous errors
  $('.text-danger').text('');
  $('.form-control, .form-select').removeClass('is-invalid');

  $.ajax({
    url: url,
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    success: function(response) {
      if (response.success) {
        blogModalInstance.hide();
        $('#blogsTable').DataTable().ajax.reload();
        toastr.success(response.message);
      }
    },
    error: function(xhr) {
      console.log('Error:', xhr);
      if (xhr.status === 422) {
        const errors = xhr.responseJSON.errors;
        $.each(errors, function(key, value) {
          $(`.error-${key}`).text(value[0]);
          $(`[name="${key}"]`).addClass('is-invalid');
        });
      } else {
        const errorMessage = xhr.responseJSON?.message || 'Something went wrong';
        toastr.error(errorMessage);
      }
    },
    complete: function() {
      $btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save Blog');
    }
  });
}

function editBlog(id) {
  $.ajax({
    url: "{{ route('admin.blogs.show', ':id') }}".replace(':id', id),
    type: 'GET',
    success: function(response) {
      if (response.success && response.data) {
        const blog = response.data;
        
        // Fill form with blog data
        $('#blog_id').val(blog.id);
        $('#modalTitle').text('Edit Blog');
        $('#category').val(blog.category);
        $('#title').val(blog.title);
        $('#writer_name').val(blog.writer_name);
        $('#read_time').val(blog.read_time);
        $('#publish_date').val(blog.publish_date);
        
        // Show current image
        if (blog.image) {
          $('#imagePreview').html(`
            <img src="{{ url('storage/blogs') }}/${blog.image}" class="image-preview" alt="Current Image">
            <br><small class="text-muted">Current image - ${blog.image}</small>
          `);
        } else {
          $('#imagePreview').html('');
        }
        
        // Clear errors
        $('.text-danger').text('');
        $('.form-control, .form-select').removeClass('is-invalid');
        
        // Show modal
        blogModalInstance.show();
        
        // Set TinyMCE content after a short delay to ensure editor is initialized
        setTimeout(() => {
          if (editorInstance) {
            editorInstance.setContent(blog.paragraph || '');
          }
        }, 500);
      } else {
        toastr.error('Blog not found or invalid response');
      }
    },
    error: function(xhr) {
      console.log('Edit Error:', xhr);
      const errorMessage = xhr.responseJSON?.message || 'Error loading blog data';
      toastr.error(errorMessage);
    }
  });
}

function deleteBlog(id) {
  if (!confirm('Are you sure you want to delete this blog? This action cannot be undone.')) {
    return;
  }

  $.ajax({
    url: "{{ route('admin.blogs.delete', ':id') }}".replace(':id', id),
    type: 'POST', 
    data: {
      _method: 'DELETE',
      _token: '{{ csrf_token() }}'
    },
    success: function(response) {
      if (response.success) {
        toastr.success(response.message);
        $('#blogsTable').DataTable().ajax.reload(null, false); // pagination reset न हो
      } else {
        toastr.error(response.message || 'Error deleting blog');
      }
    },
    error: function(xhr) {
      console.log('Delete Error:', xhr);
      const errorMessage = xhr.responseJSON?.message || 'Error deleting blog';
      toastr.error(errorMessage);
    }
  });
}


</script>
@endsection