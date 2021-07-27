<template>
    <div :style="`background-color: hsl( ${hue} , 45%, 90%); color: hsl( ${hue} , 50%, 45%)`"
         class="px-3 py-1 rounded text-xs font-semibold flex items-center">
        <span class="w-2 h-2 rounded-full mr-1" :style="`background-color: hsl( ${hue} , 45%, 40%)`"></span>
        <span>{{ name }}</span>
    </div>
</template>
<script>

import {helperFunctions} from "../../mixins/helperFunctionsMixin";

export default {

    mixins: [helperFunctions],

    props: {
        name: {
            type: String,
            default: "---",
        },
    },

    computed: {
        hue() {
            return this.generateHslColorWithText(this.name);
        },
    },

};
</script>
