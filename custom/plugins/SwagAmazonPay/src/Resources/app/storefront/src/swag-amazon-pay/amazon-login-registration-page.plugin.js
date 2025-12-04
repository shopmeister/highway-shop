import Plugin from 'src/plugin-system/plugin.class';

export default class AmazonLoginRegistrationPagePlugin extends Plugin {
    init() {
        this._updateFormHandling();
        this._setEmailReadOnly();
    }

    /**
     * We will have to change the form action because there are blocks missing
     */
    _updateFormHandling() {
        const registrationUrl = this.el.dataset.amazonRegistrationUrl;
        const registrationForm = this.el.querySelector('form');

        registrationForm.action = registrationUrl;
    }

    _setEmailReadOnly() {
        const emailInput = this.el.querySelector('input[name="email"]');
        emailInput.readOnly = true;
    }
}
