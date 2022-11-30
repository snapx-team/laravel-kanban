<template>
    <div v-if="backlogData !== null">
        <div class="flex flex-wrap p-4 pl-10">
            <h3 class="text-3xl text-gray-800 font-bold py-1 pr-2">Backlog - </h3>
            <h3 class="text-3xl text-gray-800 py-1"> Between {{ filters.filterStart | moment("MMM Do YYYY") }} and
                {{ filters.filterEnd | moment("MMM Do YYYY") }}</h3>
        </div>

        <div class="p-5">
            <div class="flex-column">

                <!-- date filter -->
                <div class="flex flex-wrap bg-gray-50 p-4 rounded">
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
                    <button @click="setTimeSpan()"
                            class="px-4 mt-4 h-12 border border-transparent rounded text-white bg-indigo-600 hover:bg-indigo-500 transition duration-300 ease-in-out"
                            type="button">
                        <span>Set Time Range</span>
                    </button>
                </div>

                <!-- general search -->
                <div class="bg-gray-50 p-4 mt-3 rounded">

                    <div class="flex flex-wrap">
                        <div class="flex block relative mt-3">
                            <span class="absolute inset-y-0 left-0 flex items-center p-2">
                                <i class="text-gray-400 fas fa-search"></i>
                            </span>
                            <input
                                @change="filterTrigger()"
                                class="w-72 px-7 py-3 placeholder-gray-400 text-gray-700 rounded border border-gray-400 pr-10 outline-none text-md leading-4"
                                placeholder="Search by keyword"
                                type="text"
                                v-model="filters.filterText"/>
                        </div>

                        <!-- active, completed, cancelled -->
                        <div class="flex py-3 h-12 border mt-3 ml-2 bg-white rounded">
                            <label class="flex mx-3">
                                <input
                                    class="mt-2 form-radio text-indigo-600"
                                    name="task-options"
                                    type="checkbox"
                                    value="active"
                                    @change="filterTrigger()"
                                    v-model="filters.filterStatus">
                                <div class="ml-1 text-gray-700 font-medium">
                                    <p>Active</p>
                                </div>
                            </label>
                            <label class="flex mx-3">
                                <input
                                    class="mt-2 form-radio text-indigo-600"
                                    name="task-options"
                                    type="checkbox"
                                    value="completed"
                                    @change="filterTrigger()"
                                    v-model="filters.filterStatus">
                                <div class="ml-1 text-gray-700 font-medium">
                                    <p>Completed</p>
                                </div>
                            </label>
                            <label class="flex mx-3">
                                <input
                                    class="mt-2 form-radio text-indigo-600"
                                    name="task-options"
                                    type="checkbox"
                                    value="cancelled"
                                    @change="filterTrigger()"
                                    v-model="filters.filterStatus">
                                <div class="ml-1 text-gray-700 font-medium">
                                    <p>Cancelled</p>
                                </div>
                            </label>
                        </div>

                        <!-- placed in board, awaiting placement -->
                        <div class="flex py-3 h-12 border mt-3 mx-2 bg-white rounded">
                            <label class="flex mx-3">
                                <input
                                    class="mt-2 form-radio text-indigo-600"
                                    name="task-options"
                                    type="checkbox"
                                    @change="filterTrigger()"
                                    v-model="filters.filterPlacedInBoard">
                                <div class="ml-1 text-gray-700 font-medium">
                                    <p>Placed In Board</p>
                                </div>
                            </label>
                            <label class="flex mx-3">
                                <input
                                    class="mt-2 form-radio text-indigo-600"
                                    name="task-options"
                                    type="checkbox"
                                    @change="filterTrigger()"
                                    v-model="filters.filterNotPlacedInBoard">
                                <div class="ml-1 text-gray-700 font-medium">
                                    <p>Awaiting Placement</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- select filters -->
                    <div class="flex flex-wrap">
                        <div class="flex block mr-2">
                            <vSelect
                                v-model="filters.filterBadge"
                                multiple
                                :options="backlogData.badges"
                                label="name"
                                placeholder="Filter By Badge"
                                @input="filterTrigger()"
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
                                v-model="filters.filterAssignedTo"
                                multiple
                                :options="backlogData.kanbanUsers"
                                :getOptionLabel="opt => opt.user.full_name"
                                label="user.full_name"
                                placeholder="Filter By Assigned Employee"
                                @input="filterTrigger()"
                                class="w-72 flex-grow text-gray-400">
                                <template slot="option" slot-scope="option">
                                    <avatar :name="option.user.full_name" :size="4"
                                            class="mr-3 m-1 float-left"></avatar>
                                    <p class="inline">{{ option.user.full_name }}</p>
                                </template>
                                <template #no-options="{ search, searching, loading }">
                                    No result .
                                </template>
                            </vSelect>
                        </div>

                        <!-- reporter -->
                        <div class="flex block mr-2">
                            <vSelect
                                v-model="filters.filterReporter"
                                multiple
                                :options="backlogData.kanbanUsers"
                                :getOptionLabel="opt => opt.user.full_name"
                                label="name"
                                placeholder="Filter By Reporter"
                                @input="filterTrigger()"
                                class="w-72 flex-grow text-gray-400">
                                <template slot="option" slot-scope="option">
                                    <avatar :name="option.user.full_name" :size="4"
                                            class="mr-3 m-1 float-left"></avatar>
                                    <p class="inline">{{ option.user.full_name }}</p>
                                </template>
                                <template #no-options="{ search, searching, loading }">
                                    No result .
                                </template>
                            </vSelect>
                        </div>

                        <div class="flex">
                            <!-- erp employee -->
                            <div class="flex block mr-2">
                                <vSelect
                                    v-model="filters.filterErpEmployee"
                                    multiple
                                    :options="erpEmployees"
                                    :getOptionLabel="opt => opt.full_name"
                                    label="full_name"
                                    placeholder="Filter By ERP Employees"
                                    @search="onTypeEmployee"
                                    @input="filterTrigger()"
                                    class="w-72 flex-grow text-gray-400">
                                    <template slot="option" slot-scope="option">
                                        <avatar :name="option.full_name" :size="4"
                                                class="mr-3 m-1 float-left"></avatar>
                                        <p class="inline">{{ option.full_name }}</p>
                                    </template>
                                    <template #no-options="{ search, searching, loading }">
                                        No result .
                                    </template>
                                </vSelect>
                            </div>

                            <!-- erp contract -->
                            <div class="flex block mr-2">
                                <vSelect
                                    v-model="filters.filterErpContract"
                                    multiple
                                    :options="erpContracts"
                                    :getOptionLabel="opt => opt.contract_identifier"
                                    label="contract_identifier"
                                    placeholder="Filter By ERP Contracts"
                                    @search="onTypeContract"
                                    @input="filterTrigger()"
                                    class="w-72 flex-grow text-gray-400">
                                    <template slot="option" slot-scope="option">
                                        <avatar :name="option.contract_identifier" :size="4"
                                                class="mr-3 m-1 float-left"></avatar>
                                        <p class="inline">{{ option.contract_identifier }}</p>
                                    </template>
                                    <template #no-options="{ search, searching, loading }">
                                        No result .
                                    </template>
                                </vSelect>
                            </div>
                        </div>
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
                            <div v-if="backlogData.backlogTasks.total > 0" class="h-full list-group">
                                <backlog-card
                                    v-for="task in backlogTaskList"
                                    :key="task.id"
                                    :task="task"
                                    class="cursor-pointer"
                                    v-on:click.native="updateSelectedTaskInUrl(task)">
                                </backlog-card>
                            </div>

                            <button v-observe-visibility="visibilityChanged"
                                    @click="getMoreBacklogTasks"
                                    class=" text-sm bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded w-full"
                                    v-if="backlogData.backlogTasks.current_page <= backlogData.backlogTasks.last_page">
                                <span v-if="!isLoadingTasks">Load More</span>
                            </button>

                            <div>
                                <div v-if="backlogData.backlogTasks.total === 0 && !isLoadingTasks">
                                    <div
                                        class="text-2xl text-indigo-800 text-center m-auto font-bold py-10 bg-gray-100">
                                        <i class="fas fa-search mb-2 animate-bounce"></i>
                                        <p>No Tasks Found</p>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div v-if="isLoadingTasks">
                                    <div
                                        class="text-2xl text-indigo-800 text-center m-auto font-bold py-10 bg-gray-100">
                                        <i class="fas fa-circle-notch mb-2 animate-spin"></i>
                                        <p>Loading</p>
                                    </div>
                                </div>
                            </div>
                        </pane>
                        <pane v-if="showTaskPane">
                            <task-pane></task-pane>
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
import {Pane, Splitpanes} from "splitpanes";
import "splitpanes/dist/splitpanes.css";
import BacklogBar from "./backlogComponents/BacklogBar.vue";
import vSelect from "vue-select";
import {ajaxCalls} from "../../mixins/ajaxCallsMixin";
import Avatar from "../global/Avatar.vue";
import moment from "moment";
import axios from 'axios';
import _ from "lodash";

