<template>
    <div>
        <div v-if="kanban !== null">
            <kanban-bar :kanbanId="kanban.id"
                        :kanbanMembers="kanban.members"
                        :kanbanName="kanban.name"
                        :loadingMembers="loadingMembers"
                        :sortMethod="selectedSortMethod"></kanban-bar>

            <draggable @start="isDraggingRow = true"
                       @end="getRowChangeData($event)"
                       :animation="200"
                       :list="kanban.rows"
                       class="h-full list-group"
                       ghost-class="ghost-card"
                       :disabled="isDraggableDisabled || $role !== 'admin'"
                       :options="draggableOptions">
                <div :key="row.id" class="mx-10 my-3" v-for="(row, rowIndex) in kanban.rows">

                    <div class="border bg-gray-700 pl-3 pr-3 rounded py-2 flex justify-between"
                         v-if="loadingRow.rowId === row.id && loadingRow.isLoading ">
                        <h2 class="text-gray-100 font-medium tracking-wide animate-pulse">
                            Loading... </h2>
                    </div>

                    <div class="border bg-gray-700 pl-3 pr-3 rounded py-2 flex justify-between" v-else>

                        <div class="flex">
                            <div
                                :class="{ 'transform rotate-90 ' : !collapsedRows.includes(row.id)  }"
                                class="transition duration-150 ease-in-out text-white"
                                id="container"
                                @click="collapseRow(row.id)">
                                <i id="icon " class="fa fa-chevron-right cursor-pointer"></i>
                            </div>
                            <h2 class="text-gray-100 font-medium tracking-wide pl-3">{{ row.name }} </h2>
                        </div>

                        <a @click="createRowAndColumns(rowIndex, row.columns, row.id, row.name)"
                           v-if="$role === 'admin'"
                           class="px-2 text-gray-500 hover:text-gray-400 transition duration-300 ease-in-out focus:outline-none">
                            <i class="fas fa-edit"></i>
                        </a>
                    </div>
                    <div class="flex flex-wrap" :class="{ 'hidden' : collapsedRows.includes(row.id)  }">
                        <div class="space-x-2  flex flex-1 flex-col pt-3 pb-2 overflow-x-auto overflow-y-hidden">

                            <draggable @start="isDraggingColumn = true"
                                       @end="getColumnChangeData($event, rowIndex)"
                                       :animation="200"
                                       class="h-full list-group flex"
                                       ghost-class="ghost-card"
                                       :list="row.columns"
                                       :group="'row-'+ row.id"
                                       :disabled="isDraggableDisabled || $role !== 'admin'"
                                       :options="draggableOptions">
                                <div :key="column.id"
                                     class="flex flex-col flex-1 bg-gray-200 px-3 py-3 column-width rounded mr-4 overflow-y-auto overflow-x-hidden"
                                     style="max-height: 800px"
                                     v-for="(column, columnIndex) in row.columns">
                                    <div class="flex"
                                         v-if="loadingCards.columnId === column.id && loadingCards.isLoading ">
                                        <p class="flex-auto text-gray-700 font-semibold font-sans tracking-wide pt-1 animate-pulse">
                                            Loading... </p>
                                    </div>
                                    <div class="flex" v-else>
                                        <p class="flex-auto text-gray-700 font-semibold font-sans tracking-wide pt-1">
                                            {{ column.name }} </p>
                                        <button @click="createTaskCard(rowIndex, columnIndex)"
                                                class="w-6 h-6 bg-blue-200 rounded-full hover:bg-blue-300 mouse transition ease-in duration-200 focus:outline-none">
                                            <i class="fas fa-plus text-white"></i>
                                        </button>
                                    </div>

                                    <draggable :animation="200"
                                               :disabled="isDraggableDisabled"
                                               :list="column.task_cards"
                                               @change="getTaskChangeData($event, columnIndex, rowIndex)"
                                               class="list-group flex-col flex-1"
                                               ghost-class="ghost-card"
                                               group="tasks"
                                               @start="taskDragStart"
                                               @end="isDraggingTask=false"
                                               @add="(e) => {e.item.classList.add('hidden')}"
                                               :options="draggableOptions">
                                        <transition-group type="transition" :name="!isDraggingTask ? 'flip-list' : null"
                                                          class="flex w-full h-full flex-col">
                                            <task-card :class="{'opacity-60':isDraggableDisabled}"
                                                       :key="task_card.id"
                                                       :task_card="task_card"
                                                       class="mt-3 cursor-move"
                                                       v-for="task_card in column.task_cards"
                                                       v-on:mouseup.native="openTask(task_card)"></task-card>
                                        </transition-group>
                                    </draggable>
                                </div>
                            </draggable>
                        </div>
                    </div>
                </div>
            </draggable>

            <hr class="mt-5"/>

            <button v-if="$role === 'admin'" @click="createRowAndColumns(kanban.rows.length, [], null, null)"
                    class="text-gray-500 hover:text-gray-600 font-semibold font-sans tracking-wide bg-gray-200 rounded-lg rounded p-4 m-10 hover:bg-blue-200 mouse transition ease-in duration-200 focus:outline-none">
                <p class="font-bold inline">Create new row</p>
                <i class="pl-2 fas fa-plus"></i>
            </button>

            <kanban-task-modal :taskId="task" :kanbanData="kanban"></kanban-task-modal>
            <add-member-modal :kanbanData="kanban"></add-member-modal>
            <add-row-and-columns-modal :kanbanData="kanban"></add-row-and-columns-modal>
            <add-task-by-column-modal :kanbanData="kanban"></add-task-by-column-modal>
        </div>

        <div v-else>
            <div v-if="kanbanErrorMessage"
                 class="bg-blue-100 border-t-4 border-blue-500 rounded-b text-blue-800 px-4 py-3 shadow-md">
                <div class="flex">
                    <i class="fas fa-exclamation-triangle my-1 mx-2"></i>
                    <div>
                        <p class="font-bold">Cannot Open Kanban:</p>
                        <p class="text-sm pt-2">{{ kanbanErrorMessage }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import draggable from "vuedraggable";
import {ajaxCalls} from "../../mixins/ajaxCallsMixin";

import TaskCard from "./kanbanComponents/TaskCard.vue";
import kanbanTaskModal from "./kanbanComponents/KanbanTaskModal";
import AddMemberModal from "./kanbanComponents/AddMemberModal.vue";
import KanbanBar from "./kanbanComponents/KanbanBar.vue";
import AddRowAndColumnsModal from "./kanbanComponents/AddRowAndColumnsModal";
import AddTaskByColumnModal from "./kanbanComponents/AddTaskByColumnModal";

export default {
    inject: ["eventHub"],
    components: {
        AddRowAndColumnsModal,
        TaskCard,
        draggable,
        AddMemberModal,
        KanbanBar,
        kanbanTaskModal,
        AddTaskByColumnModal,
    },

    mixins: [ajaxCalls],

    props: {
        id: Number,
        task: Number
    },

    data() {
        return {
            kanban: null,
            loadingRow: {rowId: null, isLoading: false},
            loadingCards: {columnId: null, isLoading: false},
            loadingMembers: {memberId: null, isLoading: false},
            isDraggableDisabled: false,
            collapsedRows: [],
            selectedSortMethods: [],
            selectedSortMethod: {'name': 'index', 'value': 'index'},
            isDraggingRow: false,
            isDraggingColumn: false,
            isDraggingTask: false,
            kanbanErrorMessage: null,
        };
    },

    mounted() {
        this.getKanban(this.id);
        if (localStorage.collapsedRows) {
            this.collapsedRows = localStorage.collapsedRows.split(',').map(Number);
        }
        if (localStorage.selectedSortMethods) {
            this.selectedSortMethods = JSON.parse(localStorage.selectedSortMethods);
            this.setSelectedSortMethodFromStorage();
        }
    },

    watch: {
        id: function (newVal) {
            this.getKanban(newVal);
            this.setSelectedSortMethodFromStorage();
        },
        collapsedRows(newCollapsedRows) {
            localStorage.collapsedRows = newCollapsedRows;
        },
    },

    created() {
        this.eventHub.$on("update-task-card-data-from-kanban", (cardData) => {
            this.updateTaskCard(cardData);
        });
        this.eventHub.$on("save-members", (selectedMembers) => {
            this.saveMember(selectedMembers);
        });
        this.eventHub.$on("remove-member", (memberData) => {
            this.deleteMember(memberData);
        });
        this.eventHub.$on("save-row-and-columns", (rowData) => {
            this.saveRowAndColumns(rowData);
        });
        this.eventHub.$on("save-task", (taskData) => {
            this.saveTask(taskData);
        });
        this.eventHub.$on("delete-row", (rowData) => {
            this.deleteRow(rowData);
        });
        this.eventHub.$on("fetch-and-set-column-tasks", (task) => {
            this.fetchAndSetColumnTasks(task);
        });
        this.eventHub.$on("set-kanban-sort-method", (selectedSortMethod) => {
            this.setKanbanSortMethod(selectedSortMethod);
        });
    },

    beforeDestroy() {
        this.eventHub.$off('save-task');
        this.eventHub.$off('delete-kanban-task-cards');
        this.eventHub.$off('save-members');
        this.eventHub.$off('remove-member');
        this.eventHub.$off('save-row-and-columns');
        this.eventHub.$off('update-task-card-data-from-kanban');
        this.eventHub.$off('delete-row');
        this.eventHub.$off('show-employee-tasks');
        this.eventHub.$off('reset-show-employee-tasks');
        this.eventHub.$off('set-kanban-sort-method');
    },

    computed: {
        draggableOptions() {
            if (this.$device.mobile || this.$device.ios || this.$device.android) {
                return {delay: 400}
            }
        }
    },

    methods: {

        taskDragStart() {
            this.isDraggingTask = true;
            navigator.vibrate(200);
        },

        createTaskCard(rowIndex, columnIndex) {
            let rowName = this.kanban.rows[rowIndex].name;
            let rowId = this.kanban.rows[rowIndex].id;

            let columnName = this.kanban.rows[rowIndex].columns[columnIndex].name;
            let columnId = this.kanban.rows[rowIndex].columns[columnIndex].id;

            let boardId = this.kanban.id;

            this.eventHub.$emit("create-task", {
                rowIndex,
                rowName,
                columnId,
                rowId,
                columnIndex,
                columnName,
                boardId
            });
        },

        createRowAndColumns(rowIndex, rowColumns, rowId, rowName, boardId = this.kanban.id) {
            this.eventHub.$emit("create-row-and-columns", {
                rowIndex,
                rowColumns,
                rowId,
                rowName,
                boardId
            });
        },

        setKanbanSortMethod(selectedSortMethod) {
            this.selectedSortMethod = selectedSortMethod
            const index = this.selectedSortMethods.findIndex(e => e.id === selectedSortMethod.id);
            if (index > -1) this.selectedSortMethods[index] = selectedSortMethod;
            else this.selectedSortMethods.push(selectedSortMethod);
            localStorage.selectedSortMethods = JSON.stringify(this.selectedSortMethods);
            this.sortAllTaskCards();
        },


        sortAllTaskCards() {
            for (let row of this.kanban.rows) {
                for (let column of row.columns) {
                    this.sortTaskCards(column.task_cards)
                }
            }
        },

        sortTaskCards(taskCards) {
            switch (this.selectedSortMethod.value) {
                case 'index':
                    return taskCards.sort(function (a, b) {
                        return a.index - b.index;
                    })
                case 'deadline_desc':
                    return taskCards.sort(function (a, b) {
                        return new Date(a.deadline) - new Date(b.deadline);
                    })
                case 'created_desc':
                    return taskCards.sort(function (a, b) {
                        return new Date(b.created_at) - new Date(a.created_at);
                    })
                case 'time_estimate_desc':
                    return taskCards.sort(function (a, b) {
                        return b.time_estimate - a.time_estimate;
                    })
            }
        },

        // Whenever a user drags a card
        getTaskChangeData(event, columnIndex, rowIndex) {

            if (this.selectedSortMethod.value !== 'index')
                this.sortTaskCards(this.kanban.rows[rowIndex].columns[columnIndex].task_cards)

            this.isDraggableDisabled = true;
            let eventName = Object.keys(event)[0];
            let taskCardData = this.kanban.rows[rowIndex].columns[columnIndex].task_cards;
            let columnId = this.kanban.rows[rowIndex].columns[columnIndex].id;
            let rowId = this.kanban.rows[rowIndex].id

            switch (eventName) {
                case "moved":
                    this.asyncUpdateTaskCardIndexes(taskCardData, 'moved', this.selectedSortMethod.value, event.moved.element.id).then(() => {
                        this.isDraggableDisabled = false
                    });
                    break;
                case "added":
                    this.asyncUpdateTaskCardRowAndColumnId(columnId, rowId, event.added.element.id).then(() => {
                            this.asyncUpdateTaskCardIndexes(taskCardData, 'added', this.selectedSortMethod.value, event.added.element.id).then(() => {
                                this.asyncGetTaskCardsByColumn(columnId).then((data) => {
                                    this.kanban.rows[rowIndex].columns[columnIndex].task_cards = data.data;
                                    this.sortTaskCards(this.kanban.rows[rowIndex].columns[columnIndex].task_cards)
                                    this.isDraggableDisabled = false
                                })
                            });
                        }
                    );
                    break;
                case "removed":
                    this.asyncUpdateTaskCardIndexes(taskCardData, 'removed', this.selectedSortMethod.value, event.removed.element.id);
                    break;
                default:
                    alert('event "' + eventName + '" not handled: ');
            }
        },

        // Whenever a user drags a column
        getColumnChangeData(event, rowIndex) {
            this.isDraggingColumn = false;
            if (event.oldIndex !== event.newIndex) {
                let columns = this.kanban.rows[rowIndex].columns;
                this.isDraggableDisabled = true;
                this.asyncUpdateColumnIndexes(columns).then(() => {
                    this.getKanban(this.kanban.id);
                });
            }
        },

        // Whenever a user drags a row
        getRowChangeData(event) {
            this.isDraggingRow = false;
            if (event.oldIndex !== event.newIndex) {
                this.isDraggableDisabled = true
                this.asyncUpdateRowIndexes(this.kanban.rows).then(() => {
                    this.getKanban(this.kanban.id);
                });
            }
        },

        saveMember(selectedMembers) {
            this.loadingMembers = {memberId: null, isLoading: true}
            const cloneSelectedMembers = {...selectedMembers};
            this.asyncAddMembers(cloneSelectedMembers, this.kanban.id).then(() => {
                this.asyncGetMembers(this.kanban.id).then((data) => {
                    this.kanban.members = data.data;
                    this.loadingMembers = {memberId: null, isLoading: false}
                })
            })
        },

        deleteMember(member) {
            this.asyncDeleteMember(member.id).then(() => {
                this.loadingMembers = {memberId: member.id, isLoading: true}
                this.asyncGetMembers(this.kanban.id).then((data) => {
                    this.kanban.members = data.data;
                    this.loadingMembers = {memberId: null, isLoading: false};
                    this.getKanban(this.kanban.id);
                })
            })
        },

        updateTaskCard(cardData) {
            this.isDraggableDisabled = true;
            const cloneCardData = {...cardData};
            this.loadingCards = {columnId: cloneCardData.column_id, isLoading: true}
            this.asyncUpdateTask(cloneCardData).then(() => {
                this.asyncGetTaskCardsByColumn(cloneCardData.column_id).then((data) => {
                    this.kanban.rows[cloneCardData.row.index].columns[cloneCardData.column.index].task_cards = this.sortTaskCards(data.data);
                    this.loadingCards = {columnId: null, isLoading: false}
                    this.isDraggableDisabled = false
                })
            })
        },

        saveTask(taskData) {

            this.isDraggableDisabled = true;
            this.asyncCreateTask(taskData).then((data) => {
                this.asyncGetTaskCardsByColumn(taskData.selectedColumnId).then((data) => {
                    this.kanban.rows[taskData.selectedRowIndex].columns[taskData.selectedColumnIndex].task_cards = this.sortTaskCards(data.data);
                    this.isDraggableDisabled = false;
                })
            });
        },

        saveRowAndColumns(rowData) {
            this.isDraggableDisabled = true;
            const cloneRowData = {...rowData};
            let rowIndex = cloneRowData.rowIndex;

            if (cloneRowData.rowId !== null) {
                this.loadingRow = {rowId: this.kanban.rows[rowIndex].id, isLoading: true}
            }

            this.asyncCreateRowAndColumns(cloneRowData).then((data) => {

                if (cloneRowData.rowId !== null) {
                    this.kanban.rows[rowIndex] = data.data[0];
                } else {
                    this.kanban.rows.push(data.data[0]);
                }
                this.loadingRow = {rowId: null, isLoading: false}
                this.isDraggableDisabled = false;
            })
        },

        deleteRow(rowData) {
            this.asyncDeleteRow(rowData.rowId).then(() => {
                this.getKanban(this.kanban.id);
            })
        },

        fetchAndSetColumnTasks(task) {
            this.isDraggableDisabled = true;
            this.asyncGetTaskCardsByColumn(task.column.id).then((data) => {
                this.kanban.rows[task.row.index].columns[task.column.index].task_cards = data.data;
                this.sortTaskCards(this.kanban.rows[task.row.index].columns[task.column.index].task_cards)
                this.isDraggableDisabled = false
            })
        },

        getKanban(kanbanID) {
            this.kanbanErrorMessage = null;
            this.isDraggableDisabled = true;
            this.eventHub.$emit("set-loading-state", true);
            this.asyncGetKanbanData(kanbanID).then((data) => {
                this.kanban = data.data;
                this.eventHub.$emit("set-loading-state", false);
                this.isDraggableDisabled = false
            }).catch(error => {
                this.eventHub.$emit("set-loading-state", false);
                this.kanbanErrorMessage = error;
                this.$swal({
                    icon: 'warning',
                    title: 'Cannot Open Kanban',
                    text: error,
                    showCancelButton: true,
                    confirmButtonText: `Return To Dashboard`,
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.$router.push({name: "dashboard"})
                    }
                })
            })
        },

        openTask(task) {
            this.eventHub.$emit("update-kanban-task-cards", task);
        },

        collapseRow(rowId) {
            if (!this.collapsedRows.includes(rowId)) {          //checking weather array contain the id
                this.collapsedRows.push(rowId);               //adding to array because value doesnt exists
            } else {
                this.collapsedRows.splice(this.collapsedRows.indexOf(rowId), 1);  //deleting
            }
        },

        setSelectedSortMethodFromStorage(){
            const index = this.selectedSortMethods.findIndex(e => e.id === this.id);
            if (index > -1) this.selectedSortMethod = this.selectedSortMethods[index];
            else this.selectedSortMethod = {'name': 'index', 'value': 'index'};
        }
    },
};
</script>

<style scoped>
.column-width {
    min-width: 300px;
}

.ghost-card {
    opacity: 0.5;
    background: #F7FAFC;
    border: 1px solid #4299e1;
}

.flip-list-move {
    transition: transform 0.4s;
}


</style>
