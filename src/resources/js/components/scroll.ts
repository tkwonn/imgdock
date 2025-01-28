export class ScrollHandler {
    private tagContainer: HTMLElement;
    private leftArrow: HTMLElement;
    private rightArrow: HTMLElement;
    private readonly boundUpdateArrows: () => void;

    constructor() {
        this.tagContainer = document.getElementById('tagContainer') as HTMLElement;
        this.leftArrow = document.querySelector('.scroll-arrow-left') as HTMLElement;
        this.rightArrow = document.querySelector('.scroll-arrow-right') as HTMLElement;

        this.boundUpdateArrows = this.updateArrows.bind(this);
        this.updateArrows();

        this.tagContainer.addEventListener('scroll', this.boundUpdateArrows);
        window.addEventListener('resize', this.boundUpdateArrows);
        this.leftArrow.addEventListener('click', (): void => this.scrollContainer(-1));
        this.rightArrow.addEventListener('click', (): void => this.scrollContainer(1));
    }

    private updateArrows(): void {
        const scrollLeft: number = this.tagContainer.scrollLeft;
        const maxScrollLeft: number = this.tagContainer.scrollWidth - this.tagContainer.clientWidth;

        if (scrollLeft > 0) {
            this.leftArrow.classList.remove('d-none');
        } else {
            this.leftArrow.classList.add('d-none');
        }

        if (scrollLeft < maxScrollLeft - 1) {
            this.rightArrow.classList.remove('d-none');
        } else {
            this.rightArrow.classList.add('d-none');
        }
    }

    private scrollContainer(direction: number): void {
        const scrollAmount: number = this.tagContainer.clientWidth;
        this.tagContainer.scrollBy({
            left: direction * scrollAmount,
            behavior: 'smooth'
        });
    }
}