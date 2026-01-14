const handleSystems = {
  activeIndex: {},
  reviewIndex: 0,
  reviewPosition: 0,
  cart: [],
  init: function () {
    this.events.init();
    this.events.loadPage();
    this.events.carouselLoop();
  },
  events: {
    init: function () {
      $("#products .arrow-action").on("click", function () {
        let direction = $(this).data("direction");
        let id = $(this).data("id");
        handleSystems.events.processArrowProducts(id, direction);
      });

      $(".pagination .item").on("click", function (event) {
        let id = $(this).closest('.pagination').attr('data-index');
        event.preventDefault();
        let pageIndex = $(this).parent().children('.item').index(this);
        handleSystems.events.scrollToItem(id, pageIndex);
      });

      $("#pagination-products .item").on("click", function (event) {
        event.preventDefault();
        handleSystems.events.scrollToItem($(this).index());
      });

      if ($('#products .category').length > 0) {
        $('#products .category').each(function() {
          let categoryId = $(this).attr('id');
          if (categoryId) {
            if (typeof handleSystems.activeIndex[categoryId] === 'undefined') {
              handleSystems.activeIndex[categoryId] = 0;
            }
            handleSystems.events.updateArrowProduct(categoryId);
            handleSystems.events.activeItemProduct(categoryId, handleSystems.activeIndex[categoryId]);
          }
        });
      }

      $("#products .order-item-btn").on("click", function () {
        let product = $(this).closest('.item-product');
        let productData = {
          id: product.find('img').attr('alt'),
          name: product.find('.name').text(),
          price: parseFloat(product.find('.price').text().replace(/[^0-9.]/g, '')),
          currency: product.find('.price').data('currency') || 'VND',
          image: product.find('img').attr('src')
        };
        handleSystems.events.addToCart(productData);
      });

      $(".cart-container .cart-icon").on("click", function () {
        handleSystems.events.openCartModal();
      });

      $(".close-cart").on("click", function () {
        handleSystems.events.closeCartModal();
      });

      $(".close-customer").on("click", function () {
        handleSystems.events.closeCustomerModal();
      });

      $("#cart-modal").on("click", function (event) {
        if ($(event.target).is("#cart-modal")) {
          handleSystems.events.closeCartModal();
        }
      });

      $("#customer-modal").on("click", function (event) {
        if ($(event.target).is("#customer-modal")) {
          handleSystems.events.closeCustomerModal();
        }
      });

      $("#order-btn").on("click", function () {
        handleSystems.events.showCustomerForm();
      });

      $("#submit-order-btn").on("click", function () {
        handleSystems.events.submitOrder();
      });

      $(".star-rating i").on("click", function () {
        handleSystems.events.selectRating($(this));
      });

      $("#submit-review-btn").on("click", function () {
        handleSystems.events.submitReview();
      });

      $(".close-review").on("click", function () {
        handleSystems.events.closeReviewModal();
      });

      $("#review-modal").on("click", function (event) {
        if ($(event.target).is("#review-modal")) {
          handleSystems.events.closeReviewModal();
        }
      });
    },

    loadPage: function () {
      $(document).ready(function () {
        $("html, body").animate(
          {
            scrollTop: 0,
          },
          "smooth"
        );
      });
    },

    processMenu: function (target) {
      $(".navbar-nav [data-target]").removeClass("active");
      let section = $("#" + target);
      if (section.length > 0) {
        $("html, body").animate(
          {
            scrollTop: section.offset().top,
          },
          100
        );
      }

      let activeItem = $(`.navbar-nav [data-target="${target}"]`);
      if (activeItem.length) {
        activeItem.addClass("active");
      }
    },

    changeSlider: function (imageSrc = null, color) {
      if (imageSrc !== null) {
        const elementImg = document.querySelector(".slider-img");
        elementImg.src = imageSrc;
      }

      document.documentElement.style.setProperty('--accent-color', color);
      
      // const elementFoot = document.querySelector("#foot");
      // elementFoot.style.backgroundColor = color;
    },

    readMoreText: function () {
      if ($("#text-extra").is(":visible")) {
        $("#text-extra").hide();
        $("#text-dots").show();
        $(this).text("Read More");
      } else {
        $("#text-extra").show();
        $("#text-dots").hide();
        $(this).text("Read Less");
      }
    },

    processArrowProducts: function (id, direction) {
      if (direction === "next") {
        handleSystems.events.nextProduct(id);
      } else if (direction === "prev") {
        handleSystems.events.prevProduct(id);
      }
    },

    nextProduct: function (id) {
      if (typeof handleSystems.activeIndex[id] === 'undefined') handleSystems.activeIndex[id] = 0;
      var itemWidth = $("#list-products-" + id).width() + 400;
      if (handleSystems.activeIndex[id] < $("#pagination-products-" + id + " .item").length - 1) {
        handleSystems.activeIndex[id]++;
        $("#list-products-" + id).animate(
          {
            scrollLeft: "+=" + itemWidth,
          },
          150
        );
        handleSystems.events.updateArrowProduct(id);
        handleSystems.events.activeItemProduct(id, handleSystems.activeIndex[id]);
      }
    },

    prevProduct: function (id) {
      if (typeof handleSystems.activeIndex[id] === 'undefined') handleSystems.activeIndex[id] = 0;
      var itemWidth = $("#list-products-" + id).width() + 400;
      if (handleSystems.activeIndex[id] > 0) {
        handleSystems.activeIndex[id]--;
        $("#list-products-" + id).animate(
          {
            scrollLeft: "-=" + itemWidth,
          },
          150
        );
        handleSystems.events.updateArrowProduct(id);
        handleSystems.events.activeItemProduct(id, handleSystems.activeIndex[id]);
      }
    },

    activeItemProduct: function (id, page) {
      $("#pagination-products-" + id + " .item").removeClass("active");
      $("#pagination-products-" + id + " .item").eq(page).addClass("active");
    },

    prevReview: function (review, totalReviews) {
      if (handleSystems.reviewPosition > 0) {
        handleSystems.reviewPosition -= 380;
        review.style.transform = `translateY(-${handleSystems.reviewPosition}px)`;
      }
    },

    updateArrowProduct: function (id) {
      if (typeof handleSystems.activeIndex[id] === 'undefined') handleSystems.activeIndex[id] = 0;
      if (handleSystems.activeIndex[id] === 0) {
        $("#prev-" + id).addClass("disabled");
      } else {
        $("#prev-" + id).removeClass("disabled");
      }
      if (handleSystems.activeIndex[id] >= $("#pagination-products-" + id + " .item").length - 1) {
        $("#next-" + id).addClass("disabled");
      } else {
        $("#next-" + id).removeClass("disabled");
      }
    },

    scrollToItem: function (id, page) {
      if (typeof handleSystems.activeIndex[id] === 'undefined') handleSystems.activeIndex[id] = 0;
      if (page !== handleSystems.activeIndex[id]) {
        let difference = page - handleSystems.activeIndex[id];
        if (difference > 0) {
          for (let i = 0; i < difference; i++) {
            handleSystems.events.nextProduct(id);
          }
        } else {
          for (let i = 0; i < Math.abs(difference); i++) {
            handleSystems.events.prevProduct(id);
          }
        }
      }
    },

    processArrowReviews: function (direction) {
      const review = document.querySelector("#list-review");
      const reviewItem = document.querySelectorAll("#list-review .item-review");
      
      if (handleSystems.reviewIndex === 0) {
        handleSystems.reviewIndex = reviewItem.length;
      }

      if (direction === "next") {
        handleSystems.events.nextReview(review, reviewItem.length);
      } else if (direction === "prev") {
        handleSystems.events.prevReview(review, reviewItem.length);
      }
    },

    nextReview: function (review, totalReviews) {
      if (handleSystems.reviewPosition < (totalReviews - 1) * 380) {
        handleSystems.reviewPosition += 380;
        review.style.transform = `translateY(-${handleSystems.reviewPosition}px)`;
      }
    },

    carouselLoop: function () {
      $(".carousel-track").each(function () {
        var track = $(this);

        if (track.data("looped") !== true) {
          var children = track.children().toArray();
          if (children.length > 0) {
            $.each(children, function (index, child) {
              track.append($(child).clone());
            });

            track.data("looped", true);
          }
        }
      });
    },

    addToCart: function (productData) {
      let counters = $('.cart-counter');
      
      if (counters.length > 0) {
        let existingProduct = handleSystems.cart.find(item => item.name === productData.name);
        
        if (existingProduct) {
          existingProduct.quantity += 1;
        } else {
          productData.quantity = 1;
          handleSystems.cart.push(productData);
        }

        let totalItems = handleSystems.cart.reduce((sum, item) => sum + item.quantity, 0);
        
        counters.each(function() {
          let counter = $(this);
          counter.text(totalItems);
          
          let animationClass = 'animated-counter';
          counter.removeClass(animationClass);
          
          setTimeout(() => {
            counter.addClass(animationClass);
          }, 10);
        });
      }
    },

    openCartModal: function () {
      handleSystems.events.renderCartItems();
      $('#cart-modal').fadeIn(300);
    },

    closeCartModal: function () {
      $('#cart-modal').fadeOut(300);
    },

    renderCartItems: function () {
      let cartItems = $('#cart-items');
      let totalPrice = $('#cart-total-price');
      let emptyText = $('#cart-modal').data('empty-text');
      
      if (handleSystems.cart.length === 0) {
        cartItems.html(`<p class="empty-cart-message">${emptyText}</p>`);
        totalPrice.text('0 VND');
      } else {
        let cartHtml = '<div class="cart-items-list">';
        let total = 0;
        
        handleSystems.cart.forEach((item, index) => {
          let itemTotal = item.price * item.quantity;
          total += itemTotal;
          let currency = item.currency;
          
          cartHtml += `
            <div class="cart-item" data-index="${index}">
              <img src="${item.image}" alt="${item.name}" loading="lazy">
              <div class="cart-item-info">
                <h4>${item.name}</h4>
                <p class="cart-item-price">${item.price.toFixed(3)} ${currency}</p>
              </div>
              <div class="cart-item-quantity">
                <button class="qty-btn minus" data-index="${index}">-</button>
                <span class="qty">${item.quantity}</span>
                <button class="qty-btn plus" data-index="${index}">+</button>
              </div>
              <div class="cart-item-total">${itemTotal.toFixed(3)} ${currency}</div>
              <button class="remove-item" data-index="${index}"><i class="bi bi-trash"></i></button>
            </div>
          `;
        });
        
        cartHtml += '</div>';
        cartItems.html(cartHtml);
        
        let displayCurrency = handleSystems.cart[0]?.currency;
        totalPrice.text(total.toFixed(3) + ' ' + displayCurrency);
        
        $('.qty-btn.plus').on('click', function() {
          let index = $(this).data('index');
          handleSystems.events.updateQuantity(index, 1);
        });
        
        $('.qty-btn.minus').on('click', function() {
          let index = $(this).data('index');
          handleSystems.events.updateQuantity(index, -1);
        });
        
        $('.remove-item').on('click', function() {
          let index = $(this).data('index');
          handleSystems.events.removeItem(index);
        });
      }
    },

    updateQuantity: function (index, change) {
      if (handleSystems.cart[index]) {
        handleSystems.cart[index].quantity += change;
        
        if (handleSystems.cart[index].quantity <= 0) {
          handleSystems.cart.splice(index, 1);
        }
        
        handleSystems.events.renderCartItems();
        handleSystems.events.updateCartCounters();
      }
    },

    removeItem: function (index) {
      handleSystems.cart.splice(index, 1);
      handleSystems.events.renderCartItems();
      handleSystems.events.updateCartCounters();
    },

    updateCartCounters: function () {
      let totalItems = handleSystems.cart.reduce((sum, item) => sum + item.quantity, 0);
      $('.cart-counter').text(totalItems);
    },

    order: function () {
      let modal = $('#cart-modal');
      let emptyText = modal.data('empty-text');
      
      if (handleSystems.cart.length === 0) {
        alert(emptyText);
      } else {
        handleSystems.events.showCustomerForm();
      }
    },

    showCustomerForm: function() {
      let modal = $('#cart-modal');
      let emptyText = modal.data('empty-text');
      
      if (handleSystems.cart.length === 0) {
        alert(emptyText);
      } else {
        $('#cart-modal').hide();
        $('#customer-modal').fadeIn(300);
        $('#customer-form')[0].reset();
      }
    },

    closeCustomerModal: function() {
      $('#customer-modal').fadeOut(300);
    },

    submitOrder: function() {
      let form = $('#customer-form')[0];
      
      if (!form.checkValidity()) {
        form.reportValidity();
      } else {
        let customerData = {
          first_name: $('input[name="first_name"]').val(),
          last_name: $('input[name="last_name"]').val(),
          email: $('input[name="email"]').val(),
          phone: $('input[name="phone"]').val(),
          address: $('input[name="address"]').val(),
          state: $('input[name="state"]').val(),
          city: $('input[name="city"]').val()
        };

        let successMsg = $('#cart-modal').data('order-success');
        let errorMsg = $('#cart-modal').data('order-error');

        $.ajax({
          url: (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + '/home/order',
          method: 'POST',
          contentType: 'application/json',
          data: JSON.stringify({
            customer: customerData,
            items: handleSystems.cart,
            total: handleSystems.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0)
          }),
          success: function(response) {
            alert(successMsg || 'Order placed successfully!');
            handleSystems.cart = [];
            handleSystems.events.closeCustomerModal();
            handleSystems.events.updateCartCounters();
            $('#customer-form')[0].reset();
          },
          error: function(xhr, status, error) {
            console.error('Order error:', xhr.responseText, status, error);
            alert(errorMsg || 'An error occurred while placing your order!');
          }
        });
      }
    },

    selectRating: function(star) {
      let rating = star.data('value');
      let container = star.closest('.star-rating');
      
      container.find('i').removeClass('bi-star-fill').addClass('bi-star');
      
      container.find('i').each(function() {
        if ($(this).data('value') <= rating) {
          $(this).removeClass('bi-star').addClass('bi-star-fill');
        }
      });
      
      container.data('rating', rating);
    },

    submitReview: function() {
      let form = $('#review-form')[0];
      let result = null;
      
      if (!form) {
        result = false;
      } else {
        let rating = $('.star-rating').data('rating');
        
        if (!rating) {
          alert('Please select a star rating!');
          result = false;
        } else {
          if (!form.checkValidity()) {
            form.reportValidity();
            result = false;
          } else {
            let reviewData = {
              name: $('input[name="review_name"]').val(),
              email: $('input[name="review_email"]').val(),
            };
              $.ajax({
                url: '/highlands/public/check-login',
                method: 'GET',
                dataType: 'json',
                xhrFields: { withCredentials: true },
                success: function(data) {
                  if (!data.logged_in) {
                    alert('Please login your account');
                    window.location.href = '/highlands/public/login';
                  } else {
                    if (!email) {
                      alert('Please enter your email!');
                      return;
                    }
                    $.ajax({
                      url: (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + '/home/check-customer',
                      method: 'POST',
                      contentType: 'application/json',
                      data: JSON.stringify({ email: email }),
                      success: function(response) {
                        $('#review-email-form')[0].reset();
                        handleSystems.events.showReviewModal(email);
                      },
                      error: function(xhr, status, error) {
                        let errorMsg = 'Please purchase a product before writing a review!';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                          errorMsg = xhr.responseJSON.message;
                        }
                        alert(errorMsg);
                      }
                    });
                  }
                },
                error: function() {
                  alert('Please login your account');
                  window.location.href = '/highlands/public/login';
                }
              });
          }
        }
      }
      
      if (result === null) {
        result = false;
      }
    },

    showReviewModal: function(email) {
      $('#review-modal').fadeIn(300);
      if (email) {
        $('input[name="review_email"]').val(email);
      }
      $('#review-form')[0].reset();
      if (email) {
        $('input[name="review_email"]').val(email);
      }
      $('.star-rating i').removeClass('bi-star-fill').addClass('bi-star');
      $('.star-rating').data('rating', 0);
    },

    closeReviewModal: function() {
      $('#review-modal').fadeOut(300);
    },

    reviewSubmit: function(e) {
      e.preventDefault();
      $.ajax({
        url: '/highlands/public/check-login',
        method: 'GET',
        dataType: 'json',
        xhrFields: { withCredentials: true },
        success: function(data) {
          if (!data.logged_in) {
            alert('Please login your account');
            window.location.href = '/highlands/public/login';
          } else {
            e.target.submit();
          }
        },
        error: function() {
          alert('Please login your account');
          window.location.href = '/highlands/public/login';
        }
      });
    },
    
  },
};

handleSystems.init();
