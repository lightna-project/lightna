const animations = {};
const keyframes = {};

// usp slide animation, from 2 to 5 items
const slideAnimation = () => {
    Array.from([2, 3, 4, 5]).forEach(item => {
        const animationName = `slide-${item}`;
        const animationDuration = `${item * 3}s`; // 1.5s for play and pause state
        const maxIterations = item * 2;
        const iterationValue = 100 / (maxIterations - 1);
        let transformValue = 100;

        animations[animationName] = `${animationName} ${animationDuration} ease-in-out infinite`;
        keyframes[animationName] = {};

        for (let i = 0; i < maxIterations; i++) {
            transformValue = i % 2 === 0 ? transformValue - 100 : transformValue;
            keyframes[`slide-${item}`][`${i * iterationValue}%`] = {transform: `translateX(${transformValue}%)`};
        }
    });
}

slideAnimation();

// fade in animation
animations['fade-in'] = 'fade-in .2s ease-in-out forwards';
keyframes['fade-in'] = {
    '0%': {opacity: 0},
    '100%': {opacity: 1}
};

// fade out animation
animations['fade-out'] = 'fade-out .2s ease-in-out forwards';
keyframes['fade-out'] = {
    '0%': {opacity: 1},
    '100%': {opacity: 0}
};

module.exports = { animations, keyframes };
