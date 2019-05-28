/*jshint esversion: 6 */

import { VenueManager } from "./venue-manager";
import { InputMasker } from "./input-masker";

export class ScheduleBuilder {

    constructor() {
        this._venueManager = new VenueManager();
        this._inputMasker = new InputMasker();
    }

    init() {

        // Init the VenueManager
        this.venueManager.init();

        // Init the InputMask
        this.inputMasker.init();
    }

    // Getters and setters ---------------------------------------------------------------------------------------------

    get venueManager() {
        return this._venueManager;
    }

    set venueManager(value) {
        this._venueManager = value;
    }

    get inputMasker() {
        return this._inputMasker;
    }

    set inputMasker(value) {
        this._inputMasker = value;
    }
}

// Initialize
let scheduleBuilder = new ScheduleBuilder();
scheduleBuilder.init();
