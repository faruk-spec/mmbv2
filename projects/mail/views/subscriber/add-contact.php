<?php
// Add Contact View
$pageTitle = 'Add Contact';
include 'layout.php';
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-user-plus"></i> Add Contact</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/projects/mail/subscriber/dashboard">Home</a></li>
                        <li class="breadcrumb-item"><a href="/projects/mail/contacts">Contacts</a></li>
                        <li class="breadcrumb-item active">Add</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Contact Information</h3>
                        </div>
                        <form method="POST" action="/projects/mail/contacts/store">
                            <?= csrf_field() ?>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" required 
                                           placeholder="John Doe" autofocus>
                                </div>

                                <div class="form-group">
                                    <label>Email Address <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" required 
                                           placeholder="john@example.com">
                                </div>

                                <div class="form-group">
                                    <label>Company</label>
                                    <input type="text" name="company" class="form-control" 
                                           placeholder="Company Name">
                                </div>

                                <div class="form-group">
                                    <label>Phone</label>
                                    <input type="text" name="phone" class="form-control" 
                                           placeholder="+1 234 567 8900">
                                </div>

                                <div class="form-group">
                                    <label>Notes</label>
                                    <textarea name="notes" class="form-control" rows="4" 
                                              placeholder="Additional notes about this contact..."></textarea>
                                </div>
                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Contact
                                </button>
                                <a href="/projects/mail/contacts" class="btn btn-default">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title">Tips</h3>
                        </div>
                        <div class="card-body">
                            <p><strong>Contact Management:</strong></p>
                            <ul class="pl-3">
                                <li>Use descriptive names for easy identification</li>
                                <li>Email addresses must be unique</li>
                                <li>Add company info for business contacts</li>
                                <li>Notes field is great for additional details</li>
                            </ul>

                            <hr>

                            <p><strong>Bulk Import:</strong></p>
                            <p class="small text-muted">
                                You can import multiple contacts from a CSV file.
                                Go to the Contacts page and click "Import CSV".
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include 'footer.php'; ?>
