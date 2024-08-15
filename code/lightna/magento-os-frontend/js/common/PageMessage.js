export class PageMessage {
    removeTimeout = 5000;

    constructor(html) {
        this.messageHtml = html;
        this.message = this.attachToDom();
        this.closeButton = this.message.querySelector('.message__close');
        this.addCloseListener();
        setTimeout(() => this.remove(), this.removeTimeout);
    }

    attachToDom() {
        const message = document.createElement('div');
        message.innerHTML = this.messageHtml;
        document.querySelector('.page-messages').prepend(message);

        return message;
    }

    addCloseListener() {
        this.closeButton.addEventListener('click', () => this.remove());
    }

    remove() {
        if (this.message.querySelector('.message__content').matches(':hover')) {
            setTimeout(() => this.remove(), this.removeTimeout);
            return;
        }
        this.message.classList.add('fade-out');
        setTimeout(() => this.message.remove(), 200);
    }
}
