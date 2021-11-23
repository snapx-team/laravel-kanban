<template>
    <div class="custom-number-input h-12 w-32 text-center">

        <div
            class="border flex flex-row h-12 w-full rounded relative bg-transparent mt-1">
            <button @click="decrement"
                    class=" bg-gray-50 border-r text-gray-600 hover:text-gray-700 hover:bg-gray-200 h-full w-24 rounded-l cursor-pointer outline-none">
                <span class="m-auto text-2xl font-semibold">−</span>
            </button>

            <input @input="handleInput($event.target.value)"
                   :value="content"
                   type="number"
                   class="outline-none focus:outline-none text-center w-full font-semibold text-md hover:text-black focus:text-black flex items-center text-gray-700 outline-none"
                   name="custom-input-number"/>

            <button @click="increment"
                    class="bg-gray-50  border-l text-gray-600 hover:text-gray-700 hover:bg-gray-200 h-full w-24 rounded-r cursor-pointer">
                <span class="m-auto text-2xl font-semibold">+</span>
            </button>
        </div>
    </div>
</template>
<script>

import {ajaxCalls} from "../../mixins/ajaxCallsMixin";

export default {
    props: {
        value: {default: 0,},
    },
    data () {
        return {
            content: this.value
        }
    },
    mixins: [ajaxCalls],

    methods: {
        handleInput (value) {
            this.content = parseInt(value)


            if (this.content < 0){
                this.content = 0;
                this.triggerErrorToast('A task cannot have negative hours');
                this.$emit('input', this.content)
            }
            else if (this.content > 40){
                this.content = 40;
                this.triggerErrorToast('A task should be under 40 hours. Try breaking up the task into smaller subtasks');
                this.$emit('input', this.content)
            }
            else{
                this.$emit('input', this.content)
            }
        },
        increment(){
            if (this.content + 1 > 40){
                this.content = 40;
                this.triggerErrorToast('A task should be under 40 hours. Try breaking up the task into smaller subtasks');
                this.$emit('input', this.content)
            }
            else{
                this.$emit('input', ++this.content)
            }
        },
        decrement(){
            if (this.content - 1 < 0){
                this.content = 0
                this.triggerErrorToast('A task cannot have negative hours');
                this.$emit('input', this.content)
            }
            else{
                this.$emit('input', --this.content)
            }
        }
    }
}
</script>

<style scoped>

input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.custom-number-input input:focus {
    outline: none !important;
}

.custom-number-input button:focus {
    outline: none !important;
}

</style>
