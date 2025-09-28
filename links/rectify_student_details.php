<?php 
$page_title = "Rectify Student Details";
// Ensure the header is included. This should handle session checks and DB connection.
include $_SERVER['DOCUMENT_ROOT'] . '/banglar-shiksha/links/teacher_header.php'; 
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<style>
    /* Custom styles for better UX */
    .search-card { box-shadow: 0 4px 8px rgba(0,0,0,0.05); }
    #loader { display: none; } /* Initially hidden */
    .edit-btn { cursor: pointer; }
    .table-responsive { max-height: 60vh; }
</style>

<section class="dashboard-overview p-4">
    <div class="container-fluid">
        <div class="card search-card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-search me-2"></i>Find Student to Rectify</h5>
            </div>
            <div class="card-body">
                <form id="searchForm" class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label for="searchQuery" class="form-label fw-bold">Search by Name or Student ID</label>
                        <input type="text" class="form-control" id="searchQuery" placeholder="e.g., John Doe or STU-2025-1234">
                    </div>
                    <div class="col-md-4">
                        <label for="searchClass" class="form-label fw-bold">Filter by Class</label>
                        <select id="searchClass" class="form-select">
                            <option value="">All Classes</option>
                            <?php for ($i = 1; $i <= 12; $i++) echo "<option value='Class $i'>Class $i</option>"; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="reset" class="btn btn-outline-secondary w-100">Clear</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i>Search Results</h5>
            </div>
            <div class="card-body">
                <div id="loader" class="text-center my-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Fetching student data...</p>
                </div>
                <div id="studentResultsContainer" class="table-responsive">
                    <p class="text-center text-muted">Type in the search box or select a class to see student details.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="editStudentModal" tabindex="-1" aria-labelledby="editStudentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editStudentModalLabel">Edit Student Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editStudentForm">
        <div class="modal-body">
            <div id="editAlertContainer"></div>
            <input type="hidden" id="edit_student_id" name="student_id">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="edit_full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="edit_full_name" name="full_name" required>
                </div>
                <div class="col-md-6">
                    <label for="edit_dob" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="edit_dob" name="dob" required>
                </div>
                 <div class="col-md-6">
                    <label for="edit_gender" class="form-label">Gender <span class="text-danger">*</span></label>
                    <select id="edit_gender" name="gender" class="form-select" required>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="edit_contact_number" class="form-label">Contact Number <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" id="edit_contact_number" name="contact_number" required>
                </div>
                <div class="col-md-12">
                    <label for="edit_full_address" class="form-label">Address <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="edit_full_address" name="full_address" rows="3" required></textarea>
                </div>
                </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary" id="saveChangesBtn">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    let searchTimeout;

    function performSearch() {
        $('#loader').show();
        $('#studentResultsContainer').html(''); // Clear previous results

        $.ajax({
            url: 'api_search_students.php',
            type: 'GET',
            dataType: 'json',
            data: {
                query: $('#searchQuery').val(),
                class: $('#searchClass').val()
            },
            success: function(response) {
                $('#loader').hide();
                let html = '<p class="text-center text-muted">No students found matching your criteria.</p>';
                if (response.success && response.students.length > 0) {
                    html = `<table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Student ID</th>
                                        <th>Full Name</th>
                                        <th>Class</th>
                                        <th>Contact</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>`;
                    response.students.forEach(function(student) {
                        html += `<tr>
                                    <td>${student.student_id}</td>
                                    <td>${student.full_name}</td>
                                    <td>${student.current_class}</td>
                                    <td>${student.contact_number}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary edit-btn" 
                                                data-id='${student.student_id}' 
                                                data-name='${student.full_name}' 
                                                data-dob='${student.dob}'
                                                data-gender='${student.gender}'
                                                data-contact='${student.contact_number}'
                                                data-address='${student.full_address}'>
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                    </td>
                                 </tr>`;
                    });
                    html += `</tbody></table>`;
                }
                $('#studentResultsContainer').html(html);
            },
            error: function() {
                $('#loader').hide();
                $('#studentResultsContainer').html('<p class="text-center text-danger">An error occurred while fetching data.</p>');
            }
        });
    }

    // Live search on keyup with debounce
    $('#searchQuery, #searchClass').on('keyup change', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(performSearch, 500); // 500ms delay
    });
    
    $('#searchForm').on('reset', function() {
        setTimeout(performSearch, 100);
    });

    // Handle Edit button click to populate and show modal
    $('#studentResultsContainer').on('click', '.edit-btn', function() {
        const student = $(this).data();
        $('#edit_student_id').val(student.id);
        $('#edit_full_name').val(student.name);
        $('#edit_dob').val(student.dob);
        $('#edit_gender').val(student.gender);
        $('#edit_contact_number').val(student.contact);
        $('#edit_full_address').val(student.address);
        
        $('#editStudentModal').modal('show');
    });

    // Handle form submission for updating student details
    $('#editStudentForm').on('submit', function(e) {
        e.preventDefault();
        $('#saveChangesBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');
        
        $.ajax({
            url: 'api_update_student.php',
            type: 'POST',
            dataType: 'json',
            data: $(this).serialize(),
            success: function(response) {
                let alertClass = response.success ? 'success' : 'danger';
                $('#editAlertContainer').html(`<div class="alert alert-${alertClass}">${response.message}</div>`);
                if(response.success) {
                    setTimeout(() => {
                        $('#editStudentModal').modal('hide');
                        performSearch(); // Refresh the table
                    }, 1500);
                }
            },
            error: function() {
                 $('#editAlertContainer').html('<div class="alert alert-danger">An unknown error occurred.</div>');
            },
            complete: function() {
                $('#saveChangesBtn').prop('disabled', false).html('Save Changes');
                 // Clear alert after a few seconds
                setTimeout(() => $('#editAlertContainer').html(''), 5000);
            }
        });
    });
});
</script>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/banglar-shiksha/links/teacher_footer.php'; ?>