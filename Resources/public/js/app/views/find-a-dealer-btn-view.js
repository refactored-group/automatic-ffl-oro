define(function(require) {
    'use strict';

    const BaseView = require('oroui/js/app/views/base/view');
    const DialogWidget = require('oro/dialog-widget');
    const _ = require('underscore');
    const __ = require('orotranslation/js/translator');
    const routing = require('routing');
    const $ = require('jquery');
    const tools = require('oroui/js/tools');

    const FindADealerBtnView = BaseView.extend({
        dialogWidget: null,

        inputSelector: null,

        events: {
            'click .find-a-dealer-btn': 'openFindADealerModal'
        },

        options: {
            findADealerDialogRoute: null,
            containerSelector: '#find-a-ffl-dealer',
            findADealerBtn: {
                className: 'btn',
                text: 'Find a Dealer'
            }
        },

        constructor: function FindADealerBtnView(options) {
            return FindADealerBtnView.__super__.constructor.call(this, options);
        },

        /**
         * @constructor
         */
        initialize: function(options) {
            this.options = $.extend(true, {}, this.options, options);
            FindADealerBtnView.__super__.initialize.call(this, options);

            this.addFindADealerBtn();
        },

        addFindADealerBtn: function() {
            const findADealerBtnElement = this.createFindADealerBtn();
            $(this.options.containerSelector)
                .append(findADealerBtnElement);
        },

        createFindADealerBtn: function() {
            const findADealerBtnClassName = [this.options.findADealerBtn.className,
                'find-a-dealer-btn'].join(' ');

            return '<button type="button" class="' + findADealerBtnClassName + '">' +
                this.options.findADealerBtn.text +
                '</button>';
        },

        openFindADealerModal: function(event) {
            event.preventDefault();

            const routeName = this.options.findADealerDialogRoute;

            this.dialogWidget = new DialogWidget({
                url: routing.generate(routeName),
                stateEnabled: false,
                incrementalPosition: true,
                dialogOptions: {
                    modal: true,
                    width: '100%',
                    height: '100%',
                    minHeight: '100%',
                    allowMaximize: true
                    // minWidth: tools.isMobile() ? 520 : 804,
                }
            });
            this.dialogWidget.once('renderComplete', (function($el) {
                const cancelButton = $el.find('[type="reset"]');

                cancelButton.on('click', (function() {
                    this.dialogWidget.remove();
                    delete this.dialog;
                }).bind(this));
            }).bind(this));

            this.dialogWidget.render();
        }
    });

    return FindADealerBtnView;
});
