(function (window) {
    function CustomFeedbackForm(options) {
        this.root = document.getElementById(options.rootId);
        this.form = this.root ? this.root.querySelector('form') : null;
        this.resultNode = this.root ? this.root.querySelector('[data-role="result"]') : null;
        this.componentName = options.componentName;
        this.signedParameters = options.signedParameters;
        this.siteId = options.siteId;

        if (this.form) {
            this.bindEvents();
        }
    }

    CustomFeedbackForm.prototype.bindEvents = function () {
        this.form.addEventListener('submit', this.onSubmit.bind(this));
    };

    CustomFeedbackForm.prototype.onSubmit = function (event) {
        event.preventDefault();
        var formData = new FormData(this.form);

        if (window.grecaptcha) {
            var token = grecaptcha.getResponse();
            formData.set('GOOGLE_CAPTCHA_TOKEN', token || '');
        }

        var yandexToken = this.form.querySelector('input[name="smart-token"]');
        if (yandexToken && yandexToken.value) {
            formData.set('YANDEX_CAPTCHA_TOKEN', yandexToken.value);
        }

        BX.ajax.runComponentAction(this.componentName, 'submit', {
            mode: 'class',
            signedParameters: this.signedParameters,
            data: formData
        }).then(function (response) {
            this.showMessage(response.data.message || 'OK', true);
            this.form.reset();
        }.bind(this)).catch(function (response) {
            var errorMessage = (response.errors && response.errors[0] && response.errors[0].message)
                ? response.errors[0].message
                : 'Error';
            this.showMessage(errorMessage, false);
        }.bind(this));
    };

    CustomFeedbackForm.prototype.showMessage = function (message, success) {
        if (!this.resultNode) {
            return;
        }

        this.resultNode.className = 'cf-form__result ' + (success ? 'is-success' : 'is-error');
        this.resultNode.textContent = message;
    };

    window.CustomFeedbackForm = CustomFeedbackForm;
})(window);
