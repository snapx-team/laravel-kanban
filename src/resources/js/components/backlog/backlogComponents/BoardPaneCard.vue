<template>
    <div
        class="border-gray-300 border px-2 pt-2"
        @click="filterByBoard()"
        :class="clicked ? 'unclick-theme' : 'click-theme'">
        <div class="flex">
            <div
                :class="{ 'transform rotate-90 ' : showMore }"
                class="transition duration-150 ease-in-out"
                id="container"
                v-on:click.stop
                @click="expandPanel()">
                <i id="icon " class="fa fa-chevron-right cursor-pointer"></i>
            </div>
            <p class="pl-3">{{ board.name }}</p>
        </div>

        <div class="pt-1">
            <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-green-200">
                <div
                    :style="{ width: percentAssigned}"
                    class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-green-500"
                ></div>
            </div>
        </div>

        <div class="flex-1" v-if="showMore">
            <table>
                <tbody>
                <tr>
                    <td class="w-40 pb-2">Active</td>
                    <td>{{ board.active }}</td>
                </tr>
                <tr>
                    <td class="pb-2">Completed</td>
                    <td>{{ board.completed }}</td>
                </tr>
                <tr>
                    <td class="pb-2">Cancelled</td>
                    <td>{{ board.cancelled }}</td>
                </tr>
                <tr>
                    <td class="pb-2">Assigned</td>
                    <td>{{ board.percent }}</td>
                </tr>
                <tr>
                    <td class="pb-2">Unassigned</td>
                    <td>{{ board.unassigned }}</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
export default {
    inject: ["eventHub"],
    components: {},
    data() {
        return {
            showMore: false,
            clicked: false,
        };
    },
    props: {
        board: {
            type: Object,
        },
    },
    computed: {
        percentAssigned: function () {
            return this.board.percent / this.board.total * 100 + '%'
        }
    },
    methods: {
        expandPanel() {
            this.showMore = !this.showMore;
        },
        filterByBoard() {
            this.eventHub.$emit("filter-by-board", this.board);
            this.clicked = !this.clicked;
        },
    },
};
</script>

<style scoped>
.click-theme {
    background-color: #ffffff;
}

.unclick-theme {
    background-color: #acd0f1;
}
</style>
