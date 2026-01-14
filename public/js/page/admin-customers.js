const adminCustomers = {
    showAlert: function(type, message) {
        const alertHtml = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
        const container = $('#alerts-container');
        let alertElem;
        if (container.length) {
            container.html(alertHtml);
            alertElem = container.find('.alert');
        } else {
            $('body').prepend(alertHtml);
            alertElem = $('body').find('.alert').first();
        }
        setTimeout(function() {
            alertElem.alert('close');
        }, 2000);
    },
    init: function() {
    },
    
    events: {
        openModal: function() {
            $('#customerForm')[0].reset();
            $('#customerId').val('');
            $('#customerModalTitle').text('Add Customer');
            const modalElement = $('#customerModal')[0];
            const modalInstance = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
            modalInstance.show();
        },

        edit: function(customerId) {
            const url = `${BASE_URL}/admin/get-customer?id=${customerId}`;
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        const customer = data.customer;
                        $('#customerId').val(customer.id);
                        $('#customerFirstName').val(customer.first_name);
                        $('#customerLastName').val(customer.last_name);
                        $('#customerEmail').val(customer.email);
                        $('#customerPhone').val(customer.phone || '');
                        $('#customerCity').val(customer.city || '');
                        $('#customerAddress').val(customer.address || '');
                        $('#customerModalTitle').text('Edit Customer');
                        const modalElement = $('#customerModal')[0];
                        const modalInstance = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                        modalInstance.show();
                    } else {
                        alert('Error loading customer data: ' + (data.message || 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    console.error('Status:', status);
                    console.error('Response:', xhr.responseText);
                    alert('Error loading customer: ' + (xhr.responseText || error));
                }
            });
        },

        save: function() {
            const form = $('#customerForm')[0];
            const customerId = $('#customerId').val();
            const formData = new FormData(form);
            const url = `${BASE_URL}/admin/save-customer`;
            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        adminCustomers.showAlert('success', data.message || 'Customer saved successfully');
                        const modalElement = $('#customerModal')[0];
                        const modalInstance = bootstrap.Modal.getInstance(modalElement);
                        if (modalInstance) {
                            modalInstance.hide();
                        }
                        adminCustomers.events.loadContent();
                    } else {
                        adminCustomers.showAlert('danger', data.message || 'Error saving customer');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    adminCustomers.showAlert('danger', 'Error saving customer: ' + error);
                }
            });
        },

        delete: function(customerId) {
            if (!confirm('Delete this customer?')) return;
            const url = `${BASE_URL}/admin/delete-customer?id=${customerId}`;
            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        adminCustomers.showAlert('success', data.message || 'Customer deleted successfully');
                        adminCustomers.events.loadContent();
                    } else {
                        adminCustomers.showAlert('danger', data.message || 'Error deleting customer');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    adminCustomers.showAlert('danger', 'Error deleting customer: ' + error);
                }
            });
        },

        loadContent: function() {
            const contentArea = $('#admin-content-area');
            contentArea.html('<p class="text-muted">Loading...</p>');
            
            $.ajax({
                url: `${BASE_URL}/admin/get-content?action=customers`,
                type: 'GET',
                dataType: 'html',
                success: function(html) {
                    contentArea.html(html);
                    if (typeof bootstrap !== 'undefined') {
                        $('[data-bs-toggle="tooltip"]').each(function() {
                            new bootstrap.Tooltip(this);
                        });
                    }
                    adminCustomers.events.initModalCleanup();
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    contentArea.html('<p class="text-danger">Error loading customers</p>');
                }
            });
        },

        initModalCleanup: function() {
            const modalElement = $('#customerModal')[0];
            if (modalElement) {
                $(modalElement).off('hidden.bs.modal').on('hidden.bs.modal', function() {
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open').css('overflow', '');
                    $('body').css('padding-right', '');
                });
            }
        }
    }
};
