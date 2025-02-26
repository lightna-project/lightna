import { $ } from 'lightna/engine/lib/utils/dom';
import { resolveAnimationEnd } from 'lightna/engine/lib/utils/resolveAnimationEnd';

export class PageMessage {
    static container = $('.page-messages');
    static selectors = {
        messageCloseButton: '.message__close',
        messageContent: '.message__content',
    }
    classes = {
        fadeOut: 'fade-out',
    };

    constructor(html, removeTimeout = 5000) {
        this.extendProperties();
        this.removeTimeout = removeTimeout;
        this.messageHtml = html;
        this.message = this.attachToDom();
        this.closeButton = $(PageMessage.selectors.messageCloseButton, this.message);
        this.initializeEventListeners();
        if (this.removeTimeout) {
            setTimeout(() => this.removeMessage(), this.removeTimeout);
        }
    }

    extendProperties() {}

    attachToDom() {
        const message = document.createElement('div');
        message.innerHTML = this.messageHtml;
        PageMessage.container.prepend(message);
        return message;
    }

    initializeEventListeners() {
        this.closeButton.addEventListener('click', () => this.removeMessage());
    }

    async removeMessage() {
        const messageHasHover = $(PageMessage.selectors.messageContent, this.message).matches(':hover');
        if (this.removeTimeout && messageHasHover) {
            setTimeout(() => this.removeMessage(), this.removeTimeout);
            return;
        }
        this.message.classList.add(this.classes.fadeOut);
        await resolveAnimationEnd(this.message);
        this.message.remove();
    }

    static clearAll() {
        if (this.container) {
            this.container.innerHTML = '';
        }
    }
}
