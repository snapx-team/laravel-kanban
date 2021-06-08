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
                    <div class="space-x-2  flex flex-1 pt-3 pb-2 overflow-x-auto overflow-y-hidden">

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
                                               v-on:click.native="updateTask(task_card.id)"></task-card>
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

        <p>{{kanban}}</p>

        <add-task-card-modal :kanbanData="kanban"></add-task-card-modal>
        <add-member-modal :kanbanData="kanban"></add-member-modal>
        <add-row-and-columns-modal :kanbanData="kanban"></add-row-and-columns-modal>
    </div>
</template>

<script>
    import draggable from "vuedraggable";
    import TaskCard from "./kanbanComponents/TaskCard.vue";
    import AddTaskCardModal from "./kanbanComponents/AddTaskCardModal.vue";
    import AddMemberModal from "./kanbanComponents/AddMemberModal.vue";
    import KanbanBar from "./kanbanComponents/KanbanBar.vue";
    import {ajaxCalls} from "../../mixins/ajaxCallsMixin";
    import AddRowAndColumnsModal from "./kanbanComponents/AddRowAndColumnsModal";

    export default {
        inject: ["eventHub"],
        components: {
            AddRowAndColumnsModal,
            TaskCard,
            draggable,
            AddTaskCardModal,
            AddMemberModal,
            KanbanBar,
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
            this.eventHub.$on("save-task-cards", (cardData) => {
                this.saveTaskCards(cardData);
            });
            this.eventHub.$on("delete-kanban-task-cards", (cardData) => {
                this.deleteTaskCard(cardData);
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
        },

        beforeDestroy() {
            this.eventHub.$off('save-task-cards');
            this.eventHub.$off('delete-kanban-task-cards');
            this.eventHub.$off('save-members');
            this.eventHub.$off('remove-member');
            this.eventHub.$off('save-row-and-columns');
        },

        methods: {
            createTaskCard(rowIndex, columnIndex) {
                var rowName = this.kanban.rows[rowIndex].name;
                var columnName = this.kanban.rows[rowIndex].columns[columnIndex].name;
                var columnId = this.kanban.rows[rowIndex].columns[columnIndex].id;

                this.eventHub.$emit("create-kanban-task-cards", {
                    rowIndex,
                    rowName,
                    columnIndex,
                    columnName,
                    columnId,
                });
            },
            createRowAndColumns(rowIndex, rowColumns, rowId, rowName) {
                this.eventHub.$emit("create-row-and-columns", {
                    rowIndex,
                    rowColumns,
                    rowId,
                    rowName,
                });
            },

            // Whenever a user drags a card
            getTaskChangeData(event, columnIndex, rowIndex) {
                var eventName = Object.keys(event)[0];
                let taskCardData = this.kanban.rows[rowIndex].columns[columnIndex].task_cards
                let columnId = this.kanban.rows[rowIndex].columns[columnIndex].id
                this.isDraggableDisabled = true

                console.log(eventName);


                switch (eventName) {
                    case "moved":
                        this.asyncUpdateTaskCardIndexes(taskCardData).then(() => {this.isDraggableDisabled = false});
                        break;
                    case "added":
                        this.asyncUpdateTaskCardColumnId(columnId, event.added.element.id).then(() => {
                                this.asyncUpdateTaskCardIndexes(taskCardData).then(() => {this.isDraggableDisabled = false});
                            }
                        );
                        break;
                    case "removed":
                        this.asyncUpdateTaskCardIndexes(taskCardData).then(() => {this.isDraggableDisabled = false});
                        break;
                    default:
                        alert('event "' + eventName + '" not handled: ');
                }
            },

            // Whenever a user drags a column
            getColumnChangeData(event, rowIndex) {

                if(event.oldIndex !== event.newIndex){
                    console.log('column');
                    let columns = this.kanban.rows[rowIndex].columns;
                    this.isDraggableDisabled = true;
                    this.asyncUpdateColumnIndexes(columns).then(() => {this.isDraggableDisabled = false});
                }

            },

            // Whenever a user drags a row
            getRowChangeData(event) {
                console.log('row');

                if(event.oldIndex !== event.newIndex) {
                    this.isDraggableDisabled = true
                    this.asyncUpdateRowIndexes(this.kanban.rows).then(() => {this.isDraggableDisabled = false});
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
                    }).catch(res => {console.log(res)});
                }).catch(res => {console.log(res)});
            },

            deleteMember(member) {
                this.asyncDeleteMember(member.id).then(() => {
                    this.loadingMembers = {memberId: member.id, isLoading: true}
                    this.asyncGetMembers(this.kanban.id).then((data) => {
                        this.kanban.members = data.data;
                        this.loadingMembers = {memberId: null, isLoading: false};
                    }).catch(res => {console.log(res)});
                }).catch(res => {console.log(res)});

                this.getKanban(this.kanban.id);
            },

            saveTaskCards(cardData) {
                const cloneCardData = {...cardData};
                this.loadingCards = {columnId: cloneCardData.columnId, isLoading: true}
                this.asyncCreateKanbanTaskCards(cloneCardData).then(() => {
                    this.asyncGetTaskCardsByColumn(cloneCardData.columnId).then((data) => {
                        this.kanban.rows[cloneCardData.selectedRowIndex].columns[cloneCardData.selectedColumnIndex].task_cards = data.data;
                        this.loadingCards = {columnId: null, isLoading: false}
                    }).catch(res => {console.log(res)});
                }).catch(res => {console.log(res)});
            },

            deleteTaskCard(cardData) {
                const cloneCardData = {...cardData};
                this.loadingCards = {columnId: cloneCardData.selectedCardData.column_id, isLoading: true}
                this.asyncDeleteKanbanTaskCard(cloneCardData.selectedCardData.id).then(() => {
                    this.asyncGetTaskCardsByColumn(cloneCardData.selectedCardData.column_id).then((data) => {
                        this.kanban.rows[cloneCardData.selectedRowIndex].columns[cloneCardData.selectedColumnIndex].task_cards = data.data;
                        this.loadingCards = {columnId: null, isLoading: false}
                    }).catch(res => {console.log(res)});
                }).catch(res => {console.log(res)});
            },

            saveRowAndColumns(rowData) {
                const cloneRowData = {...rowData};
                var rowIndex = cloneRowData.rowIndex;

                if (cloneRowData.rowId !== null)
                    this.loadingRow = {rowId: this.kanban.rows[rowIndex].id, isLoading: true}

                this.asyncCreateRowAndColumns(cloneRowData).then((data) => {

                    if (cloneRowData.rowId !== null) {
                        this.kanban.rows[rowIndex] = data.data[0];

                    }
                    else {
                        console.log(data);
                        this.kanban.rows.push(data.data[0]);
                    }

                    this.loadingRow = {rowId: null, isLoading: false}

                }).catch(res => {console.log(res)});
            },

            getKanban(kanbanID) {
                this.eventHub.$emit("set-loading-state", true);

                this.asyncGetkanbanData(kanbanID).then((data) => {
                    this.kanban = data.data;
                    this.eventHub.$emit("set-loading-state", false);

                    console.log(data);
                }).catch(res => {console.log(res)});
            },
        },
    };
</script>

<style scoped>
    .column-width {
        min-width: 230px;
    }

    .ghost-card {
        opacity: 0.5;
        background: #F7FAFC;
        border: 1px solid #4299e1;
    }
</style>
