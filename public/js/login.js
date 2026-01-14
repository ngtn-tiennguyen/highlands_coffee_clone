
const loginPanel = {
  init: function () {
    this.events.init();
  },
  events: {
    init: function () {
      const signupSuccess = $('#signup-success-alert');
      if (signupSuccess.length) {
        setTimeout(function () {
          const loginTab = $('#login-tab');
          if (loginTab.length) {
            const tab = new bootstrap.Tab(loginTab[0]);
            tab.show();
          }
        }, 1200);
      }

      const signupAlerts = $('#signup-alerts');
      if (signupAlerts.length) {
        const errorAlert = signupAlerts.find('.alert-danger');
        if (errorAlert.length && errorAlert.text().trim() === window.SIGNUP_USERNAME_EXISTS) {
          setTimeout(function () {
            errorAlert.hide();
          }, 2000);
        }
      }
    }
  }
};

$(function () {
  loginPanel.init();
});
