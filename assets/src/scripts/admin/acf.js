!function($, w, d) {

    var sb = w.sb = {};

    acf.inputmask = acf.model.extend({
        
        events: {
            'domchange.sb ': '_load',
        },

        masks: [
            {
                selector: '.acf-time input',
                mask:     'h:s',
                options:  {},
            }
        ],

        init: function() {
            this._load();
        },

        _load: function() {
            $.each(this.masks, function(i, maskConfig) {
                $(maskConfig.selector).inputmask($.extend({
                    mask: maskConfig.mask
                }, maskConfig.options));
            });
        }
    });

    var domchanger = acf.model.extend({
        _domchange: function() {
            $(d).trigger('domchange.sb');
        }
    })

    acf.fields.repeater = acf.fields.repeater
        .extend(domchanger)
        .extend({
            events: {
                'click a[data-event="add-row"]': '_domchange',
            }
        });

    acf.fields.flexible_content = acf.fields.flexible_content
        .extend(domchanger)
        .extend({
            events: {
                'click .acf-fc-popup a': '_domchange',
            }
        });

    $(d).ready(function() {
        acf.inputmask.init();
    });

}(jQuery, window, document);
