<?php 
$page_title = "View Student List";
include $_SERVER['DOCUMENT_ROOT'] . '/banglar-shiksha/links/teacher_header.php';
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<style>
    .search-card { box-shadow: 0 4px 8px rgba(0,0,0,0.05); }
    #loader { display: none; }
    .action-buttons .btn, .action-buttons .btn-group { margin-right: 5px; }
    .modal-body dt { font-weight: 600; color: var(--primary-color); }
    .modal-body dd { margin-left: 1.5rem; }
</style>

<section class="dashboard-overview p-4">
    <div class="container-fluid">
        <div class="card search-card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Student List</h5>
            </div>
            <div class="card-body">
                <form id="searchForm" class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label for="searchQuery" class="form-label fw-bold">Search by Name or Student ID</label>
                        <input type="text" class="form-control" id="searchQuery" placeholder="e.g., Priya Das or STU-2025-5678">
                    </div>
                    <div class="col-md-4">
                        <label for="searchClass" class="form-label fw-bold">Filter by Class</label>
                        <select id="searchClass" class="form-select">
                            <option value="">All Classes</option>
                            <?php for ($i = 1; $i <= 12; $i++) echo "<option value='Class $i'>Class $i</option>"; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="reset" class="btn btn-outline-secondary w-100">Clear Filters</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i>Student List</h5>
                <span id="studentCount" class="badge bg-secondary"></span>
            </div>
            <div class="card-body">
                <div id="loader" class="text-center my-5">
                    <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
                    <p class="mt-2">Fetching student data...</p>
                </div>
                <div id="studentResultsContainer" class="table-responsive">
                    </div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="viewStudentModal" tabindex="-1" aria-labelledby="viewStudentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewStudentModalLabel">Student Profile</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
            <div class="col-md-12">
                <h4 id="modal_student_name" class="text-primary"></h4>
                <p class="lead">Student ID: <span id="modal_student_id" class="fw-bold"></span></p>
                <hr>
                <dl class="row">
                    <dt class="col-sm-4">Date of Birth</dt>
                    <dd class="col-sm-8" id="modal_dob"></dd>

                    <dt class="col-sm-4">Gender</dt>
                    <dd class="col-sm-8" id="modal_gender"></dd>
                    
                    <dt class="col-sm-4">Current Class</dt>
                    <dd class="col-sm-8" id="modal_class"></dd>

                    <dt class="col-sm-4">Father's Name</dt>
                    <dd class="col-sm-8" id="modal_father_name"></dd>

                    <dt class="col-sm-4">Contact Number</dt>
                    <dd class="col-sm-8" id="modal_contact"></dd>
                    
                    <dt class="col-sm-4">Address</dt>
                    <dd class="col-sm-8" id="modal_address"></dd>
                </dl>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
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
        $('#studentResultsContainer').hide();
        $('#studentCount').text('');

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
                $('#studentResultsContainer').show();
                let html = '<p class="text-center text-muted">No students found matching your criteria.</p>';
                if (response.success && response.students.length > 0) {
                    $('#studentCount').text(response.students.length + ' student(s) found');
                    html = `<table class="table table-striped table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Student ID</th>
                                        <th>Name</th>
                                        <th>Class</th>
                                        <th>Father's Name</th>
                                        <th>Contact</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>`;
                    response.students.forEach(function(student) {
                        html += `<tr>
                                    <td>${student.student_id}</td>
                                    <td>${student.full_name}</td>
                                    <td>${student.current_class}</td>
                                    <td>${student.father_name}</td>
                                    <td>${student.contact_number}</td>
                                    <td class="text-center action-buttons">
                                        <div class="btn-group" role="group">
                                            <button title="View Details" class="btn btn-sm btn-info view-btn" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#viewStudentModal"
                                                    data-id='${student.student_id}' 
                                                    data-name='${student.full_name}' 
                                                    data-dob='${student.dob}'
                                                    data-gender='${student.gender}'
                                                    data-class='${student.current_class}'
                                                    data-father='${student.father_name}'
                                                    data-contact='${student.contact_number}'
                                                    data-address='${student.full_address}'>
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a href="rectify_student_details.php?id=${student.student_id}" title="Edit Student" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                                            <a href="promote_detain_student.php?id=${student.student_id}" title="Promote/Detain" class="btn btn-sm btn-success"><i class="fas fa-user-check"></i></a>
                                            <a href="student_transfer.php?id=${student.student_id}" title="Transfer Student" class="btn btn-sm btn-warning"><i class="fas fa-exchange-alt"></i></a>
                                        </div>
                                    </td>
                                 </tr>`;
                    });
                    html += `</tbody></table>`;
                }
                $('#studentResultsContainer').html(html);
            },
            error: function() {
                $('#loader').hide();
                $('#studentResultsContainer').show().html('<p class="text-center text-danger">An error occurred while fetching data.</p>');
            }
        });
    }

    // Live search on keyup/change with a short delay (debounce)
    $('#searchQuery, #searchClass').on('keyup change', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(performSearch, 500); // 500ms delay
    });
    
    // Clear filters and re-run search
    $('#searchForm').on('reset', function() {
        setTimeout(performSearch, 100);
    });

    // Handle View button click to populate and show modal
    $('#viewStudentModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var student = button.data(); // Extract info from data-* attributes
        
        var modal = $(this);
        modal.find('#modal_student_name').text(student.name);
        modal.find('#modal_student_id').text(student.id);
        modal.find('#modal_dob').text(student.dob);
        modal.find('#modal_gender').text(student.gender);
        modal.find('#modal_class').text(student.class);
        modal.find('#modal_father_name').text(student.father);
        modal.find('#modal_contact').text(student.contact);
        modal.find('#modal_address').text(student.address);
    });

    // Initial load of all students
    performSearch();
});
</script>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/banglar-shiksha/links/teacher_footer.php'; ?>