define([
    'Otto/Common'
], function () {
    window.HealthStatus = Class.create(Common, {

        // ---------------------------------------

        initialize: function () {
            var self = this;

            $('notification_mode').observe('change', self.notificationModeChanged)
                    .simulate('change');

            this.initFormValidation();
        },

        notificationModeChanged: function () {
            var self = this;

            $('notification_email_value_container').hide();
            if (this.value == Otto.php.constant('\\M2E\\Otto\\Model\\HealthStatus\\Notification\\Settings::MODE_EMAIL')) {
                $('notification_email_value_container').show();
            }
        }

        // ---------------------------------------
    })
});
