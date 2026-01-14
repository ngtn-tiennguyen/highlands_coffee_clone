const adminProducts = {
    init: function() {
    },
    
    events: {
        openModal: function() {
            $('#productForm')[0].reset();
            $('#productId').val('');
            $('#productImageFile').val('');
            $('#productExistingImage').val('');
            $('#productImagePreviewWrapper').hide();
            $('#productImagePreview').attr('src', '');
            $('#productModalTitle').text('Add Product');
            const modalElement = $('#productModal')[0];
            const modalInstance = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
            modalInstance.show();
            adminProducts.events.bindImagePreview();
        },

        edit: function(productId) {
            const url = `${BASE_URL}/admin/get-product?id=${productId}`;
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        const product = data.product;
                        $('#productId').val(product.id);
                        $('#productName').val(product.name);
                        $('#productDescription').val(product.descriptions || '');
                        $('#productPrice').val(product.price);
                        $('#productCategory').val(product.category_id).trigger('change');
                        $('#productImageFile').val('');
                        $('#productExistingImage').val(product.image || '');
                        if (product.image) {
                            let imgPath = product.image.replace(/^\/+/,'')
                            if (imgPath.indexOf('/') === -1) {
                                imgPath = 'products/' + imgPath;
                            }
                            const imgSrc = `${BASE_URL}/images/${imgPath}`;
                            $('#productImagePreview').attr('src', imgSrc);
                            $('#productImagePreviewWrapper').show();
                        } else {
                            $('#productImagePreviewWrapper').hide();
                        }
                        $('#productModalTitle').text('Edit Product');
                        const modalElement = $('#productModal')[0];
                        const modalInstance = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                        modalInstance.show();
                        adminProducts.events.bindImagePreview();
                    } else {
                        alert('Error loading product data: ' + (data.message || 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('Error loading product: ' + error);
                }
            });
        },

        bindImagePreview: function() {
            $('#productImageFile').off('change').on('change', function(e) {
                const file = e.target.files && e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(ev) {
                        $('#productImagePreview').attr('src', ev.target.result);
                        $('#productImagePreviewWrapper').show();
                    };
                    reader.readAsDataURL(file);
                }
            });
        },

        save: function() {
            const form = $('#productForm')[0];
            const productId = $('#productId').val();
            const formData = new FormData(form);
            const url = `${BASE_URL}/admin/save-product`;

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        adminProducts.showAlert('success', data.message || 'Product saved successfully');
                        const modalElement = $('#productModal')[0];
                        const modalInstance = bootstrap.Modal.getInstance(modalElement);
                        if (modalInstance) {
                            modalInstance.hide();
                        }
                        adminProducts.events.loadContent();
                    } else {
                        adminProducts.showAlert('danger', data.message || 'Error saving product');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    adminProducts.showAlert('danger', 'Error saving product: ' + error);
                }
            });
        },

        delete: function(productId) {
            if (confirm('Are you sure you want to delete this product?')) {
                const url = `${BASE_URL}/admin/delete-product?id=${productId}`;
                
                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    success: function(data) {
                        if (data.success) {
                            adminProducts.showAlert('success', data.message || 'Product deleted successfully');
                            adminProducts.events.loadContent();
                        } else {
                            adminProducts.showAlert('danger', data.message || 'Error deleting product');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        adminProducts.showAlert('danger', 'Error deleting product: ' + error);
                    }
                });
            }
        },

        loadContent: function() {
            const contentArea = $('#admin-content-area');
            contentArea.html('<p class="text-muted">Loading...</p>');
            
            $.ajax({
                url: `${BASE_URL}/admin/get-content?action=products`,
                type: 'GET',
                dataType: 'html',
                success: function(html) {
                    contentArea.html(html);
                    if (typeof bootstrap !== 'undefined') {
                        $('[data-bs-toggle="tooltip"]').each(function() {
                            new bootstrap.Tooltip(this);
                        });
                    }
                    adminProducts.events.initModalCleanup();
                    adminProducts.events.bindImagePreview();
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    contentArea.html('<p class="text-danger">Error loading products</p>');
                }
            });
        },

        initModalCleanup: function() {
            const modalElement = $('#productModal')[0];
            if (modalElement) {
                $(modalElement).off('hidden.bs.modal').on('hidden.bs.modal', function() {
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open').css('overflow', '');
                    $('body').css('padding-right', '');
                });
            }
        }
    },

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
    }
};
