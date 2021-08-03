<template>
    <div>

        <div class="flex justify-between p-2 bg-indigo-800 border-b">
            <h1 class="text-white">All Boards</h1>
            <div>
                <button @click="closeBoardView()"
                        class="focus:outline-none flex flex-col items-center text-gray-400 hover:text-gray-500 transition duration-150 ease-in-out pl-8"
                        type="button">
                    <i class="fas fa-times"></i>
                    <span class="text-xs font-semibold text-center leading-3 uppercase">Esc</span>
                </button>
            </div>
        </div>

        <div>
            <board-pane-card
                v-for="board in boards"
                :key="board.id"
                :board="board">
            </board-pane-card>
            <div></div>
        </div>
    </div>
</template>

<script>
import BoardPaneCard from "./BoardPaneCard.vue";

export default {
    inject: ["eventHub"],
    components: {
        BoardPaneCard,
    },
    props: {
        boards: {
            type: Object,
        },
    },
    methods: {
        closeBoardView() {
            this.eventHub.$emit("close-board-view");
        },
    },
};
</script>
