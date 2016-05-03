!function($, w, d) {

    var _acf = $.ScheduleBuilder.ACF = {};

    _acf.VenueManager = {

        select2: false,
        $venue: null,
        $room: null,
        defaultChoices: null,

        init: function() {
            var venueSelector = '#acf-location--venue',
                roomSelector = '#acf-location--room';

            if ($(venueSelector).data('ui') == 1) {
                // For Select2 support
                this.select2 = true;
                venueSelector = venueSelector +'-input';
            }

            this.$venue = $(venueSelector);
            this.$room = $(roomSelector);

            this.defaultChoices = this.$room.children();

            this.$venue.on('change', this.loadRooms).trigger('change');
        },

        loadRooms: function() {
            var self = _acf.VenueManager;

            $.ajax(w.ajaxurl, {
                data: {
                    action: 'sb/load_rooms',
                    nonce: w.sb_acf.nonce,
                    venue: self.$venue.val(),
                    post: w.sb_acf.post,
                },
                dataType: 'json',
            }).done(function(data) {
                console.log(data);
                self.$room.empty();
                self.$room.append(self.defaultChoices);
                $.each(data, function(i, room) {
                    self.addRoom(room);
                });
                if (self.select2) {
                    acf.select2.init(self.$room);
                }
            });
        },

        addRoom: function(room) {
            var $option = this.$room.children('[value="'+ room.name +'"]');
            if ($option.length == 0) {
                $option = $(d.createElement('option'))
                    .attr('value', room.name)
                    .text(room.name);
                if (room.selected) {
                    $option.attr('selected', 'selected');
                }
            }

            this.$room.append($option);
        },
    };

    _acf.InputMask = {

        masks: [
            {
                selector: '.acf-time input',
                mask: 'h:s',
            }
        ],

        init: function() {
            $.each(this.masks, function(i, mask) {
                $(mask.selector).inputmask($.extend({
                    mask: mask.mask
                }, mask.options));
            });
        }
    };

    $(d).ready(function() {
        _acf.VenueManager.init();
        _acf.InputMask.init();
    });

}(jQuery, window, document);
