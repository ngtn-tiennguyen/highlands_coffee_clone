const adminSidebar = {
  init: function () {
    this.events.init();
  },

  events: {
    contentArea: null,
    menuLinks: null,

    init: function () {
      this.contentArea = $("#admin-content-area");
      this.menuLinks = $(".menu-link");

      this.menuLinks.on("click", function (e) {
        e.preventDefault();

        const action = $(this).data("action");
        const target = $(this).data("target");

        adminSidebar.events.menuLinks.removeClass("active");

        $(this).addClass("active");

        if (action === "index") {
          adminSidebar.events.loadDashboard();
        } else {
          adminSidebar.events.loadContent(target);
        }
      });
    },

    loadDashboard: function () {
      this.contentArea.html('<p class="text-muted">Loading...</p>');

      $.ajax({
        url: `${BASE_URL}/admin/getContent?action=index`,
        type: "GET",
        dataType: "html",
        success: function (html) {
          adminSidebar.events.contentArea.html(html);
          adminSidebar.events.reinitializeComponents();
          adminSidebar.events.attachOrderStatusListeners();
        },
        error: function (xhr, status, error) {
          console.error("Error:", error);
          adminSidebar.events.contentArea.html(
            '<p class="text-danger">Error loading dashboard</p>'
          );
        },
      });
    },

    loadContent: function (target) {
      this.contentArea.html('<p class="text-muted">Loading...</p>');

      $.ajax({
        url: `${BASE_URL}/admin/getContent?action=${target}`,
        type: "GET",
        dataType: "html",
        success: function (html) {
          adminSidebar.events.contentArea.html(html);
          adminSidebar.events.reinitializeComponents();
          adminSidebar.events.attachOrderStatusListeners();
        },
        error: function (xhr, status, error) {
          console.error("Error:", error);
          adminSidebar.events.contentArea.html(
            '<p class="text-danger">Error loading content</p>'
          );
        },
      });
    },

    reinitializeComponents: function () {
      if (typeof bootstrap !== "undefined") {
        $('[data-bs-toggle="tooltip"]').each(function () {
          new bootstrap.Tooltip(this);
        });
      }
    },

    attachOrderStatusListeners: function () {
      $(".order-status").off("change").on("change", function () {
        const orderId = $(this).data("orderId");
        const newStatus = $(this).val();
        adminSidebar.events.updateOrderStatus(orderId, newStatus);
      });
    },

    updateOrderStatus: function (orderId, status) {
      $.ajax({
        url: `${BASE_URL}/admin/update-order-status`,
        type: "POST",
        dataType: "json",
        data: {
          id: orderId,
          status: status,
        },
        success: function (data) {
          if (data.success) {
            console.log("Order status updated successfully");
          } else {
            alert(
              "Error: " + (data.message || "Failed to update order status")
            );
            adminSidebar.events.loadContent("orders");
          }
        },
        error: function (xhr, status, error) {
          console.error("Error:", error);
          alert("Error updating order status");
          adminSidebar.events.loadContent("orders");
        },
      });
    },
  },
};

$(document).ready(function () {
  adminSidebar.init();
});
