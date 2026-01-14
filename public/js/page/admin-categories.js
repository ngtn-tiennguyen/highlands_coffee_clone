const adminCategories = {
    modal: null,
    selectors: {
        modal: '#categoryModal',
        form: '#categoryForm',
        id: '#categoryId',
        name: '#categoryName',
        modalTitle: '#categoryModalTitle'
    },
    init() {
        const modalElement = document.querySelector(this.selectors.modal);
        if (modalElement) {
            this.modal = new bootstrap.Modal(modalElement);
        }
    },
    helpers: {
        populate(data) {
            $(adminCategories.selectors.id).val(data.id || '');
            $(adminCategories.selectors.name).val(data.name || '');
        },
        clear() {
            adminCategories.helpers.populate({});
        }
    },
    events: {
        openModal() {
            if (!adminCategories.modal) {
                adminCategories.init();
            }
            adminCategories.helpers.clear();
            $(adminCategories.selectors.modalTitle).text('Add Category');
            adminCategories.modal.show();
        },
        edit(id) {
            if (!adminCategories.modal) {
                adminCategories.init();
            }
            const url = `${BASE_URL}/admin/get-category?id=${id}`;
            $.getJSON(url, function (resp) {
                if (resp.success) {
                    adminCategories.helpers.populate(resp.category || {});
                    $(adminCategories.selectors.modalTitle).text('Edit Category');
                    adminCategories.modal.show();
                } else {
                    alert(resp.message || 'Failed to load category.');
                }
            }).fail(function(xhr, status, error) {
                console.error('Get category error:', error);
                alert('Failed to load category: ' + error);
            });
        },
        save() {
            const formData = $(adminCategories.selectors.form).serialize();
            const url = `${BASE_URL}/admin/save-category`;
            $.post(url, formData, function (resp) {
                if (resp.success) {
                    adminCategories.modal.hide();
                    adminCategories.showAlert('success', resp.message || 'Category saved successfully.');
                    adminCategories.events.loadContent();
                } else {
                    adminCategories.showAlert('danger', resp.message || 'Failed to save category.');
                }
            }, 'json').fail(function(xhr, status, error) {
                console.error('Save category error:', error);
                adminCategories.showAlert('danger', 'Failed to save category: ' + error);
            });
        },
        delete(id) {
            if (!confirm('Delete this category?')) return;
            const url = `${BASE_URL}/admin/delete-category?id=${id}`;
            $.post(url, {}, function (resp) {
                if (resp.success) {
                    adminCategories.showAlert('success', resp.message || 'Category deleted successfully.');
                    adminCategories.events.loadContent();
                } else {
                    adminCategories.showAlert('danger', resp.message || 'Failed to delete category.');
                }
            }, 'json').fail(function(xhr, status, error) {
                console.error('Delete category error:', error);
                adminCategories.showAlert('danger', 'Failed to delete category: ' + error);
            });
        },

        loadContent() {
            const contentArea = $('#admin-content-area');
            contentArea.html('<p class="text-muted">Loading...</p>');

            $.ajax({
                url: `${BASE_URL}/admin/get-content?action=categories`,
                type: 'GET',
                dataType: 'html',
                success: function(html) {
                    contentArea.html(html);
                    if (typeof bootstrap !== 'undefined') {
                        $('[data-bs-toggle="tooltip"]').each(function() { new bootstrap.Tooltip(this); });
                    }
                    adminCategories.init();
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    contentArea.html('<p class="text-danger">Error loading categories</p>');
                }
            });
        }
    },
    showAlert(type, message) {
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
    }
};

$(document).ready(() => adminCategories.init());
