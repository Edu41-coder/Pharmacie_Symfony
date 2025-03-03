import { Controller } from '@hotwired/stimulus';
import { Modal } from 'bootstrap';

export default class extends Controller {
    static targets = ["modal"];
    
    connect() {
        console.log("Admin modal controller connected");
        if (this.hasModalTarget) {
            this.modal = new Modal(this.modalTarget);
        } else {
            console.error("Modal target not found during connection");
        }
    }
    
    open(event) {
        event.preventDefault();
        if (this.modal) {
            this.modal.show();
        } else if (this.hasModalTarget) {
            // Fallback si le modal n'a pas été initialisé correctement
            this.modal = new Modal(this.modalTarget);
            this.modal.show();
        } else {
            console.error("Cannot open modal: target not found");
        }
    }
    
    close() {
        if (this.modal) {
            this.modal.hide();
        }
    }
}
