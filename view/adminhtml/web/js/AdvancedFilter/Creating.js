define([
    'jquery',
    'mage/translate',
    'Magento_Ui/js/modal/modal',
    'M2ECore/Plugin/Messages',
    'Otto/AdvancedFilter',
], function ($, $t, modal, MessageObj) {

    window.AdvancedFilterCreating = Class.create({

        init: function (ruleNick, prefix, viewStateKey) {
            this.ruleNick = ruleNick;
            this.prefix = prefix;
            this.viewStateKey = viewStateKey;
            this.advancedFilter = new AdvancedFilter();
        },

        openSaveFilterPopup: function () {
            const self = this;
            const content = $('#new_filter_popup_content');
            content.removeClass('hidden');

            modal({
                title: $t('Save Filter'),
                type: 'popup',
                modalClass: 'width-500',
                buttons: [
                    {
                        text: $t('Cancel'),
                        class: 'action-secondary',
                        click: function () {
                            this.closeModal();
                        }
                    },
                    {
                        text: $t('Save'),
                        class: 'action-primary',
                        click: function () {
                            const modal = this;
                            const success = function () {
                                self.advancedFilter.submitForm();
                                content.remove();
                            };
                            const validationFail = function (message) {
                                MessageObj.clear();
                                MessageObj.addError(message);
                            };
                            self.sendForm(success, validationFail);
                            modal.closeModal();
                        }
                    },
                ],
                closed: function () {
                    content.addClass('hidden')
                },
            }, content);

            content.modal('openModal');
        },

        sendForm: function (successCallback, validationFailCallback) {
            new Ajax.Request(Otto.url.get('listing_product_advanced_filter/save'), {
                method: 'post',
                parameters: {
                    title: $('#advanced_filter_name_input_create').val(),
                    form_data: $('#rule_form').serialize(),
                    rule_nick: this.ruleNick,
                    prefix: this.prefix,
                    view_state_key: this.viewStateKey,
                },
                onSuccess: function (response) {
                    const result = JSON.parse(response.transport.response);
                    if (result['result']) {
                        successCallback();

                        return;
                    }

                    validationFailCallback(result['message']);
                }
            });
        },

        back: function () {
            this.advancedFilter.addCreatingBackInput();
            this.advancedFilter.submitForm();
        },
    });
});
