export const helperFunctions = {

    methods: {
        generateHexColorWithText(input) {
            let hash = 0;
            for (let i = 0; i < input.length; i++) {
                hash = input.charCodeAt(i) + ((hash << 5) - hash);
            }
            let c = (hash & 0x00ffffff).toString(16).toUpperCase();
            return '00000'.substring(0, 6 - c.length) + c;
        },

        generateHslColorWithText(input) {
            let hue = 0;
            for (let i = 0; i < input.length; i++) {
                hue += input.charCodeAt(i);
            }
            return hue % 360;
        },
    }
};
