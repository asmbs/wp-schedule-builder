!function($, w, d) {

    var sb = w.sb = {};

    /**
     * Venue manager -- dynamically loads room choices based on the
     * selected venue.
     */
    sb.venueManager = {

        $venueField: null,
        $roomField: null,

        select2: false,
        defaultChoices: null,

        /**
         * Initialize the field relationship.
         */
        init: function() {
            // Set field references
            this.$venueField = $('#acf-location--venue');
            this.$roomField = $('#acf-location--room');

            // If the Select2 UI is enabled
            if (this.$venueField.data('ui') === 1) {
                this.select2 = true;

                // Backwards-compatibility with Select2 before v4 (which wasn't introduced in ACF until v5.6)
                if(!this.$venueField.hasClass('select2-hidden-accessible')){
                    // Swap the venue field reference to the *-input sibling
                    this.$venueField = $(this.$venueField.selector +'-input');
                }
            }

            // Get the default room choice list
            this.defaultChoices = this.$roomField.children();

            // Bind handlers
            this.$venueField
                .on('change.venuemanager init.venuemanager', this.load_rooms)
                .trigger('init.venuemanager');
        },

        /**
         * Issue an XHR request to load the room choices for a selected venue.
         */
        load_rooms: function(e) {
            // Get self-reference
            var that = sb.venueManager,
                ajaxurl = w.ajaxurl,
                opts = w.sb_acf;

            // Run XHR request
            $.ajax(ajaxurl, {
                dataType: 'json',
                data: {
                    action: 'sb/load_rooms',
                    nonce:  opts.nonce,
                    post:   opts.post,
                    venue:  that.$venueField.val(),
                },
            }).done(that._set_rooms);
        },

        /**
         * Populate the room list using _add_room.
         */
        _set_rooms: function(data) {
            // Get self-reference
            var that = sb.venueManager;

            // Reset the room list with the default choices
            that.$roomField.empty()
                .append(that.defaultChoices);

            // Add each room in the returned list
            $.each(data, function(i, room) {
                that._add_room(room);
            });

            // Reinitialize select2
            if (that.select2) {
                acf.select2.init(that.$roomField);
            }
        },

        /**
         * Add a room to the room list.
         */
        _add_room: function(room) {
            var $o = this.$roomField.children('[value="'+ room.name +'"]');
            if ($o.length == 0) {
                $o = $(d.createElement('option'))
                    .attr('value', room.name)
                    .text(room.name);
                if (room.selected) {
                    $o.attr('selected', 'selected');
                }
            }

            this.$roomField.append($o);
        },
    };

    // -----------------------------------------------------------------------------------------------------------------
    
    sb.agenda = {};

    /**
     * Abstract agenda-item manager
     * 
     * This extension helps make sure that the presenter list for an abstract is limited to the list of authors
     * that have already been assigned to it.
     */
    sb.agenda.abstracts = {};
    
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Inputmask extension -- helper for adding input masks to arbitrary
     * acf fields.
     */
    acf.inputmask = acf.model.extend({

        mask: function(e, mask, args) {
            e.$el.inputmask(mask, args);
        },

        unmask: function(e) {
            e.$el.inputmask('remove');
        }
    });

    /**
     * Time field -- extends the basic text field to add a time-formatted
     * input mask.
     */
    // acf.fields.time = acf.field.extend({
    //
    //     type: 'time',
    //
        // events: {
        //     'focus input': 'mask_time'
        // },
        //
        // mask_time: function(e) {
        //     acf.inputmask.mask(e, 'h:s');
    //     }
    // });

    // -----------------------------------------------------------------------------------------------------------------

    $(d).ready(function() {
        sb.venueManager.init();
    });

}(jQuery, window, document);
