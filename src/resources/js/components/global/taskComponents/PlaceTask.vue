<template>
    <div v-if="cloneCardData"
         class="space-y-6 px-8 py-6 flex-1 bg-gray-50 border-b">
        <div class="flex space-x-3">
            <div class="flex-1 space-y-2">
                <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Kanban</span>
                <vSelect
                    :create-option="option => ({name: option.toLowerCase()})"
                    :options="boards"
                    :loading="loadingBoards"
                    class="text-gray-400"
                    label="name"
                    placeholder="Select one or more kanban boards"
                    style="margin-top: 7px"
                    v-model="cloneCardData.board"
                    @input="updateBoard()">
                    <template slot="option" slot-scope="option">
                        {{ option.name }}
                    </template>
                    <template #no-options="{ search, searching, loading }">
                        No result .
                    </template>
                </vSelect>
            </div>
        </div>

        <div class="flex space-x-3">
            <div class="flex-1 space-y-2">
                <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Row</span>
                <vSelect
                    :create-option="option => ({name: option.toLowerCase()})"
                    :options="rows"
                    :loading="loadingRows"
                    class="text-gray-700"
                    label="name"
                    placeholder="Choose Row"
                    style="margin-top: 7px"
                    taggable
                    @input="clearAndGetColumn()"
                    v-model="cloneCardData.row">
                    <template #no-options="{ search, searching, loading }">
                        No result .
                    </template>
                </vSelect>
            </div>
            <div class="flex-1 space-y-2">
                <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Column</span>
                <vSelect
                    :create-option="option => ({name: option.toLowerCase()})"
                    :options="columns"
                    :loading="loadingColumns"
                    class="text-gray-700"
                    label="name"
                    placeholder="Choose or Create"
                    style="margin-top: 7px"
                    taggable
                    v-model="cloneCardData.column">
                    <template #no-options="{ search, searching, loading }">
                        No result .
                    </template>
                </vSelect>
            </div>
        </div>
        <button @click="assignTask($event)"
                class="px-4 py-3 border border-transparent rounded text-white bg-indigo-600 hover:bg-indigo-500 transition duration-300 ease-in-out"
                type="button">
            <span>Place Task</span>
        </button>
    </div>
</template>

<script>
import Avatar from "../Avatar.vue";
import Badge from "../Badge";
import TaskCard from "../../kanban/kanbanComponents/TaskCard";
import {ajaxCalls} from "../../../mixins/ajaxCallsMixin";
import vSelect from "vue-select";

export default {
    inject: ["eventHub"],
    components: {
        vSelect,
    },
    mixins: [ajaxCalls],
    props: {
        task: Object,
    },
    data() {
        return {
            rows: [],
            columns: [],
            boards: [],
            cloneCardData: null,
            loadingBoards: false,
            loadingRows: false,
            loadingColumns: false,
        }
    },

    mounted() {
        this.cloneCardData = {...this.task}
        this.getBoards();
        this.loadRowsAndColumns();
    },

    methods: {
        getBoards() {
            this.loadingBoards = true;
            this.asyncGetBoards().then((data) => {
                this.boards = data.data;
                this.loadingBoards = false;
            })
        },
        loadRowsAndColumns() {
            this.getRows(this.cloneCardData.board.id);
            if (this.cloneCardData.row && this.cloneCardData.row.id) {
                this.getColumns();
            } else {
                this.columns = []
            }
        },
        getRows(board_id) {
            this.loadingRows = true;
            this.asyncGetRows(board_id).then((data) => {
                this.rows = data.data;
                this.loadingRows = false;
            })
        },
        clearAndGetColumn() {
            this.cloneCardData.column = null;
            this.getColumns();
        },

        getColumns() {
            this.loadingColumns = true;
            this.asyncGetColumns(this.cloneCardData.row.id).then((data) => {
                this.columns = data.data;
                this.loadingColumns = false;
            })
        },
        updateBoard() {
            this.cloneCardData.row = null;
            this.cloneCardData.column = null;
            this.loadRowsAndColumns()
        },

        assignTask() {
            if (this.cloneCardData.row === null || this.cloneCardData.column === null) {
                this.triggerErrorToast('row and column need to be selected!');
            } else {
                let taskPlacementData = {
                    taskId: this.cloneCardData.id,
                    boardId: this.cloneCardData.board.id,
                    rowId: this.cloneCardData.row.id,
                    columnId: this.cloneCardData.column.id
                }
                this.asyncPlaceTask(taskPlacementData).then(() => {
                    this.eventHub.$emit("fetch-and-replace-task-data");
                    window.scrollTo({top: 0, behavior: 'smooth'});
                })
            }
        },
    },
};
</script>

