<template>
    <div v-if="backlogData !== null">
        <backlog-bar :name="'Backlog'"></backlog-bar>
        <div class="p-5">
            <div class="flex-column">

                <!-- date filter -->
                <div class="flex mb">
                    <div class="flex-column w-72 mr-2">
                    <span
                        class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Start</span>
                        <date-picker type="date" v-model="start"
                                     placeholder="YYYY-MM-DD"
                                     format="YYYY-MM-DD"
                        ></date-picker>
                    </div>
                    <div class="flex-column w-72 mr-2">
                    <span
                        class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">End</span>
                        <date-picker type="date" v-model="end"
                                     placeholder="YYYY-MM-DD"
                                     format="YYYY-MM-DD"
                        ></date-picker>
                    </div>
                    <button @click="getBacklogData()"
                            class="px-4 mt-4 h-12 border border-transparent rounded text-white bg-indigo-600 hover:bg-indigo-500 transition duration-300 ease-in-out"
                            type="button">
                        <span>Filter</span>
                    </button>
                </div>

                <!-- general search -->
                <div class="flex">
                    <div class="flex block relative mt-3">
                    <span class="absolute inset-y-0 left-0 flex items-center p-2">
                        <i class="text-gray-400 fas fa-search"></i>
                    </span>
                        <input
                            class="w-72 px-7 py-3 placeholder-gray-400 text-gray-700 rounded border border-gray-400 pr-10 outline-none text-md leading-4"
                            placeholder="Search by keyword"
                            type="text"
                            v-model="filterText"/>
                    </div>

                    <!-- active, completed, cancelled -->
                    <div class="flex py-3 h-12 border mt-3 ml-2">
                        <label class="flex mx-3">
                            <input
                                class="mt-2 form-radio text-indigo-600"
                                name="task-options"
                                type="checkbox"
                                v-model="activeBool">
                            <div class="ml-1 text-gray-700 font-medium">
                                <p>Active</p>
                            </div>
                        </label>
                        <label class="flex mx-3">
                            <input
                                class="mt-2 form-radio text-indigo-600"
                                name="task-options"
                                type="checkbox"
                                v-model="completedBool">
                            <div class="ml-1 text-gray-700 font-medium">
                                <p>Completed</p>
                            </div>
                        </label>
                        <label class="flex mx-3">
                            <input
                                class="mt-2 form-radio text-indigo-600"
                                name="task-options"
                                type="checkbox"
                                v-model="cancelledBool">
                            <div class="ml-1 text-gray-700 font-medium">
                                <p>cancelled</p>
                            </div>
                        </label>
                    </div>

                    <!-- placed in board, awaiting placement -->
                    <div class="flex py-3 h-12 border mt-3 mx-2">
                        <label class="flex mx-3">
                            <input
                                class="mt-2 form-radio text-indigo-600"
                                name="task-options"
                                type="checkbox"
                                v-model="placedInBoard">
                            <div class="ml-1 text-gray-700 font-medium">
                                <p>Placed In Board</p>
                            </div>
                        </label>
                        <label class="flex mx-3">
                            <input
                                class="mt-2 form-radio text-indigo-600"
                                name="task-options"
                                type="checkbox"
                                v-model="notPlacedInBoard">
                            <div class="ml-1 text-gray-700 font-medium">
                                <p>Awaiting Placement</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- badges -->
                <div class="flex mb-3">
                    <div class="flex block mr-2">
                        <vSelect
                            v-model="filterBadge"
                            multiple
                            :options="backlogData.badges"
                            label="name"
                            placeholder="Filter By Badge"
                            class="w-72 flex-grow text-gray-400">
                            <template slot="option" slot-scope="option">
                                <p class="inline">{{ option.name }}</p>
                            </template>
                            <template #no-options="{ search, searching, loading }">
                                No result .
                            </template>
                        </vSelect>
                    </div>

                    <!-- assigned to -->
                    <div class="flex block mr-2">
                        <vSelect
                            v-model="filterAssignedTo"
                            multiple
                            :options="backlogData.kanbanUsers"
                            :getOptionLabel="opt => opt.user.full_name"
                            label="user.full_name"
                            placeholder="Filter By Assigned Employee"
                            class="w-72 flex-grow text-gray-400">
                            <template slot="option" slot-scope="option">
                                <avatar :name="option.user.full_name" :size="4" class="mr-3 m-1 float-left"></avatar>
                                <p class="inline">{{ option.user.full_name }}</p>
                            </template>
                            <template #no-options="{ search, searching, loading }">
                                No result .
                            </template>
                        </vSelect>
                    </div>

                    <!-- reporter -->
                    <div class="flex block">
                        <vSelect
                            v-model="filterReporter"
                            multiple
                            :options="backlogData.kanbanUsers"
                            :getOptionLabel="opt => opt.user.full_name"
                            label="name"
                            placeholder="Filter By Reporter"
                            class="w-72 flex-grow text-gray-400">
                            <template slot="option" slot-scope="option">
                                <avatar :name="option.user.full_name" :size="4" class="mr-3 m-1 float-left"></avatar>
                                <p class="inline">{{ option.user.full_name }}</p>
                            </template>
                            <template #no-options="{ search, searching, loading }">
                                No result .
                            </template>
                        </vSelect>
                    </div>
                </div>
            </div>

            <hr class="mb-5"/>

            <div>
                <div>
                    <button
                        v-if="!showBoardsPane"
                        @click="showBoardsPane = !showBoardsPane"
                        class="py-4 font-semibold text-indigo-600 hover:text-indigo-800 transition duration-300 ease-in-out focus:outline-none">
                        {{ showBoardsPane ? "Hide" : "Show" }} Boards
                        <i class="fa fa-th-large ml-2"></i>
                    </button>
                </div>
                <div class="h-full">
                    <splitpanes class="default-theme">
                        <pane :size="20" :max-size="25" v-if="showBoardsPane" class="bg-indigo-50">
                            <board-pane :boards="backlogData.boards"></board-pane>
                        </pane>
                        <pane :size="50" class="flex-grow">
                            <div v-if="filtered.length > 0" class="h-full list-group">
                                <backlog-card
                                    v-for="task in filtered"
                                    :key="task.id"
                                    :task="task"
                                    class="cursor-pointer">
                                </backlog-card>
                            </div>
                            <div v-else>
                                <div class="text-2xl text-indigo-800 text-center m-auto font-bold py-10 bg-gray-100" >
                                    <i class="fas fa-search mb-2 animate-bounce"></i>
                                    <p >No Tasks Found</p>
                                </div>
                            </div>
                        </pane>
                        <pane v-if="showTaskPane">
                            <task-pane :badges="backlogData.badges"
                                       :boards="backlogData.allBoards"></task-pane>
                        </pane>
                    </splitpanes>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import draggable from "vuedraggable";
