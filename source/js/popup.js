import initPassViewSwitcher from "./pass-view-switcher.js";


class Popup {

    initLogin = () => {

        this.popup = document.querySelector('#login-popup');

        this.renderSendCode();
    }

    initChangePhone = () => {

        this.popup = document.querySelector('#change-phone-number-popup');

        this.renderChangePhoneSendCode();
    }

    renderSendCode = () => {
        const sendCodeTemplate = document.querySelector('#login-send-code').content.cloneNode(true);
        this.render(sendCodeTemplate);

        const thisSection = this.popup.querySelector('.custom-popup-section');
        const sendCodeSubmit = thisSection.querySelector('.custom-popup-submit');
        const telInput = thisSection.querySelector('input[type="tel"]');
        const loginPassLink = thisSection.querySelector('.login-pass-link');

        this.setMask(telInput);

        this.addFormListeners(sendCodeSubmit, 'login_send_code', ['phone'], this.renderSmsCode);

        this.prevSection = 'send_code';

        this.addLoginPassListener(loginPassLink);
    }

    renderChangePhoneSendCode = () => {
        const sendCodeTemplate = document.querySelector('#change-phone-number-send-code').content.cloneNode(true);
        this.render(sendCodeTemplate);

        const thisSection = this.popup.querySelector('.custom-popup-section');
        const sendCodeSubmit = thisSection.querySelector('.custom-popup-submit');
        const telInput = thisSection.querySelector('input[type="tel"]');

        this.setMask(telInput);

        this.addFormListeners(sendCodeSubmit, 'change_phone_send_code', ['phone'], this.renderChangePhoneSmsCode);
    }

    setMask = (inp) => {
        $(inp).mask('+79999999999');
    }

    addFormListeners = (submitBtn, action, inputNamesArr, successCb = null) => {
        submitBtn.onclick = (e) => {
            e.preventDefault();
            const thisBtn = e.target;
            this.addLoading(thisBtn);

            const thisForm = e.target.closest('form');
            const thisSection = e.target.closest('.custom-popup-section');

            this.ajaxSend(action, this.getFormValues(thisForm, inputNamesArr), (res) => {

                this.removeLoading(thisBtn);

                this.removeWarning(thisSection);
                this.removeInvalidInputs(thisSection);

                console.log(res);

                if (res['console_message']) {
                    console.log(res['console_message']);
                }

                if (res['error_message']) {
                    this.addWarnings(thisSection, res['error_message']);
                }

                if (res['error_fields']) {
                    this.setInvalidInputs(thisSection, res['error_fields']);
                }

                if (res['phone_number']) {
                    this.phoneNumber = res['phone_number'];
                }

                if (res['reload']) {
                    location.reload();
                }

                if (res['success'] && typeof successCb === 'function') {
                    successCb();
                }
            });
        }
    }

    addLoginPassListener = (link) => {
        link.onclick = (e) => {
            e.preventDefault();
            this.renderLoginPass();
        }
    }

    addLoading = (btn) => {
        btn.classList.add('loading');
        const bodyOverlay = document.createElement('div');
        bodyOverlay.classList.add('popup-body-overlay');
        document.body.prepend(bodyOverlay);
        const innerOverlay = document.createElement('div');
        innerOverlay.classList.add('popup-inner-overlay');
        this.popup.prepend(innerOverlay);
    };

    removeLoading = (btn) => {
        btn.classList.remove('loading');
        const bodyOverlay = document.querySelector('.popup-body-overlay');
        bodyOverlay.remove();
        const innerOverlay = this.popup.querySelector('.popup-inner-overlay');
        innerOverlay.remove();
    }

    getFormValues = (formSelector, namesArr) => {
        const valuesObj = {};
        namesArr.forEach(name => {
            const input = formSelector.querySelector(`input[name="${name}"]`);
            valuesObj[name] = input.value;
        });

        return valuesObj;
    }

    addWarnings = (section, warnings) => {
        const heading = section.querySelector('h2');

        const warningsWrapper = document.createDocumentFragment();

        warnings.forEach((it) => {
            const warning = document.createElement('div');
            warning.classList.add('custom-popup-warning');
            warning.textContent = it;
            warningsWrapper.append(warning);
        });

        heading.after(...warningsWrapper.children);
    }

    removeWarning = (section) => {
        const warnings = section.querySelectorAll('.custom-popup-warning');

        if (warnings.length) {
            warnings.forEach(it => it.remove());
        }
    }

    setInvalidInputs = (section, inpArr) => {
        inpArr.forEach(inp => {
            const invalidInput = section.querySelector(`input[name="${inp}"]`);
            invalidInput.classList.add('invalid');
        });
    }

    removeInvalidInputs = (section) => {
        const inputs = section.querySelectorAll('input.invalid');
        if (inputs.length) {
            inputs.forEach(inp => {
                inp.classList.remove('invalid');
            });
        }
    }