const cancellationToken = axios.CancelToken;
let tokenSource = cancellationToken.source();

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

    props: {
        taskId: Number
    },

    data() {
        return {
            task: null,
            requests: [],
            request: null,
            pageNumber: 1,
            backlogTaskList: [],
            isLoadingTasks: false,
            backlogData: null,
            erpEmployees: [],
            erpContracts: [],

            filters: {
                filterText: "",
                filterBadge: [],
                filterBoard: [],
                filterAssignedTo: [],
                filterReporter: [],
                filterErpEmployee: [],
                filterErpContract: [],
                filterStatus: ['active'],
                filterPlacedInBoard: false,
                filterNotPlacedInBoard: true,
                filterStart: this.getOneYearAgo(),
                filterEnd: new Date(),
            },
            start: this.getOneYearAgo(),
            end: new Date(),
            showBoardsPane: true,
            showTaskPane: false,
        };
    },

    mixins: [ajaxCalls],

    async mounted() {
        await this.getBacklogData();
        this.getErpEmployees();
        this.getContracts();
        this.getTaskFromUrl();
    },

    created() {
        this.moment = moment;

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
        this.eventHub.$off('fetch-and-replace-task-data');
    },

    computed: {

        startTime() {
            return this.filters.filterStart.toISOString().slice(0, 10);
        },
        endTime() {
            return this.filters.filterEnd.toISOString().slice(0, 10);
        }
    },
    methods: {

        async getBacklogData() {
            window.scrollTo({top: 0, behavior: 'smooth'});
            if (this.start && this.end) {
                this.eventHub.$emit("set-loading-state", true);
                this.isLoadingTasks = true;
                this.pageNumber = 1;
                this.backlogTaskList = [];

                await this.asyncGetBacklogData(this.startTime, this.endTime).then((data) => {
                    this.backlogData = data.data;
                    this.eventHub.$emit("set-loading-state", false);
                    this.getMoreBacklogTasks();
                })
            } else {
                this.triggerErrorToast('select a start and end time')
            }
        },

        getMoreBacklogTasks() {
            if (this.isLoadingTasks) {
                tokenSource.cancel();
            }
            this.isLoadingTasks = true;

            tokenSource = cancellationToken.source();
            this.asyncGetBacklogTasks(this.pageNumber, this.filters, tokenSource.token).then((data) => {
                this.pageNumber++;
                this.backlogData.backlogTasks = data.data;
                this.backlogTaskList = this.backlogTaskList.concat(this.backlogData.backlogTasks.data);
                this.isLoadingTasks = false;
            })
        },

        visibilityChanged(isVisible, entry) {
            if (isVisible) {
                this.getMoreBacklogTasks();
            }
        },

        filterByBoard(board) {
            let contains = this.filters.filterBoard.some(elem => {
                return board.id === elem.id;
            });
            if (contains) {
                this.filters.filterBoard = this.filters.filterBoard.filter((t) => {
                    return t.id !== board.id;
                });
            } else {
                this.filters.filterBoard.push(board);
            }
            this.filterTrigger();
        },

        filterTrigger() {
            this.pageNumber = 1;
            this.backlogTaskList = [];
            this.getMoreBacklogTasks();
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

        setTimeSpan() {
            this.filters.filterStart = this.start;
            this.filters.filterEnd = this.end;
            this.getBacklogData();
        },

        getOneYearAgo() {
            let d = new Date()
            return new Date(d.setFullYear(d.getFullYear() - 1));
        },

        getErpEmployees() {
            this.asyncGetAllUsers().then((data) => {
                this.erpEmployees = data.data;
            }).catch(res => {
                console.log(res)
            });
        },

        getContracts() {
            this.asyncGetAllContracts().then((data) => {
                this.erpContracts = data.data;
            }).catch(res => {
                console.log(res)
            });
        },

        getTaskFromUrl() {
            if (!isNaN(this.taskId)) {
                this.asyncGetTaskData(this.taskId).then((data) => {
                    this.setSideInfo(data.data);
                });
            }
        },

        updateSelectedTaskInUrl(task) {
            this.$router.replace({name: "backlog", query: {task: task.id}}).catch(() => {
            });
            this.task = task;
        },

        onTypeEmployee(search, loading) {
            if (search.length) {
                loading(true);
                this.typeEmployee(search, loading, this);
            }
        },

        typeEmployee: _.debounce(function (search, loading) {
            this.asyncGetSomeUsers(search).then((data) => {
                this.erpEmployees = data.data;
            })
                .catch(res => {
                    console.log(res)
                })
                .then(function () {
                    setTimeout(500);
                    loading(false);
                });
        }, 500),

        onTypeContract(search, loading) {
            if (search.length) {
                loading(true);
                this.typeContract(search, loading, this);
            }
        },

        typeContract: _.debounce(function (search, loading) {
            this.asyncGetSomeContracts(search).then((data) => {
                this.erpContracts = data.data;
            })
                .catch(res => {
                    console.log(res)
                })
                .then(function () {
                    setTimeout(500);
                    loading(false);
                });
        }, 500),
    }
};
</script>

<style scoped>

.splitpanes.default-theme .splitpanes__pane {
    background-color: #ffffff;
}

</style>
