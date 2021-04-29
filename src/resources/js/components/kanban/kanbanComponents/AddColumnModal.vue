<template>
    <div>
        <transition enter-active-class="transition duration-500 ease-out transform"
                    enter-class=" opacity-0 bg-blue-200"
                    leave-active-class="transition duration-300 ease-in transform"
                    leave-to-class="opacity-0 bg-blue-200">
            <div class="overflow-auto fixed inset-0 bg-gray-700 bg-opacity-50 z-30" v-if="modalOpen"></div>
        </transition>

        <transition enter-active-class="transition duration-300 ease-out transform "
                    enter-class="scale-95 opacity-0 -translate-y-10"
                    enter-to-class="scale-100 opacity-100"
                    leave-active-class="transition duration-150 ease-in transform"
                    leave-class="scale-100 opacity-100"
                    leave-to-class="scale-95 opacity-0">
            <!-- Modal container -->

            <div class="fixed inset-0 z-40 flex items-start justify-center" v-if="modalOpen">
                <!-- Close when clicked outside -->
                <div @click="modalOpen = false" class="overflow-auto fixed h-full w-full"></div>
                <div class="flex flex-col overflow-auto z-50 w-100 bg-white rounded-md shadow-2xl m-10"
                     style="width: 700px; min-height: 300px; max-height: 80%">
                    <!-- Task heading -->
                    <div class="flex justify-between p-5 bg-indigo-800 border-b">
                        <div class="space-y-1">
                            <h1 class="text-2xl text-white pb-2">Set Shifts & Time Ranges</h1>
                            <p class="text-sm font-medium leading-5 text-gray-500">
                                Choose the number of shifts for this day and set each shift time range </p>
                        </div>
                        <div>
                            <button @click="modalOpen = false"
                                    class="focus:outline-none flex flex-col items-center text-gray-400 hover:text-gray-500 transition duration-150 ease-in-out pl-8"
                                    type="button">
                                <i class="fas fa-times"></i>
                                <span class="text-xs font-semibold text-center leading-3 uppercase">Esc</span>
                            </button>
                        </div>
                    </div>
                    <!-- Task container -->
                    <div class="overflow-auto px-8 py-6">
                        <!-- Flow options -->
                        <div class="block">
                            <p class="text-center font-bold text-red-600 py-5">
                                Updating shift times and/or changing number of shifts will remove current shifts and all
                                employees associations. </p>

                            <div v-if="columnData.numberOfShifts > 0">
                                <vue-time-ranges-picker :isTwelfthMode="true"
                                                        :stepOfMoving="0.25"
                                                        :value="rangesComputed"
                                                        @change="handleRangesChange"
                                                        class="px-10 max-w-lg m-auto"/>
                            </div>
                        </div>

                        <div class="flex flex-row justify-center h-10 rounded-lg relative bg-transparent">
                            <button @click="updateNumberOfShifts(columnData.numberOfShifts - 1)"
                                    class="bg-gray-300 text-gray-600 hover:text-gray-700 hover:bg-gray-400 h-full w-20 rounded-l cursor-pointer outline-none">
                                <span class="m-auto text-2xl font-thin">−</span>
                            </button>
                            <div class="text-center bg-gray-200 font-semibold flex items-center text-gray-700 px-5">
                                {{ columnData.numberOfShifts }}
                            </div>
                            <button @click="updateNumberOfShifts(columnData.numberOfShifts + 1)"
                                    class="bg-gray-300 text-gray-600 hover:text-gray-700 hover:bg-gray-400 h-full w-20 rounded-r cursor-pointer">
                                <span class="m-auto text-2xl font-thin">+</span>
                            </button>
                        </div>
                        <p class="text-center text-gray-600 pt-5 pb-10">
                            Use the counter below to increment/decrement the number of shifts and set the shift times
                            using the wheel. Click save to apply change. </p>
                        <div class="w-full grid sm:grid-cols-2 gap-3 sm:gap-3">
                            <button @click="modalOpen = false"
                                    class="px-4 py-3 border border-gray-200 rounded text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-600 transition duration-300 ease-in-out"
                                    type="button">
                                Cancel
                            </button>
                            <button @click="saveColumns($event)"
                                    class="px-4 py-3 border border-transparent rounded text-white bg-indigo-600 hover:bg-indigo-500 transition duration-300 ease-in-out">
                                Save Column
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>
<script>
    import VueTimeRangesPicker from "vue-time-ranges-picker-edit/src/TimeRangesPicker/index.vue";

    export default {
        inject: ["eventHub"],
        components: {VueTimeRangesPicker},
        props: {
            kanbanData: Object,
        },
        data() {
            return {
                modalOpen: false,
                isSavingColumn: false,
                columnData: {
                    rowIndex: null,
                    rowId: null,
                    ranges: [],
                    numberOfShifts: null,
                },
            };
        },

        created() {
            this.eventHub.$on("create-columns", (rowData) => {
                this.columnData.rowIndex = rowData.rowIndex;
                this.columnData.rowId = rowData.rowId;
                this.columnData.ranges = rowData.rowColumns;
                this.columnData.numberOfShifts = Object.keys(
                    this.columnData.ranges
                ).length;
                this.modalOpen = true;
            });
        },

        beforeDestroy(){
            this.eventHub.$off('create-columns');
        },

        computed: {
            rangesComputed() {
                for (const [index, column] of this.columnData.ranges.entries()) {
                    column["scaleColor"] = this.getColor(index);
                    column["startTime"] = column.shift_start;
                    column["endTime"] = column.shift_end;
                    column["name"] = column["startTime"] + " - " + column["endTime"];

                }
                return this.columnData.ranges;
            },
        },

        methods: {
            updateNumberOfShifts(value) {
                if (value > 0 && value < 11) this.columnData.numberOfShifts = value;
                this.generateNewRanges();
            },

            handleRangesChange(event) {
                var ranges = event.map(timeRange => ({shift_start: timeRange.startTime, shift_end: timeRange.endTime}));
                this.columnData.ranges = ranges;
            },

            generateNewRanges() {
                let addMinutes = 0;
                var startTime = new Date();
                startTime.setHours(0, 0, 0, 0);
                let newRanges = [];

                for (var i = 1; i <= this.columnData.numberOfShifts; i++) {
                    addMinutes = 1440 / this.columnData.numberOfShifts;
                    var endTime = new Date(startTime.getTime());

                    //rounds numbers to the nearest 15 minutes
                    var coeff = 1000 * 60 * 15;
                    endTime.setMinutes(endTime.getMinutes() + addMinutes);
                    endTime = new Date(Math.round(endTime.getTime() / coeff) * coeff);

                    let newRange = {
                        shift_start: startTime.toLocaleString("en-US", {
                            hour: "numeric",
                            minute: "numeric",
                            hour12: true,
                        }),
                        shift_end: endTime.toLocaleString("en-US", {
                            hour: "numeric",
                            minute: "numeric",
                            hour12: true,
                        }),
                    };
                    newRanges.push(newRange);
                    startTime = new Date(endTime.getTime());
                }

                this.columnData.ranges = newRanges;
            },
            getColor(index) {
                switch (index) {
                    case 0:
                        return "#059669";
                    case 1:
                        return "#1D4ED8";
                    case 2:
                        return "#6D28D9";
                    case 3:
                        return "#BE185D";
                    case 4:
                        return "#1D4ED8";
                    case 5:
                        return "#4338CA";
                    case 6:
                        return "#B45309";
                    default:
                        return "#B91C1C";
                }
            },
            saveColumns(event) {
                event.target.disabled = true;
                this.eventHub.$emit("save-columns", this.columnData);
                this.modalOpen = false;
                this.columnData.rowIndex = null;
            },
        },
    };
</script>
