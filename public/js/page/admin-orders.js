const adminOrders = {
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
    events: {
        openModal: function() {
            $('#orderForm')[0].reset();
            $('#orderId').val('');
            $('#orderModalTitle').text('Add Order');
            const modalElement = $('#orderModal')[0];
            const modalInstance = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
            modalInstance.show();
        },

        edit: function(orderId) {
            const url = `${BASE_URL}/admin/get-order?id=${orderId}`;
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        const order = data.order;
                        $('#orderId').val(order.id);
                        $('#orderCustomer').val(order.customer_id);
                        $('#orderStatus').val(order.status);
                        $('#orderModalTitle').text('Edit Order');
                        const modalElement = $('#orderModal')[0];
                        const modalInstance = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                        modalInstance.show();
                    } else {
                        alert(data.message || 'Order not found');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('Error loading order');
                }
            });
        },

        save: function() {
            const form = $('#orderForm')[0];
            const formData = new FormData(form);
            const url = `${BASE_URL}/admin/save-order`;

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        adminOrders.showAlert('success', data.message || 'Order saved successfully');
                        const modalElement = $('#orderModal')[0];
                        const modalInstance = bootstrap.Modal.getInstance(modalElement);
                        if (modalInstance) modalInstance.hide();
                        adminOrders.events.loadContent();
                    } else {
                        adminOrders.showAlert('danger', data.message || 'Error saving order');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    adminOrders.showAlert('danger', 'Error saving order: ' + error);
                }
            });
        },

        delete: function(orderId) {
            if (!confirm('Delete this order?')) return;
            const url = `${BASE_URL}/admin/delete-order?id=${orderId}`;
            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        adminOrders.showAlert('success', data.message || 'Order deleted successfully');
                        adminOrders.events.loadContent();
                    } else {
                        adminOrders.showAlert('danger', data.message || 'Error deleting order');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    adminOrders.showAlert('danger', 'Error deleting order: ' + error);
                }
            });
        },

        loadContent: function() {
            const contentArea = $('#admin-content-area');
            contentArea.html('<p class="text-muted">Loading...</p>');

            $.ajax({
                url: `${BASE_URL}/admin/get-content?action=orders`,
                type: 'GET',
                dataType: 'html',
                success: function(html) {
                    contentArea.html(html);
                    if (typeof bootstrap !== 'undefined') {
                        $('[data-bs-toggle="tooltip"]').each(function() { new bootstrap.Tooltip(this); });
                    }
                    adminSidebar?.events?.attachOrderStatusListeners?.();
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    contentArea.html('<p class="text-danger">Error loading orders</p>');
                }
            });
        }
    }
};

$(document).ready(function() {
});
