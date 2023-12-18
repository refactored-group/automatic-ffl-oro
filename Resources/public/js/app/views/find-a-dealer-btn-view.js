define(function(require) {
    'use strict';

    const BaseView = require('oroui/js/app/views/base/view');
    const DialogWidget = require('oro/dialog-widget');
    const _ = require('underscore');
    const __ = require('orotranslation/js/translator');
    const mediator = require('oroui/js/mediator');
    const routing = require('routing');
    const $ = require('jquery');

    const FindADealerBtnView = BaseView.extend({
        dialogWidget: null,

        inputSelector: null,

        events: {
            'click .find-a-dealer-btn': 'openFindADealerModal',
            'click .select-new-dealer-btn': 'openFindADealerModal',
            'keydown input[name=ffl_search]': 'onGenericKeydown'
        },

        options: {
            findADealerDialogRoute: null,
            storeHash: null,
            testMode: null,
            googleMapsKey: null,
            googleMapsApiUrl: null,
            dealersEndpoint: null,
            findDealerContainerSelector: '#find-a-ffl-dealer',
            editContainerSelector: '#select-new-dealer',
            fflResultsSelector: '#ffl-results',
            findADealerBtn: {
                className: 'btn',
                text: __('refactored_group.automatic_ffl.checkout.find_a_dealer.label')
            },
            editBtn: {
                className: 'btn',
                text: __('refactored_group.automatic_ffl.checkout.edit_shipping_address.label')
            },
            editButtonNeeded: null,
            entityClass: null,
            entityId: null,
            googleMap: null,
            currentFflItemId: null,
            fflResults: null,
            isSearchingMessageVisible: null,
            isNoDealersMessageVisible: null,
            isResultsVisible: null,
            mapPositionsList: [],
            mapMarkersList: [],
            currentInfowindow: false,
            blueMarkerUrl: 'http://maps.google.com/mapfiles/kml/paddle/blu-blank.png',
            redMarkerUrl: 'http://maps.google.com/mapfiles/kml/paddle/red-blank.png'
        },

        ENTER_KEY_CODE: 13,

        constructor: function FindADealerBtnView(options) {
            return FindADealerBtnView.__super__.constructor.call(this, options);
        },

        /**
         * @constructor
         */
        initialize: function(options) {
            this.options = $.extend(true, {}, this.options, options);
            FindADealerBtnView.__super__.initialize.call(this, options);

            // Removes update_checkout_state GET parameters to avoid accidental state updates when page is refreshed.
            const url = location.href.replace(/\&?update_checkout_state=1\&?/i, '');
            if (url !== location.href) {
                history.replaceState({}, document.title, url);
            }

            if (!this.options.editButtonNeeded) {
                this.addFindADealerBtn();
            } else {
                this.addEditBtn();
            }
        },

        /**
         * Refers keydown action to proper action handler
         *
         * @param e
         */
        onGenericKeydown: function(e) {
            this.onGenericEnterKeydown(e);
        },

        /**
         * Generic keydown handler, which handles ENTER
         *
         * @param {$.Event} e
         */
        onGenericEnterKeydown: function(e) {
            if (e.keyCode === this.ENTER_KEY_CODE) {
                this.getFflResults();
                e.stopImmediatePropagation();
                e.preventDefault();
            }
        },

        addFindADealerBtn: function() {
            const findADealerBtnElement = this.createFindADealerBtn();
            $(this.options.findDealerContainerSelector)
                .append(findADealerBtnElement);
        },

        createFindADealerBtn: function() {
            const findADealerBtnClassName = [this.options.findADealerBtn.className,
                'find-a-dealer-btn'].join(' ');

            return '<button type="button" class="' + findADealerBtnClassName + '">' +
                this.options.findADealerBtn.text +
                '</button>';
        },

        addEditBtn: function() {
            const editBtnElement = this.createEditBtn();
            $(this.options.editContainerSelector)
                .append(editBtnElement);
        },

        createEditBtn: function() {
            const editBtnClassName = 'select-new-dealer-btn';

            return '<button type="button" class="' + editBtnClassName + '">' +
                this.options.editBtn.text +
                '</button>';
        },

        createDealerCard: function(dealer) {
            return '<div class="' + dealer.class + '" id="selectDealerCard' + dealer.index + '">' +
                '<p class="ffl-dealer-name">' + dealer.business_name_formatted + '</p>' +
                '<p class="ffl-dealer-address">' + dealer.formatted_address + '</p>' +
                '<p class="ffl-dealer-phone"><a href="tel:+1' + dealer.phone_number + '">' + dealer.phone_number + '</a></p>' +
                '</div>';
        },

        openFindADealerModal: function(event) {
            event.preventDefault();

            const routeName = this.options.findADealerDialogRoute;

            this.dialogWidget = new DialogWidget({
                url: routing.generate(routeName),
                stateEnabled: false,
                incrementalPosition: false,
                dialogOptions: {
                    // modal: true,
                    state: 'maximized',
                    dialogClass: 'ffl-dealer-dialog-widget',
                    modal: false,
                    title: null,
                    autoResize: false,
                    resizable: false,
                    draggable: false,
                    width: 'auto',
                    position: null
                }
            });

            this.dialogWidget.once('renderComplete', (function($el) {
                const cancelButton = $el.find('[type="reset"]');
                const searchButton = $el.find('[data-role="searchBtn"]');
                const radiusDropdown = $el.find('#ffl-miles-search');
                const searchInputField = $el.find('#ffl-input-search');
                const searchingMessage = $('.ffl-searching-message');
                const noDealersMessage = $('.ffl-no-dealers-message');

                searchingMessage.hide();
                noDealersMessage.hide();

                cancelButton.on('click', (function() {
                    this.dialogWidget.remove();
                    delete this.dialog;
                }).bind(this));

                searchInputField.on('keypress', (function(e) {
                    this.onGenericEnterKeydown(e);
                }).bind(this));

                searchButton.on('click', (function() {
                    this.getFflResults();
                }).bind(this));

                this.loadGoogleMaps();
            }).bind(this));

            this.dialogWidget.render();
        },

        /**
         * Send API request to FFL and retrieve a list of dealers
         */
        getFflResults: function() {
            const searchRadius = $('#ffl-miles-search').val();
            const searchInputString = $('#ffl-input-search').val();
            const searchResults = $('#ffl-results');
            const searchResultsById = document.getElementById('ffl-results');
            const searchingMessage = $('.ffl-searching-message');
            const noDealersMessage = $('.ffl-no-dealers-message');

            // Display searching for dealers message
            searchingMessage.show();
            noDealersMessage.hide();
            searchResults.hide();

            $.ajax({
                url: this.options.dealersEndpoint + '?location=' + searchInputString + '&radius=' + searchRadius,
                headers: {'store-hash': this.options.store_hash, 'origin': window.location.origin},
                success: (function(result) {
                    if (result && result.dealers.length > 0) {
                        this.parseDealersResult(result.dealers);
                        this.centerMap();
                        searchResults.show();
                        searchingMessage.hide();
                        noDealersMessage.hide();

                        // empty parameter call to replaceChildren will remove all child nodes for a container
                        searchResultsById.replaceChildren();

                        $(result.dealers).each(function(i, dealer) {
                            searchResults.append(this.createDealerCard(dealer));
                            const selectDealerCard = document.getElementById('selectDealerCard' + dealer.index);

                            selectDealerCard.addEventListener('click', () => {
                                this.selectDealer((dealer));
                            });
                        }.bind(this));
                    } else {
                        searchingMessage.hide();
                        searchResults.hide();
                        noDealersMessage.show();
                        this.removeMarkersFromMap();
                    }
                }).bind(this),
                error: (function(result) {
                    console.log('result error', result);
                    searchingMessage.hide();
                    searchResults.hide();
                    noDealersMessage.show();
                    this.removeMarkersFromMap();
                }).bind(this)
            });
        },

        /**
         * Center map after creating markers
         */
        centerMap: function() {
            const bounds = new google.maps.LatLngBounds();

            for (let i = 0, LtLgLen = this.options.mapPositionsList.length; i < LtLgLen; i++) {
                bounds.extend(this.options.mapPositionsList[i]);
            }
            this.options.googleMap.fitBounds(bounds);
        },

        /**
         * Select a dealer, close the modal, and save the address
         * @param dealer
         */
        selectDealer: function(dealer) {
            const data = {};
            const urlOptions = {};

            if (dealer) {
                data.dealer = dealer;
                urlOptions.checkoutId = this.options.entityId;
                urlOptions.entityClass = this.options.entityClass;
            } else {
                return;
            }

            $.ajax({
                url: routing.generate('ffl_frontend_update_checkout', urlOptions),
                data,
                type: 'post',
                success: (function(result) {
                    if (result.successful) {
                        // Close Modal
                        this.dialogWidget.remove();
                        delete this.dialog;

                        // Refresh page after updating checkout
                        this._updatePageData();
                    }
                }).bind(this),
                error: (function(result) {
                    console.log('select dealer error', result);
                })
            });
        },

        _updatePageData: function() {
            mediator.execute('showLoading');

            // Adds update_checkout_state GET parameter to force update checkout state after dealer is selected.
            const parts = location.href.split('?');
            const query = (typeof parts[1] === 'undefined' ? '?' : parts[1] + '&') + 'update_checkout_state=1';
            mediator.execute('redirectTo', {url: parts[0] + '?' + query}, {replace: true, fullRedirect: true});
        },

        /**
         * Parse API results and create markers on the map
         * @param dealers
         */
        parseDealersResult: function(dealers) {
            // Clear all markers
            this.removeMarkersFromMap();

            $(dealers).each(function(i, dealer) {
                // Format address to display in the results list
                dealers[i].id = (i + 1).toString();
                dealers[i].index = i.toString();
                dealers[i].formatted_address = dealer.premise_street + ', ' + dealer.premise_city + ', ' + dealer.premise_state + ' ' + dealer.premise_zip;
                dealers[i].business_name_formatted = dealers[i].id + '. ' + dealers[i].business_name;
                dealers[i].phone_number = this.formatPhoneNumber(dealers[i].phone_number);
                dealers[i].license = dealer.license;

                if (dealers[i].preferred) {
                    dealers[i].icon_url = this.options.blueMarkerUrl;
                    dealers[i].class = 'ffl-dealer-preferred';
                } else {
                    dealers[i].icon_url = this.options.redMarkerUrl;
                    dealers[i].class = 'ffl-dealer';
                }
            }.bind(this));
            this.options.fflResults = dealers;

            $(dealers).each(function(i, dealer) {
                // Add marker to the map
                this.addMarker(dealers[i], i);
            }.bind(this));
        },

        /**
         * Returns phone number in the format (xxx)-xxx-xxxx
         *
         * @param phoneNumberString
         * @returns {string|null}
         */
        formatPhoneNumber: function(phoneNumberString) {
            const cleaned = ('' + phoneNumberString).replace(/\D/g, '');
            const match = cleaned.match(/^(\d{3})(\d{3})(\d{4})$/);
            if (match) {
                return '(' + match[1] + ')' + match[2] + '-' + match[3];
            }
            return null;
        },

        /**
         * Add marker to the map
         * @param location
         */
        addMarker: function(dealer, zIndex) {
            const marker = new google.maps.Marker({
                position: {lat: dealer.lat, lng: dealer.lng},
                zIndex,
                map: this.options.googleMap,
                label: dealer.id,
                icon: {
                    url: dealer.icon_url,
                    labelOrigin: new google.maps.Point(33, 20)
                }
            });

            this.addPopupToMarker(marker, dealer);
            this.options.mapMarkersList.push(marker);
            this.options.mapPositionsList.push(new google.maps.LatLng(dealer.lat, dealer.lng));
        },

        /**
         * Add a popup to the marker
         * @param marker
         * @param dealer
         */
        addPopupToMarker: function(marker, dealer) {
            const contentString =
                '<div style="display: none"><div id="popupcontent' + dealer.index + '" class="popupContent">' +
                '<div id="siteNotice' + dealer.index + '">' +
                '</div>' +
                '<h2 id="firstHeading" class="firstHeading">' + dealer.business_name_formatted + '</h2>' +
                '<div id="bodyContent">' +
                '<p>' + dealer.formatted_address + '</p>' +
                '<p><b>Phone: </b><a href="tel:+1' + dealer.phone_number + '">' + dealer.phone_number + '</a></p>' +
                '<p><b>License: </b>' + dealer.license + '</p>' +
                '<p><a href="#" id="selectDealer' + dealer.index + '">' +
                'Select this dealer</a> ' +
                '</p>' +
                '</div>' +
                '</div></div>';
            $('#popupcontent' + dealer.index).remove();
            $('#map_popup_container').append(contentString);
            const domElement = document.getElementById('popupcontent' + dealer.index);

            const infowindow = new google.maps.InfoWindow({
                content: domElement
            });

            const selectDealer = document.getElementById('selectDealer' + dealer.index);

            selectDealer.addEventListener('click', () => {
                this.selectDealer((dealer));
            });

            marker.addListener('click', () => {
                if (this.options.currentInfowindow) {
                    this.options.currentInfowindow.close();
                }
                infowindow.open({
                    anchor: marker,
                    map: this.options.googleMap,
                    shouldFocus: false
                });
                this.options.currentInfowindow = infowindow;
            });
        },

        removeMarkersFromMap: function() {
            const mapPopupContainer = document.getElementById('map_popup_container');

            // Clear all markers
            for (let i = 0; i < this.options.mapMarkersList.length; i++) {
                this.options.mapMarkersList[i].setMap(null);
            }

            // Clear all positions
            this.options.mapPositionsList = [];

            // Clear out leftover empty divs from add marker popups
            mapPopupContainer.replaceChildren();
        },

        loadGoogleMaps: function() {
            const data = {};

            if (this.options.googleMapsKey) {
                data.key = this.options.googleMapsKey;
            } else {
                return;
            }

            $.ajax({
                url: `${location.protocol}//maps.googleapis.com/maps/api/js`,
                data,
                dataType: 'script',
                cache: true,
                success: () => {
                    this.initMap();
                },
                errorHandlerMessage: false,
                error: () => {
                    console.log('Could not load GoogleMaps');
                }
            });
        },

        hasGoogleMaps: function() {
            return !_.isUndefined(window.google) && google.hasOwnProperty('maps');
        },

        /**
         * Init Google Maps
         */
        initMap: function() {
            // Init Google Maps
            const myLatLng = {lat: 40.363, lng: -95.044};
            this.options.googleMap = new google.maps.Map(document.getElementById('ffl-map'), {
                zoom: 4,
                center: myLatLng,
                mapTypeControlOptions: {
                    mapTypeIds: []
                },
                fullscreenControl: false,
                panControl: false,
                streetViewControl: false,
                mapTypeId: 'roadmap'
            });
        }
    });

    return FindADealerBtnView;
});
