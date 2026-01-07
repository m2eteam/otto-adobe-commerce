define([
    'jquery',
    'Otto/AdvancedFilter',
], function ($) {
    window.AdvancedFilterSelect = Class.create({

        init: function () {
            this.advancedFilter = new AdvancedFilter();
        },

        initEvents: function () {
            const self = this;
            $('#advanced_filter_list').change(function (e) {
               self.advancedFilter.submitForm()
            });
        },

        createNewFilter: function () {
            this.advancedFilter.addCreateNewFilterInput();
            this.advancedFilter.submitForm();
        },

        updateFilter: function () {
            this.advancedFilter.addUpdateFilterInput();
            this.advancedFilter.submitForm();
        },
    });
});
