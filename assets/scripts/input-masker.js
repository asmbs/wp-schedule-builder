/*jshint esversion: 6 */

import Inputmask from "inputmask";

export class InputMasker {

    constructor() {

    }

    initTimeMasks($field){

        // Set the input mask
        let timeMask = new Inputmask("datetime", {
            inputFormat: 'HH:MM',
            insertMode: false,
            placeholder: '_',
            clearIncomplete: true
        });

        if($field) {
            // Init the mask on the field
            timeMask.mask($field);
            // Reset the field to blank
            $field.val('').trigger('change');
        }else{
            // Get all the time fields
            let timeFields = document.querySelectorAll('.acf-field-time > .acf-input > .acf-input-wrap > input');
            // Init the mask on the fields
            timeMask.mask(timeFields);
        }
    }

    handleFieldAppended($field){
        let $input = $field.find('.acf-input > .acf-input-wrap > input');
        this.initTimeMasks($input);
    }

    init() {

        // Init input masks for times
        this.initTimeMasks();

        // Init new time masks when an item is added
        window.acf.add_action('append_field', (e) => this.handleFieldAppended(e));
    }

    // Getters and setters ---------------------------------------------------------------------------------------------

}