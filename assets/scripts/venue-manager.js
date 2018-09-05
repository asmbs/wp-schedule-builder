/*jshint esversion: 6 */

import $ from 'jquery';

/**
 * Venue manager -- dynamically loads room choices based on the
 * selected venue.
 */
export class VenueManager {

    constructor() {
        this._$venueField = null;
        this._$roomField = null;
        this._defaultChoices = null;
    }

    /**
     * Issue an XHR request to load the room choices for a selected venue.
     */
    load_rooms(e) {

        // Get self-reference
        let _this = this,
            ajaxurl = window.ajaxurl,
            opts = window.acf.o;

        // Run XHR request
        $.ajax(ajaxurl, {
            dataType: 'json',
            data: {
                action: 'sb/load_rooms',
                nonce:  opts.nonce,
                post:   opts.post_id,
                venue:  _this.$venueField.val(),
            },
        }).done((e) => _this._set_rooms(e));
    }

    /**
     * Populate the room list using _add_room.
     */
    _set_rooms(data) {

        // Get self-reference
        let _this = this;

        // Reset the room list with the default choices
        _this.$roomField.empty()
            .append(_this.defaultChoices);

        // Add each room in the returned list
        $.each(data, function(i, room) {
            _this._add_room(room);
        });

        // Reinitialize select2
        acf.select2.init(_this.$roomField);
    }

    /**
     * Add a room to the room list.
     */
    _add_room(room) {

        let $o = this.$roomField.children('[value="'+ room.name +'"]');
        if ($o.length == 0) {
            $o = $(document.createElement('option'))
                .attr('value', room.name)
                .text(room.name);
            if (room.selected) {
                $o.attr('selected', 'selected');
            }
        }

        this.$roomField.append($o);
    }

    /**
     * Initialize the field relationship.
     */
    init() {

        // Set field references
        this.$venueField = $('#acf-location--venue');
        this.$roomField = $('#acf-location--room');

        // Get the default room choice list
        this.defaultChoices = this.$roomField.children();

        // Bind handlers
        this.$venueField
            .on('change.select2', (e) => this.load_rooms(e))
            .trigger('change.select2');
    }

    // Getters and setters ---------------------------------------------------------------------------------------------

    get $venueField() {
        return this._$venueField;
    }

    set $venueField(value) {
        this._$venueField = value;
    }

    get $roomField() {
        return this._$roomField;
    }

    set $roomField(value) {
        this._$roomField = value;
    }

    get defaultChoices() {
        return this._defaultChoices;
    }

    set defaultChoices(value) {
        this._defaultChoices = value;
    }
}