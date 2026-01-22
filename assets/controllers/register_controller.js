import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'checkbox', 'submit'];

    connect() {
        this.toggle();
    }

    toggle() {
        const inputsFilled = this.inputTargets.every(input => {
            return input.value.trim().length > 0;
        });

        const checkboxChecked = this.checkboxTarget.checked;

        this.submitTarget.disabled = !(inputsFilled && checkboxChecked);
    }
}
