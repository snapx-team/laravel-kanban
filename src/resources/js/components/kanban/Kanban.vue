<template>
    <div v-if="kanban !== null">
        <kanban-bar :kanbanId="kanban.id"
                    :kanbanMembers="kanban.members"
                    :kanbanName="kanban.name"
                    :loadingMembers="loadingMembers"></kanban-bar>

        <draggable @end="getRowChangeData($event)"
                   :animation="200"
                   :list="kanban.rows"
                   class="h-full list-group"
                   ghost-class="ghost-card"
                   :disabled="isDraggableDisabled"
        >

            <div :key="row.id" class="mx-10 my-3" v-for="(row, rowIndex) in kanban.rows">

                <div class="border bg-gray-700 pl-3 pr-3 rounded py-2 flex justify-between"
                     v-if="loadingRow.rowId === row.id && loadingRow.isLoading ">
                    <h2 class="text-gray-100 font-medium tracking-wide animate-pulse">
                        Loading... </h2>
                </div>
                <div class="border bg-gray-700 pl-3 pr-3 rounded py-2 flex justify-between" v-else>
                    <h2 class="text-gray-100 font-medium tracking-wide">
                        {{ row.name }} </h2>

                    <a @click="createRowAndColumns(rowIndex, row.columns, row.id, row.name)"
                       class="px-2 text-gray-500 hover:text-gray-400 transition duration-300 ease-in-out focus:outline-none">
                        <i class="fas fa-business-time"></i>
                    </a>
                </div>
                <div class="flex flex-wrap">
                    <div class="space-x-2  flex flex-1 flex-col pt-3 pb-2 overflow-x-auto overflow-y-hidden">

                        <draggable :animation="200"
                                   class="h-full list-group flex"
                                   ghost-class="ghost-card"
                                   :list="row.columns"
                                   :group="'row-'+ row.id"
                                   :disabled="isDraggableDisabled"
                                   @end="getColumnChangeData($event, rowIndex)">
                            <div :key="column.id"
                                 class="flex-1 bg-gray-200 px-3 py-3 column-width rounded mr-4"
                                 v-for="(column, columnIndex) in row.columns">
                                <div class="flex" v-if="loadingCards.columnId === column.id && loadingCards.isLoading ">
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
                                           class="h-full list-group"
                                           ghost-class="ghost-card"
                                           group="tasks">
                                    <task-card :class="{'opacity-60':isDraggableDisabled}"
                                               :key="task_card.id"
                                               :task_card="task_card"
                                               class="mt-3 cursor-move"
                                               v-for="task_card in column.task_cards"
                                               v-on:click.native="openTask(task_card)"></task-card>
                                </draggable>
                            </div>
                        </draggable>
                    </div>
                </div>
            </div>

        </draggable>

        <hr class="mt-5"/>

        <button @click="createRowAndColumns(kanban.rows.length, [], null, null)"
                class="text-gray-500 hover:text-gray-600 font-semibold font-sans tracking-wide bg-gray-200 rounded-lg rounded p-4 m-10 hover:bg-blue-200 mouse transition ease-in duration-200 focus:outline-none">
            <p class="font-bold inline">Create new row</p>
            <i class="pl-2 fas fa-plus"></i>
        </button>

        <kanban-task-modal :kanbanData="kanban"></kanban-task-modal>
        <add-member-modal :kanbanData="kanban"></add-member-modal>
        <add-row-and-columns-modal :kanbanData="kanban"></add-row-and-columns-modal>
        <add-task-by-column-modal :kanbanData="kanban"></add-task-by-column-modal>

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

    props: {'id': Number},

    data() {
        return {
            kanban: null,
            loadingRow: {rowId: null, isLoading: false},
            loadingCards: {columnId: null, isLoading: false},
            loadingMembers: {memberId: null, isLoading: false},
            isDraggableDisabled: false
        };
    },

    mounted() {
        this.getKanban(this.id);
    },

    watch: {
        id: function (newVal) {
            this.getKanban(newVal);
        }
    },

    created() {
        this.eventHub.$on("update-task-card-data", (cardData) => {
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
        this.eventHub.$on("fetch-and-set-column-tasks", (task)=> {
            this.fetchAndSetColumnTasks(task);
        });

    },

    beforeDestroy() {
        this.eventHub.$off('save-task');
        this.eventHub.$off('delete-kanban-task-cards');
        this.eventHub.$off('save-members');
        this.eventHub.$off('remove-member');
        this.eventHub.$off('save-row-and-columns');
        this.eventHub.$off('update-task-card-data');
        this.eventHub.$off('delete-row');

    },

    methods: {
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

        // Whenever a user drags a card
        getTaskChangeData(event, columnIndex, rowIndex) {
            let eventName = Object.keys(event)[0];
            let taskCardData = this.kanban.rows[rowIndex].columns[columnIndex].task_cards;
            let columnId = this.kanban.rows[rowIndex].columns[columnIndex].id;
            let rowId = this.kanban.rows[rowIndex].id
            this.isDraggableDisabled = true;


            switch (eventName) {
                case "moved":
                    this.asyncUpdateTaskCardIndexes(taskCardData).then(() => {
                        this.triggerSuccessToast('task moved')
                        this.isDraggableDisabled = false
                    });
                    break;
                case "added":
                    this.asyncUpdateTaskCardRowAndColumnId(columnId, rowId, event.added.element.id).then(() => {
                            this.asyncUpdateTaskCardIndexes(taskCardData).then(() => {
                                this.asyncGetTaskCardsByColumn(columnId).then((data) => {
                                    this.kanban.rows[rowIndex].columns[columnIndex].task_cards = data.data;
                                    this.triggerSuccessToast('task moved');
                                    this.isDraggableDisabled = false
                                }).catch(res => {
                                    console.log(res)
                                });
                            });
                        }
                    );
                    break;
                case "removed":
                    this.asyncUpdateTaskCardIndexes(taskCardData);
                    break;
                default:
                    alert('event "' + eventName + '" not handled: ');
            }
        },

        // Whenever a user drags a column
        getColumnChangeData(event, rowIndex) {

            if (event.oldIndex !== event.newIndex) {
                let columns = this.kanban.rows[rowIndex].columns;
                this.isDraggableDisabled = true;
                this.asyncUpdateColumnIndexes(columns).then(() => {

                    this.isDraggableDisabled = false
                    this.getKanban(this.kanban.id);
                    this.triggerSuccessToast('Column position updated')
                });
            }
        },

        // Whenever a user drags a row
        getRowChangeData(event) {
            if (event.oldIndex !== event.newIndex) {
                this.isDraggableDisabled = true
                this.asyncUpdateRowIndexes(this.kanban.rows).then(() => {
                    this.isDraggableDisabled = false
                    this.getKanban(this.kanban.id);
                    this.triggerSuccessToast('Row position updated')
                });
            }
        },

        saveMember(selectedMembers) {
            this.loadingMembers = {memberId: null, isLoading: true}
            const cloneSelectedMembers = {...selectedMembers};
            this.asyncAddMembers(cloneSelectedMembers, this.kanban.id
            ).then(() => {
                this.asyncGetMembers(this.kanban.id).then((data) => {
                    this.kanban.members = data.data;
                    this.loadingMembers = {memberId: null, isLoading: false}
                    this.triggerSuccessToast('New kanban members saved')

                }).catch(res => {
                    console.log(res)
                });
            }).catch(res => {
                console.log(res)
            });
        },

        deleteMember(member) {
            this.asyncDeleteMember(member.id).then(() => {
                this.loadingMembers = {memberId: member.id, isLoading: true}
                this.asyncGetMembers(this.kanban.id).then((data) => {
                    this.kanban.members = data.data;
                    this.loadingMembers = {memberId: null, isLoading: false};
                    this.triggerSuccessToast('Kanban member deleted')
                }).catch(res => {
                    console.log(res)
                });
            }).catch(res => {
                console.log(res)
            });

            this.getKanban(this.kanban.id);
        },

        updateTaskCard(cardData) {
            const cloneCardData = {...cardData};
            this.loadingCards = {columnId: cloneCardData.column_id, isLoading: true}
            this.asyncUpdateTask(cloneCardData).then(() => {
                this.asyncGetTaskCardsByColumn(cloneCardData.column_id).then((data) => {
                    this.kanban.rows[cloneCardData.row.index].columns[cloneCardData.column.index].task_cards = data.data;
                    this.loadingCards = {columnId: null, isLoading: false}
                    this.triggerSuccessToast('Task Updated')
                }).catch(res => {
                    console.log(res)
                });
            }).catch(res => {
                console.log(res)
            });
        },

        saveTask(taskData) {

            this.isDraggableDisabled = true;
            this.asyncCreateTask(taskData).then((data) => {
                this.asyncGetTaskCardsByColumn(taskData.selectedColumnId).then((data) => {
                    this.kanban.rows[taskData.selectedRowIndex].columns[taskData.selectedColumnIndex].task_cards = data.data;
                    this.isDraggableDisabled = false
                }).catch(res => {
                    console.log(res)
                });
            });
        },

        saveRowAndColumns(rowData) {
            const cloneRowData = {...rowData};
            let rowIndex = cloneRowData.rowIndex;

            if (cloneRowData.rowId !== null)
                this.loadingRow = {rowId: this.kanban.rows[rowIndex].id, isLoading: true}

            this.asyncCreateRowAndColumns(cloneRowData).then((data) => {

                if (cloneRowData.rowId !== null) {
                    this.kanban.rows[rowIndex] = data.data[0];

                } else {
                    this.kanban.rows.push(data.data[0]);
                }
                this.loadingRow = {rowId: null, isLoading: false}

            }).catch(res => {
                console.log(res)
            });
        },

        deleteRow(rowData) {
            this.asyncDeleteRow(rowData.rowId).then(() => {
                this.triggerSuccessToast('Row Deleted')
                this.getKanban(this.kanban.id);

            }).catch(res => {
                console.log(res)
            });
        },

        fetchAndSetColumnTasks(task){
            this.asyncGetTaskCardsByColumn(task.column.id).then((data) => {
                this.kanban.rows[task.row.index].columns[task.column.index].task_cards = data.data;
                this.isDraggableDisabled = false
            }).catch(res => {
                console.log(res)
            });
        },

        getKanban(kanbanID) {
            this.eventHub.$emit("set-loading-state", true);

            this.asyncGetkanbanData(kanbanID).then((data) => {
                this.kanban = data.data;
                this.eventHub.$emit("set-loading-state", false);

            }).catch(res => {
                console.log(res)
            });
        },

        openTask(task) {
            this.eventHub.$emit("update-kanban-task-cards", task);
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
</style>
