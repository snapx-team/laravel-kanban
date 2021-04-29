<template>
    <svg :height="size" :width="size" viewBox="0 0 300 300">
        <defs>
            <linearGradient gradientUnits="userSpaceOnUse" id="gradient-fill" x1="0" x2="300" y1="300" y2="0">
                <stop offset="0%">
                    <animate attributeName="stop-color"
                             dur="5s"
                             repeatCount="indefinite"
                             values="#00E06B;#CB0255;#00E06B"/>
                </stop>
                <stop offset="100%">
                    <animate attributeName="stop-color"
                             dur="8s"
                             repeatCount="indefinite"
                             values="#04AFC8;#8904C5;#04AFC8"/>
                </stop>
            </linearGradient>
            <clipPath id="clip">
                <rect class="square s1" height="90" rx="12" ry="12" width="90" x="0" y="0"></rect>
                <rect class="square s2" height="90" rx="12" ry="12" width="90" x="100" y="0"></rect>
                <rect class="square s3" height="90" rx="12" ry="12" width="90" x="200" y="0"></rect>
                <rect class="square s4" height="90" rx="12" ry="12" width="90" x="0" y="100"></rect>
                <rect class="square s5" height="90" rx="12" ry="12" width="90" x="200" y="100"></rect>
                <rect class="square s6" height="90" rx="12" ry="12" width="90" x="0" y="200"></rect>
                <rect class="square s7" height="90" rx="12" ry="12" width="90" x="100" y="200"></rect>
            </clipPath>
        </defs>
        <rect class="gradient" clip-path="url('#clip')" height="300" width="300"></rect>
    </svg>
</template>
<script>
    export default {
        props: {
            size: {
                value: Number,
                default: 100,
            },
        },
    };
</script>

<style scoped>
    .gradient {
        animation-iteration-count: infinite;
        animation-duration: 1s;
        fill: url("#gradient-fill");
    }

    .square {
        animation-iteration-count: infinite;
        animation-duration: 2s;
        transition-timing-function: ease-in-out;
    }

    .s1 {
        animation-name: slide-1;
    }

    .s2 {
        animation-name: slide-2;
    }

    .s3 {
        animation-name: slide-3;
    }

    .s4 {
        animation-name: slide-4;
    }

    .s5 {
        animation-name: slide-5;
    }

    .s6 {
        animation-name: slide-6;
    }

    .s7 {
        animation-name: slide-7;
    }

    @keyframes slide-1 {
        37.5% {
            transform: translateX(0px);
        }
        50% {
            transform: translateX(100px);
        }
        100% {
            transform: translateX(100px);
        }
    }

    @keyframes slide-2 {
        25% {
            transform: translateX(0px);
        }
        37.5% {
            transform: translateX(100px);
        }
        100% {
            transform: translateX(100px);
        }
    }

    @keyframes slide-3 {
        12.5% {
            transform: translateY(0px);
        }
        25% {
            transform: translateY(100px);
        }
        100% {
            transform: translateY(100px);
        }
    }

    @keyframes slide-4 {
        50% {
            transform: translateY(0px);
        }
        62.5% {
            transform: translateY(-100px);
        }
        100% {
            transform: translateY(-100px);
        }
    }

    @keyframes slide-5 {
        12.5% {
            transform: translate(-100px, 0px);
        }
        87.5% {
            transform: translate(-100px, 0px);
        }
        100% {
            transform: translate(-100px, 100px);
        }
    }

    @keyframes slide-6 {
        62.5% {
            transform: translateY(0px);
        }
        75% {
            transform: translateY(-100px);
        }
        100% {
            transform: translateY(-100px);
        }
    }

    @keyframes slide-7 {
        75% {
            transform: translateX(0px);
        }
        87.5% {
            transform: translateX(-100px);
        }
        100% {
            transform: translateX(-100px);
        }
    }
</style>
