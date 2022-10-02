const utilityHelper = {
    debounce: (fn, delay) => {
        let timeoutID = null;
        return function () {
            clearTimeout(timeoutID);
            let args = arguments;
            let that = this;
            timeoutID = setTimeout(function () {
                fn.apply(that, args);
            }, delay);
        };
    },

    formatMoney(value, currency) {
        const formatter = new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency || 'PHP'
        });

        return formatter.format(value);
    }
};

export default utilityHelper;
