import { Toast } from 'bootstrap';
import 'bootstrap';

interface ShareParams {
    text: string;
    url: string;
    via: string;
    [key: string]: string;
}

class ActionHandler {
    private container: HTMLElement;
    private readonly postTitle?: string;

    constructor() {
        this.container = document.getElementById('postTitle') as HTMLElement;
        this.postTitle = this.container.dataset.postTitle;

        this.initEventListeners();
    }

    private initEventListeners(): void {
        const shareButton = document.querySelector('[data-action="share-x"]') as HTMLButtonElement;
        shareButton.addEventListener('click', (): void => {
            const url: string = window.location.href;
            const params = new URLSearchParams(<ShareParams>{
                text: this.postTitle,
                url: url,
                via: 'tkwonn_image_hosting'
            });

            const shareUrl = `https://twitter.com/intent/tweet?${params.toString()}`;
            window.open(shareUrl, '_blank');
        });

        const copyButton = document.querySelector('[data-action="copy-url"]') as HTMLButtonElement;
        copyButton.addEventListener('click', (): void => {
            try {
                navigator.clipboard.writeText(window.location.href);
                const toastElement = document.getElementById('toast') as HTMLElement;
                const toast = new Toast(toastElement);
                toast.show();
            } catch (error) {
                console.error('Failed to copy to clipboard:', error);
                alert('This browser does not support copying to clipboard.');
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', (): void => {
    new ActionHandler();
});