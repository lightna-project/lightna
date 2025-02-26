import { $ } from 'lightna/engine/lib/utils/dom';

export class PageMessage {
    classes = {
        fadeOut: 'fade-out',
    };
    selectors = {
        messageCloseButton: '.message__close',
        messageContent: '.message__content',
    }
    static container = $('.page-messages');

    constructor(html, removeTimeout = 5000) {
        this.removeTimeout = removeTimeout;
        this.messageHtml = html;
        this.message = this.attachToDom();
        this.closeButton = $(this.selectors.messageCloseButton, this.message);
        this.initializeEventListeners();
        if (this.removeTimeout) {
            setTimeout(() => this.remove(), this.removeTimeout);
        }
    }

    attachToDom() {
        const message = document.createElement('div');
        message.innerHTML = this.messageHtml;
        PageMessage.container.prepend(message);
        return message;
    }

    initializeEventListeners() {
        this.closeButton.addEventListener('click', () => this.remove());
    }

    remove() {
        const messageHasHover = $(this.selectors.messageContent, this.message).matches(':hover');
        if (this.removeTimeout && messageHasHover) {
            setTimeout(() => this.remove(), this.removeTimeout);
            return;
        }
        this.message.classList.add(this.classes.fadeOut);
        this.message.addEventListener('transitionend', () => this.message.remove(), { once: true });
    }

    static clearAll() {
        if (this.container) {
            this.container.innerHTML = '';
        }
    }
}
