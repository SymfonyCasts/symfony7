import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    async close() {
        this.element.style.width = '0';

        await this.#waitForAnimation();
        this.element.remove();
    }

    #waitForAnimation() {
        return Promise.all(
            this.element.getAnimations().map((animation) => animation.finished),
        );
    }
}
