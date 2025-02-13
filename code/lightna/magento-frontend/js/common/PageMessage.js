import { $ } from 'lightna/engine/lib/utils/dom';

export class PageMessage {
    removeTimeout = 5000;
    static container = $('.page-messages');

    constructor(html) {
        this.messageHtml = html;
        this.message = this.attachToDom();
        this.closeButton = $('.message__close', this.message);
        this.addCloseListener();
        setTimeout(() => this.remove(), this.removeTimeout);
    }

    attachToDom() {
        const message = document.createElement('div');
        message.innerHTML = this.messageHtml;
        PageMessage.container.prepend(message);

        return message;
    }

    addCloseListener() {
        this.closeButton.addEventListener('click', () => this.remove());
    }

    remove() {
        if ($('.message__content', this.message).matches(':hover')) {
            setTimeout(() => this.remove(), this.removeTimeout);
            return;
        }
        this.message.classList.add('fade-out');
        setTimeout(() => this.message.remove(), 400);
    }

    static clearAll() {
        if (this.container) {
            this.container.innerHTML = '';
        }
    }
}
