<template>
    <div
        class="border-gray-300 border px-2 pt-2 cursor-pointer"
        @click="filterByBoard()"
        :class="clicked ? 'bg-purple-50' : 'bg-white'">
        <div class="flex">
            <div
                :class="{ 'transform rotate-90 ' : showMore }"
                class="transition duration-150 ease-in-out px-2 border rounded-full cursor-pointer hover:bg-green-50 bg-white"
                id="container"
                v-on:click.stop
                @click="expandPanel()">
                <i id="icon " class="fa fa-chevron-right"></i>
            </div>
            <p class="pl-3 font-medium text-gray-900">{{ board.name }}</p>
        </div>

        <div class="pt-2">
            <div class="flex justify-between pb-1">
                <small>Assigned Active Tasks: {{ percentAssigned }} </small>
                <small>({{ board.assigned }}/{{ board.total }})</small>
            </div>
            <div class="overflow-hidden h-3 mb-4 text-xs flex rounded bg-green-200">
                <div
                    :style="{ width: percentAssigned}"
                    class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-green-500"
                ></div>
            </div>
        </div>

        <div class="flex-1" v-if="showMore">

            <div>
                <div class="flex justify-between pb-1">
                    <small>Placed Active Tasks: {{ percentPlaced }} </small>
                    <small>({{ board.placed_in_board }}/{{ board.total }})</small>
                </div>

                <div class="overflow-hidden h-3 mb-4 text-xs flex rounded bg-blue-200">
                    <div
                        :style="{ width: percentPlaced}"
                        class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-500"
                    ></div>
                </div>
            </div>

            <table class="mb-2">
                <tbody>
                <tr>
                    <td class="w-40"><small>Active Tasks</small></td>
                    <td ><small>{{ board.active }}</small></td>
                </tr>
                <tr>
                    <td class="w-40"><small>Completed Tasks</small></td>
                    <td><small>{{ board.completed }}</small></td>
                </tr>
                <tr>
                    <td class="w-40"><small>Cancelled Tasks</small></td>
                    <td><small>{{ board.cancelled }}</small></td>
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
            let value = ((this.board.assigned / this.board.total) * 100).toFixed(2);
            if (isNaN(value))
                return '0%'
            return value + '%'
        },

        percentPlaced: function () {
            let value = ((this.board.placed_in_board / this.board.total) * 100).toFixed(2);
            if (isNaN(value))
                return '0%'
            return value + '%'
        },
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
