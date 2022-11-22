<template>
    <div class="container">
        <h2 class="pb-3">{{ message }}</h2>
        <div v-for="task in tasks"
             class="flex items-center">
            <button
                class="flex-none border border-blue-700 hover:bg-blue-500 py-1 px-3 my-1 rounded transition duration-300 ease-in-out"
                @click.stop="copyToClipboard(task.id)">
                <i class="fas fa-link pr-1"></i>
                Copy Link
            </button>
            <p class="px-2">to</p>
            <badge :name="task.board.name"></badge>
        </div>

    </div>
</template>

<script>

import Badge from "../Badge";

export default {
    components: {Badge},
    props: {
        message: {
            type: String,
        },
        tasks: {
            type: Array,
        },
    },

    methods: {
        clicked() {
            this.$emit("myClick");
        },

        copyToClipboard(id) {

            let temp = document.createElement('input'),
                text = window.location.hostname + '/kanban/backlog?task='+ id;

            document.body.appendChild(temp);
            temp.value = text;
            temp.select();
            document.execCommand('copy');
            document.body.removeChild(temp);

            this.$toast.success("link copied to clipboard", {
                position: 'bottom-right',
                timeout: 5000,
                closeOnClick: true,
                pauseOnFocusLoss: true,
                pauseOnHover: true,
                draggable: true,
                draggablePercent: 0.6,
                showCloseButtonOnHover: false,
                hideProgressBar: false,
                closeButton: 'button',
                icon: true,
                rtl: false
            });
        }
    }
};
</script>
