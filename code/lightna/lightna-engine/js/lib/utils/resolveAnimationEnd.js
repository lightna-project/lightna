export async function resolveAnimationEnd(item) {
    return new Promise((resolve) => {
        // forces the browser to recalculate styles, ensuring the animation actually starts
        void item.offsetWidth;

        const computedStyle = window.getComputedStyle(item);
        const animationName = computedStyle.animationName;
        const animationDuration = parseFloat(computedStyle.animationDuration) || 0;

        if (animationName !== 'none' && animationDuration) {
            item.addEventListener('animationend', resolve, { once: true });
        } else {
            resolve();
        }
    });
}
