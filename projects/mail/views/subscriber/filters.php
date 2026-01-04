<?php include __DIR__ . '/layout.php'; ?>

<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Email Filters</h2>
            <p class="text-muted mb-0">Automatically organize incoming emails</p>
        </div>
        <button class="btn btn-primary" data-toggle="modal" data-target="#addFilterModal">
            <i class="fas fa-plus mr-2"></i>Add Filter
        </button>
    </div>

    <!-- Filters List -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table" id="filtersTable">
                    <thead>
                        <tr>
                            <th width="40"><input type="checkbox" id="selectAll"></th>
                            <th>Name</th>
                            <th>Condition</th>
                            <th>Action</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th width="100">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Filters will be loaded here -->
                    </tbody>
                </table>
            </div>
            
            <!-- Empty State -->
            <div id="emptyState" class="text-center py-5" style="display: none;">
                <i class="fas fa-filter fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Email Filters</h5>
                <p class="text-muted">Create filters to automatically organize your emails</p>
                <button class="btn btn-primary" data-toggle="modal" data-target="#addFilterModal">
                    <i class="fas fa-plus mr-2"></i>Create First Filter
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Filter Modal -->
<div class="modal fade" id="addFilterModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Email Filter</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="filterForm">
                <div class="modal-body">
                    <!-- Filter Name -->
                    <div class="form-group">
                        <label for="filterName">Filter Name</label>
                        <input type="text" class="form-control" id="filterName" name="name" 
                               placeholder="e.g., Move newsletters to folder" required>
                    </div>

                    <!-- Condition Section -->
                    <h6 class="mb-3">When email matches:</h6>
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label>Field</label>
                                        <select class="form-control" name="field" id="filterField">
                                            <option value="from">From</option>
                                            <option value="to">To</option>
                                            <option value="subject">Subject</option>
                                            <option value="body">Body</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        <label>Operator</label>
                                        <select class="form-control" name="operator">
                                            <option value="contains">Contains</option>
                                            <option value="equals">Equals</option>
                                            <option value="starts_with">Starts with</option>
                                            <option value="ends_with">Ends with</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group mb-0">
                                        <label>Value</label>
                                        <input type="text" class="form-control" name="value" 
                                               placeholder="e.g., newsletter@example.com" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Section -->
                    <h6 class="mb-3">Then perform action:</h6>
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <div class="form-group">
                                <label>Action</label>
                                <select class="form-control" name="action" id="filterAction">
                                    <option value="move_to_folder">Move to folder</option>
                                    <option value="mark_as_read">Mark as read</option>
                                    <option value="star">Star message</option>
                                    <option value="delete">Delete</option>
                                    <option value="forward">Forward to email</option>
                                </select>
                            </div>

                            <!-- Folder Selection (shown for move_to_folder) -->
                            <div class="form-group" id="folderSelect">
                                <label>Target Folder</label>
                                <select class="form-control" name="target_folder_id" id="targetFolder">
                                    <option value="">Select folder...</option>
                                </select>
                            </div>

                            <!-- Email Forward (shown for forward action) -->
                            <div class="form-group" id="forwardEmail" style="display: none;">
                                <label>Forward To Email</label>
                                <input type="email" class="form-control" name="forward_to_email" 
                                       placeholder="user@example.com">
                            </div>
                        </div>
                    </div>

                    <!-- Options -->
                    <div class="form-group">
                        <label>Priority (1-100)</label>
                        <input type="number" class="form-control" name="priority" 
                               value="10" min="1" max="100">
                        <small class="form-text text-muted">
                            Lower numbers run first
                        </small>
                    </div>

                    <div class="form-group mb-0">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="stopProcessing" 
                                   name="stop_processing">
                            <label class="custom-control-label" for="stopProcessing">
                                Don't process other filters if this one matches
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>Save Filter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Load folders
function loadFolders() {
    fetch('/projects/mail/subscriber/folders')
        .then(r => r.json())
        .then(data => {
            const select = document.getElementById('targetFolder');
            select.innerHTML = '<option value="">Select folder...</option>';
            
            if (data.success && data.folders) {
                data.folders.forEach(folder => {
                    const option = document.createElement('option');
                    option.value = folder.id;
                    option.textContent = folder.folder_name;
                    select.appendChild(option);
                });
            }
        });
}

// Toggle action-specific fields
document.getElementById('filterAction').addEventListener('change', function() {
    const action = this.value;
    document.getElementById('folderSelect').style.display = 
        action === 'move_to_folder' ? 'block' : 'none';
    document.getElementById('forwardEmail').style.display = 
        action === 'forward' ? 'block' : 'none';
});

// Load filters
function loadFilters() {
    fetch('/projects/mail/subscriber/filters')
        .then(r => r.json())
        .then(data => {
            const tbody = document.querySelector('#filtersTable tbody');
            tbody.innerHTML = '';
            
            if (data.success && data.filters && data.filters.length > 0) {
                document.getElementById('emptyState').style.display = 'none';
                document.querySelector('.table-responsive').style.display = 'block';
                
                data.filters.forEach(filter => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td><input type="checkbox" class="filter-checkbox" value="${filter.id}"></td>
                        <td>${filter.name}</td>
                        <td>
                            <span class="badge badge-info">${filter.field}</span>
                            ${filter.operator} "${filter.value}"
                        </td>
                        <td><span class="badge badge-secondary">${filter.action}</span></td>
                        <td>${filter.priority}</td>
                        <td>
                            ${filter.is_active ? 
                                '<span class="badge badge-success">Active</span>' : 
                                '<span class="badge badge-secondary">Inactive</span>'}
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editFilter(${filter.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteFilter(${filter.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                document.getElementById('emptyState').style.display = 'block';
                document.querySelector('.table-responsive').style.display = 'none';
            }
        });
}

// Save filter
document.getElementById('filterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.set('is_active', 1);
    formData.set('stop_processing', document.getElementById('stopProcessing').checked ? 1 : 0);
    
    fetch('/projects/mail/subscriber/filters/save', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            $('#addFilterModal').modal('hide');
            loadFilters();
            alert('Filter saved successfully!');
            this.reset();
        } else {
            alert('Error: ' + data.message);
        }
    });
});

// Delete filter
function deleteFilter(id) {
    if (!confirm('Delete this filter?')) return;
    
    fetch(`/projects/mail/subscriber/filters/${id}/delete`, {
        method: 'POST'
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            loadFilters();
        } else {
            alert('Error: ' + data.message);
        }
    });
}

// Load on page load
loadFolders();
loadFilters();
</script>