import BacklogCard from "./backlogComponents/BacklogCard.vue";
import TaskPane from "./backlogComponents/TaskPane.vue";
import BoardPane from "./backlogComponents/BoardPane.vue";
import {Splitpanes, Pane} from "splitpanes";
import "splitpanes/dist/splitpanes.css";
import BacklogBar from "./backlogComponents/BacklogBar.vue";
import vSelect from "vue-select";
import {ajaxCalls} from "../../mixins/ajaxCallsMixin";
import Avatar from "../global/Avatar.vue";

export default {
    inject: ["eventHub"],
    components: {
        BacklogCard,
        draggable,
        Splitpanes,
        Pane,
        TaskPane,
        BoardPane,
        BacklogBar,
        vSelect,
        Avatar
    },
    data() {
        return {
            backlogData: null,
            filterText: "",
            filterBadge: [],
            filterBoard: [],
            filterAssignedTo: [],
            filterReporter: [],
            start: this.getOneMonthAgo(),
            end: new Date(),
            showBoardsPane: true,
            showTaskPane: false,
            activeBool: true,
            cancelledBool: false,
            completedBool: false,
            placedInBoard: false,
            notPlacedInBoard: true,
        };
    },

    mixins: [ajaxCalls],

    mounted() {
        this.getBacklogData();
    },

    created() {
        this.eventHub.$on("open-task-view", (task) => {
            this.setSideInfo(task);
        });
        this.eventHub.$on("close-task-view", () => {
            this.closeTaskView();
        });
        this.eventHub.$on("close-board-view", () => {
            this.closeBoardView();
        });
        this.eventHub.$on("filter-by-board", (board) => {
            this.filterByBoard(board);
        });
        this.eventHub.$on("reload-backlog-data", () => {
            this.getBacklogData();
        });
    },

    beforeDestroy() {
        this.eventHub.$off('open-task-view');
        this.eventHub.$off('close-task-view');
        this.eventHub.$off('close-board-view');
        this.eventHub.$off('filter-by-board');
        this.eventHub.$off('reload-backlog-data');
    },

    computed: {
        filtered() {
            const regex = new RegExp(this.filterText, "i");
            let newArray = [];
            this.filteredWithOptions.forEach(function (value) {
                if (
                    (value.task_simple_name).match(regex) ||
                    value.name.match(regex) ||
                    value.badge.name.match(regex) ||
                    value.board.name.match(regex)
                ) {
                    newArray.push(value);
                }
            });
            return newArray;
        },

        filteredWithOptions() {
            if (this.backlogData != null) {
                let regex = new RegExp("", "i");
                return this.backlogData.backlogTasks.filter((t) => {
                    let badgeMatch = this.isBadgeMatch(t, regex);
                    let boardMatch = this.isBoardMatch(t, regex);
                    let statusMatch = this.isStatusMatch(t);
                    let assignedMatch = this.isPlacedInBoardMatch(t);
                    let assignedToMatch = this.isAssignedToEmployeeMatch(t);
                    let reporterMatch = this.isReporterMatch(t);

                    return badgeMatch && boardMatch && statusMatch && assignedMatch && assignedToMatch && reporterMatch;
                });
            } else {
                return [];
            }
        },
        startTime() {
            return this.start.toISOString().slice(0, 10);
        },
        endTime() {
            return this.end.toISOString().slice(0, 10);
        }
    },
    methods: {
        isBadgeMatch(t, regex) {
            let badgeMatch = false;
            if (this.filterBadge.length > 0) {
                this.filterBadge.every(function (value1) {
                    regex = new RegExp(value1, "i");
                    if (t.badge.name.match(regex)) {
                        badgeMatch = true;
                        return false;
                    }
                    return true;
                });
            } else {
                badgeMatch = true;
            }
            return badgeMatch;
        },
        isBoardMatch(t, regex) {
            let boardMatch = false;
            if (this.filterBoard.length > 0) {
                this.filterBoard.every(function (value2) {
                    regex = new RegExp(value2, "i");
                    if (t.board.name.match(regex)) {
                        boardMatch = true;
                        return false;
                    }
                    return true;
                });
            } else {
                boardMatch = true;
            }
            return boardMatch;
        },
        filterByBoard(board) {
            if (this.filterBoard.includes(board)) {
                // remove it from array
                let newArray = this.filterBoard.filter((t) => {
                    return t !== board;
                });
                this.filterBoard = newArray;
            } else {
                // add to array
                this.filterBoard.push(board);
            }
        },
        isAssignedToEmployeeMatch(t) {
            let isMatch = false;
            if (this.filterAssignedTo.length > 0) {
                this.filterAssignedTo.every(function (value1) {
                    t.assigned_to.every(function (value2) {
                        if (value1.id === value2.id) {
                            isMatch = true;
                            return false;
                        }
                        return true;
                    });
                });
            } else {
                isMatch = true;
            }
            return isMatch;
        },
        isReporterMatch(t) {
            let isMatch = false;
            if (this.filterReporter.length > 0) {
                this.filterReporter.every(function (value1) {
                    if (value1.id === t.reporter_id) {
                        isMatch = true;
                        return false;
                    }
                    return true;
                });
            } else {
                isMatch = true;
            }
            return isMatch;
        },
        isStatusMatch(t) {
            if (this.activeBool && t.status === "active") {
                return true;
            }
            if (this.cancelledBool && t.status === "cancelled") {
                return true;
            }
            if (this.completedBool && t.status === "completed") {
                return true;
            }
            return false;
        },
        isPlacedInBoardMatch(t) {
            if (this.placedInBoard && t.column_id != null) {
                return true;
            }
            if (this.notPlacedInBoard && t.column_id == null && t.status === "active") {
                return true;
            }
            if (!this.notPlacedInBoard && !this.placedInBoard) {
                return true;
            }
            return false;
        },
        setSideInfo(task) {
            this.showTaskPane = true;
            setTimeout(() => {
                this.eventHub.$emit("populate-task-view", task);
            }, 100);
        },
        closeTaskView() {
            this.showTaskPane = false;
        },
        closeBoardView() {
            this.showBoardsPane = false;
        },
        getBacklogData() {
            if(this.start  && this.end){
                this.eventHub.$emit("set-loading-state", true);
                this.asyncGetBacklogData(this.startTime, this.endTime).then((data) => {
                    this.backlogData = data.data;
                    this.eventHub.$emit("set-loading-state", false);
                })
            }
            else{
                this.triggerErrorToast('select a start and end time')
            }

        },
        getOneMonthAgo() {
            let d = new Date()
            return new Date(d.setMonth(d.getMonth() - 1));
        },
    },
};
</script>

<style scoped>

.splitpanes.default-theme .splitpanes__pane {
    background-color: #ffffff;
}

</style>
