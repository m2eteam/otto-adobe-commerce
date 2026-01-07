define([
    'jquery',
    'mage/translate',
    'Magento_Ui/js/modal/modal',
    'M2ECore/Plugin/Messages',
    'M2ECore/Plugin/Confirm',
    'Otto/AdvancedFilter',
], function ($, $t, modal, MessageObj, confirm) {

    window.AdvancedFilterUpdating = Class.create({

        init: function (ruleEntityId, viewStateKey, prefix) {
            this.ruleEntityId = ruleEntityId;
            this.viewStateKey = viewStateKey;
            this.prefix = prefix;

            this.advancedFilter = new AdvancedFilter();
        },

        openUpdateFilterPopup: function () {
            const self = this;
            const content = $('#update_filter_popup_content');
            content.removeClass('hidden');

            modal({
                title: $t('Update Filter'),
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
                        text: $t('Update'),
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
            new Ajax.Request(Otto.url.get('listing_product_advanced_filter/update'), {
                method: 'post',
                parameters: {
                    title: $('#advanced_filter_name_input_update').val(),
                    form_data: $('#rule_form').serialize(),
                    prefix: this.prefix,
                    rule_entity_id: this.ruleEntityId,
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

        delete: function () {
            const self = this;
            confirm({
                actions: {
                    confirm: function() {
                        new Ajax.Request(Otto.url.get('listing_product_advanced_filter/delete'), {
                            method: 'post',
                            parameters: {
                                rule_entity_id: self.ruleEntityId,
                                view_state_key: self.viewStateKey,
                            },
                            onSuccess: function () {
                                self.advancedFilter.addClearRuleFormInput();
                                self.advancedFilter.submitForm();
                            }
                        });
                    },
                    cancel: function() {
                        return false;
                    }
                }
            })
        },

        back: function () {
            this.advancedFilter.addUpdatingBackInput();
            this.advancedFilter.submitForm();
        },
    });
});