    ajaxSend = (action, dataObject, callback) => {
        const data = new FormData();
        data.append('action', action);
        data.append('nonce_code', dataObj.nonce);

        if (dataObject) {
            Object.entries(dataObject).forEach((([key, value]) => {
                data.append(key, value);
            }));
        }

        fetch(dataObj.ajaxurl, {
            method: 'POST',
            body: data
        })
            .then(response => response.json())
            .then(callback)
            .catch(err => console.log(err));
    }

    render = (template, setWide = false) => {
        this.clear(setWide);
        this.popup.append(template);
    }

    clear = (setWide = false) => {

        if (setWide && !this.popup.classList.contains('custom-popup--wide')) {
            this.popup.classList.add('custom-popup--wide');
        } else {
            this.popup.classList.remove('custom-popup--wide');
        }

        const popupSection = this.popup.querySelector('.custom-popup-section');

        if (popupSection) popupSection.remove();
    }

    renderSmsCode = () => {
        const smsTemplate = document.querySelector('#login-sms').content.cloneNode(true);
        this.render(smsTemplate);

        const thisSection = this.popup.querySelector('.custom-popup-section');
        const passSubmit = thisSection.querySelector('.custom-popup-submit');
        const counterBlock = thisSection.querySelector('.counter-block');

        this.initCounter(counterBlock);

        this.addFormListeners(passSubmit, 'login_send_sms', ['sms'], this.renderProfile);
    }

    renderChangePhoneSmsCode = () => {
        const smsTemplate = document.querySelector('#change-phone-number-sms').content.cloneNode(true);
        this.render(smsTemplate);

        const thisSection = this.popup.querySelector('.custom-popup-section');
        const passSubmit = thisSection.querySelector('.custom-popup-submit');
        const counterBlock = thisSection.querySelector('.counter-block');

        this.initCounter(counterBlock);

        this.addFormListeners(passSubmit, 'change_phone_send_sms', ['sms']);
    }

    renderProfile = () => {
        const profileTemplate = document.querySelector('#login-profile').content.cloneNode(true);
        this.render(profileTemplate, true);

        const thisSection = this.popup.querySelector('.custom-popup-section');
        const profileSubmit = thisSection.querySelector('.custom-popup-submit');
        const phoneInp = thisSection.querySelector('input[type="tel"]');

        if (this.phoneNumber) phoneInp.value = this.phoneNumber;

        initPassViewSwitcher(thisSection);

        this.addFormListeners(profileSubmit, 'create_customer', [
            'login-phone',
            'login-email',
            'login-first-name',
            'login-second-name',
            'login-pass-first',
            'login-pass-second'
        ], this.renderProfile)
    }

    renderLoginPass = () => {
        const passTemplate = document.querySelector('#login-pass').content.cloneNode(true);
        this.render(passTemplate);

        const thisSection = this.popup.querySelector('.custom-popup-section');
        const passSubmit = thisSection.querySelector('.custom-popup-submit');
        const backlink = thisSection.querySelector('.login-popup-backlink');

        initPassViewSwitcher(thisSection);

        this.addBacklinkListener(backlink);
        this.addFormListeners(passSubmit, 'authorize', ['login', 'pass']);
    }

    addBacklinkListener = (link) => {
        link.onclick = (e) => {
            e.preventDefault();

            if (this.prevSection === 'send_code') {
                this.renderSendCode();
            }
            if (this.prevSection === 'send_sms') {
                this.renderSmsCode();
            }
        };
    }

    initCounter = (block) => {

        let count = 45;

        let timerId = setInterval(() => {

            block.innerHTML = '';

            if (count != 0) {
                block.innerHTML = `<p class="counter-text">?????????????????? ?????? ???????????????? ?????????? ?????????? ${count}&nbsp;??????.</p>`;
                count--;
            } else {
                clearInterval(timerId);
                block.innerHTML = '<a class="counter-link" href="#">?????????????????? ?????? ????????????????</a>';

                const counterLink = block.querySelector('.counter-link');

                counterLink.onclick = (e) => {
                    e.preventDefault();
                    counterLink.classList.add('loading');

                    this.ajaxSend('send_code_again', null, (res) => {
                        if (res['console_message']) {
                            console.log(res['console_message']);
                        }

                        if (res['success']) {
                            this.initCounter(block);
                        }
                    });
                }
            }
            
        }, 1000);
    }
}

export default function initPopups() {

    document.addEventListener("DOMContentLoaded", () => {

        const $accountLink = $('.account-link.logged-out, .login-link');

        const loginPopup = new Popup();

        $accountLink.magnificPopup({
            type: 'inline',
            midClick: true,
            callbacks: {
                open: loginPopup.initLogin,
                close: loginPopup.clear
            }
        });

        const $changePhoneLink = $('.change-phone-number-link');

        if ($changePhoneLink) {
            const changePhonePopup = new Popup();

            $changePhoneLink.magnificPopup({
                type: 'inline',
                midClick: true,
                callbacks: {
                    open: changePhonePopup.initChangePhone,
                    close: changePhonePopup.clear
                }
            });
        }

    });
}