const adminPanel = {
  init: function () {
    this.events.init();
  },
  events: {
    init: function () {
      adminPanel.events.initAdminPanel();
      adminPanel.events.initTableFeatures();
      adminPanel.events.initFormHandlers();
    },

    initAdminPanel: function () {
      $(".alert").each(function () {
        const alertElement = this;
        setTimeout(() => {
          const bsAlert = new bootstrap.Alert(alertElement);
          bsAlert.close();
        }, 5000);
      });

      adminPanel.events.updateActiveMenu();
    },

    updateActiveMenu: function () {
      const currentPath = window.location.pathname;
      $(".menu-link").each(function () {
        const href = $(this).attr("href");
        if (currentPath === href || currentPath.startsWith(href + "/")) {
          $(this).addClass("active");
        } else {
          $(this).removeClass("active");
        }
      });
    },

    initTableFeatures: function () {
      $("table tbody tr").on("mouseenter", function () {
        $(this).css("cursor", "pointer");
      });

      $('a[href*="delete"]').on("click", function (e) {
        if (!confirm($(this).attr("data-confirm") || "Are you sure?")) {
          e.preventDefault();
        }
      });
    },

    initFormHandlers: function () {
      $("form").on("submit", function (e) {
        if (!this.checkValidity()) {
          e.preventDefault();
          e.stopPropagation();
        }
        $(this).addClass("was-validated");
      });

      $('input[type="number"][name*="price"]').on("blur", function () {
        if ($(this).val()) {
          $(this).val(parseInt($(this).val()).toLocaleString("vi-VN"));
        }
      });
    },

    deleteItem: function (url, itemName = "item") {
      if (confirm(`Delete this ${itemName}? This action cannot be undone.`)) {
        window.location.href = url;
      }
    },

    showSuccess: function (message) {
      const alertDiv = $(
        '<div class="alert alert-success alert-dismissible fade show" role="alert"></div>'
      );
      alertDiv.html(`
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `);

      const contentArea = $("#admin-content, .admin-content").first();
      if (contentArea.length) {
        contentArea.prepend(alertDiv);

        setTimeout(() => {
          const bsAlert = new bootstrap.Alert(alertDiv[0]);
          bsAlert.close();
        }, 5000);
      }
    },

    showError: function (message) {
      const alertDiv = $(
        '<div class="alert alert-danger alert-dismissible fade show" role="alert"></div>'
      );
      alertDiv.html(`
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `);

      const contentArea = $("#admin-content, .admin-content").first();
      if (contentArea.length) {
        contentArea.prepend(alertDiv);

        setTimeout(() => {
          const bsAlert = new bootstrap.Alert(alertDiv[0]);
          bsAlert.close();
        }, 5000);
      }
    },

    updateStatus: function (
      itemId,
      status,
      endpoint = "/highlands/public/admin/update-status"
    ) {
      $.ajax({
        url: endpoint,
        type: "POST",
        dataType: "json",
        data: {
          id: itemId,
          status: status,
        },
        success: function (data) {
          if (data.success) {
            adminPanel.events.showSuccess(
              data.message || "Status updated successfully!"
            );
            setTimeout(() => {
              location.reload();
            }, 1000);
          } else {
            adminPanel.events.showError(
              data.message || "Failed to update status"
            );
          }
        },
        error: function (xhr, status, error) {
          console.error("Error:", error);
          adminPanel.events.showError("An error occurred. Please try again.");
        },
      });
    },

    formatCurrency: function (amount) {
      return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
      }).format(amount);
    },

    formatDate: function (dateString) {
      const date = new Date(dateString);
      return new Intl.DateTimeFormat("vi-VN", {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
        hour: "2-digit",
        minute: "2-digit",
      }).format(date);
    },

    exportTableToCSV: function (fileName = "export.csv") {
      const table = $("table").first();
      if (!table.length) {
        alert(appMultilang ? appMultilang('NO_TABLE_FOUND') : "No table found to export");
        return;
      }

      let csv = [];
      table.find("tr").each(function () {
        let csvRow = [];
        $(this)
          .find("td, th")
          .each(function () {
            let text = $(this).text().trim();
            if (text === "Image" && typeof appMultilang === 'function') text = appMultilang('IMAGE');
            if (text === "No Image" && typeof appMultilang === 'function') text = appMultilang('NO_IMAGE');
            csvRow.push(text);
          });
        csv.push(csvRow.join(","));
      });

      const csvContent =
        "data:text/csv;charset=utf-8," + encodeURIComponent(csv.join("\n"));
      const link = $("<a></a>");
      link.attr("href", csvContent);
      link.attr("download", fileName);
      link[0].click();
    },

    printPage: function (title = "Admin Report") {
      window.print();
    },
  },
};

$(document).ready(function () {
  adminPanel.init();
});
