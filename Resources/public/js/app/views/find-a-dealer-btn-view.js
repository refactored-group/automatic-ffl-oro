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
            'click .find-a-dealer-btn': 'openFindADealerModal',
            'keydown input[name=ffl_search]': 'onGenericKeydown'
        },

        options: {
            findADealerDialogRoute: null,
            storeHash: null,
            testMode: null,
            googleMapsKey: null,
            googleMapsApiUrl: null,
            dealersEndpoint: null,
            containerSelector: '#find-a-ffl-dealer',
            fflResultsSelector: '#ffl-results',
            findADealerBtn: {
                className: 'btn',
                text: 'Find a Dealer'
            },
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
            console.log('testing options', this.options);
            FindADealerBtnView.__super__.initialize.call(this, options);

            this.addFindADealerBtn();
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
                console.log('enter key hit');
                this.getFflResults();
                e.stopImmediatePropagation();
                e.preventDefault();
            }
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

        createDealerCard: function(dealer) {
            return '<div class="' + dealer.class + '">' +
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
                stateEnabled: true,
                // incrementalPosition: true,
                incrementalPosition: false,
                // preventModelRemoval: true,
                dialogOptions: {
                    // modal: true,
                    // width: '100%',
                    // height: '100%',
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
                    // delete this.dialog;
                }).bind(this));

                searchInputField.on('keypress', (function(e) {
                    console.log('enter keypress');
                    console.log(e);
                    this.onGenericEnterKeydown(e);
                }).bind(this));

                searchButton.on('click', (function() {
                    console.log('search button hit');
                    console.log('current radius: ', radiusDropdown.val());
                    console.log('current search input: ', searchInputField.val());

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
                    console.log('testing result', result);

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
         * Parse API results and create markers on the map
         * @param dealers
         */
        parseDealersResult: function(dealers) {
            console.log('parseDealersResult function');
            console.log('dealers list', dealers);

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
            const self = this;
            const contentString =
                '<div style="display: none"><div id="popupcontent' + dealer.index + '" class="popupContent">' +
                '<div id="siteNotice' + dealer.index + '">' +
                "</div>" +
                '<h2 id="firstHeading" class="firstHeading">' + dealer.business_name_formatted + '</h2>' +
                '<div id="bodyContent">' +
                "<p>" + dealer.formatted_address + "</p>" +
                '<p><b>Phone: </b><a href="tel:+1' + dealer.phone_number + '">' + dealer.phone_number + "</a></p>" +
                "<p><b>License: </b>" + dealer.license + "</p>" +
                '<p><a href="#" data-bind="{click: function() {selectDealer(' + dealer.index + ')}}">' +
                "Select this dealer</a> " +
                "</p>" +
                "</div>" +
                "</div></div>";
            $('#popupcontent' + dealer.index).remove();
            $('#map_popup_container').append(contentString);
            const domElement = document.getElementById('popupcontent' + dealer.index);

            const infowindow = new google.maps.InfoWindow({
                content: domElement,
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
            console.log('load google maps');
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

        /**
         * Init Google Maps
         */
        initMap: function() {
            // Init Google Maps
            const myLatLng = {lat: 40.363, lng: -95.044};
            console.log('testing ffl-map id', document.getElementById('ffl-map'));
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

            console.log('testing googlemap', this.options.googleMap);
        }
    });

    return FindADealerBtnView;
});
