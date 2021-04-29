<template>
    <div v-if="kanban !== null">
        <kanban-bar :kanbanId="kanban.id"
                    :kanbanMembers="kanban.members"
                    :kanbanName="kanban.name"
                    :loadingMembers="loadingMembers"></kanban-bar>

        <div :key="row.id" class="mx-10 my-3" v-for="(row, rowIndex) in kanban.rows">

            <div class="border bg-gray-700 pl-3 pr-3 rounded py-2 flex justify-between"
                 v-if="loadingColumn.rowId === row.id && loadingColumn.isLoading ">
                <h2 class="text-gray-100 font-medium tracking-wide animate-pulse">
                    Loading... </h2>
            </div>
            <div class="border bg-gray-700 pl-3 pr-3 rounded py-2 flex justify-between" v-else>
                <h2 class="text-gray-100 font-medium tracking-wide">
                    {{ row.name }} </h2>

                <a @click="createColumns(rowIndex, row.columns, row.id)"
                   class="px-2 text-gray-500 hover:text-gray-400 transition duration-300 ease-in-out focus:outline-none"
                   href="#">
                    <i class="fas fa-business-time"></i>
                </a>
            </div>
            <div class="flex flex-wrap">
                <div class="space-x-2  flex flex-1 pt-3 pb-2 overflow-x-auto overflow-y-hidden">
                    <div :key="column.id"
                         class="flex-1 bg-gray-200 px-3 py-3 column-width rounded"
                         v-for="(column, columnIndex) in row.columns">
                        <div class="flex" v-if="loadingCards.columnId === column.id && loadingCards.isLoading ">
                            <p class="flex-auto text-gray-700 font-semibold font-sans tracking-wide pt-1 animate-pulse">
                                Loading... </p>
                        </div>
                        <div class="flex" v-else>

                            <p class="flex-auto text-gray-700 font-semibold font-sans tracking-wide pt-1">
                                {{ column.name }} </p>

                            <button @click="createEmployeeCard(rowIndex, columnIndex)"
                                    class="w-6 h-6 bg-blue-200 rounded-full hover:bg-blue-300 mouse transition ease-in duration-200 focus:outline-none">
                                <i class="fas fa-plus text-white"></i>
                            </button>
                        </div>
                        <draggable :animation="200"
                                   :list="column.employee_cards"
                                   :disabled="isDraggableDisabled"
                                   @change="getChangeData($event, columnIndex, rowIndex)"
                                   class="h-full list-group"
                                   ghost-class="ghost-card"
                                   group="employees">
                            <employee-card :employee_card="employee_card"
                                           :key="employee_card.id"
                                           class="mt-3 cursor-move"
                                           :class="{'opacity-60':isDraggableDisabled}"
                                           v-for="employee_card in column.employee_cards"
                                           v-on:click.native="updateTask(employee_card.id)"></employee-card>
                        </draggable>
                    </div>
                </div>
            </div>
        </div>
        <hr class="mt-5"/>

        <add-employee-card-modal :kanbanData="kanban"></add-employee-card-modal>
        <add-member-modal :kanbanData="kanban"></add-member-modal>
        <add-column-modal :kanbanData="kanban"></add-column-modal>
    </div>
</template>

<script>
    import draggable from "vuedraggable";
    import EmployeeCard from "./kanbanComponents/EmployeeCard.vue";
    import AddEmployeeCardModal from "./kanbanComponents/AddEmployeeCardModal.vue";
    import AddMemberModal from "./kanbanComponents/AddMemberModal.vue";
    import AddColumnModal from "./kanbanComponents/AddColumnModal.vue";
    import KanbanBar from "./kanbanComponents/KanbanBar.vue";
    import {ajaxCalls} from "../../mixins/ajaxCallsMixin";

    export default {
        inject: ["eventHub"],
        components: {
            EmployeeCard,
            draggable,
            AddEmployeeCardModal,
            AddMemberModal,
            AddColumnModal,
            KanbanBar,
        },

        mixins: [ajaxCalls],

        props: {'id': Number},

        data() {
            return {
                kanban: null,
                loadingColumn: {rowId: null, isLoading: false},
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
            this.eventHub.$on("save-employee-cards", (cardData) => {
                this.saveEmployeeCards(cardData);
            });
            this.eventHub.$on("delete-employee-cards", (cardData) => {
                this.deleteEmployeeCard(cardData);
            });
            this.eventHub.$on("save-members", (selectedMembers) => {
                this.saveMember(selectedMembers);
            });
            this.eventHub.$on("remove-member", (memberData) => {
                this.deleteMember(memberData);
            });
            this.eventHub.$on("save-columns", (columnData) => {
                this.saveColumns(columnData);
            });
        },

        beforeDestroy(){
            this.eventHub.$off('save-employee-cards');
            this.eventHub.$off('delete-employee-cards');
            this.eventHub.$off('save-members');
            this.eventHub.$off('remove-member');
            this.eventHub.$off('save-columns');
        },

        methods: {
            createEmployeeCard(rowIndex, columnIndex) {
                var rowName = this.kanban.rows[rowIndex].name;
                var columnName = this.kanban.rows[rowIndex].columns[columnIndex].name;
                var columnId = this.kanban.rows[rowIndex].columns[columnIndex].id;

                this.eventHub.$emit("create-employee-cards", {
                    rowIndex,
                    rowName,
                    columnIndex,
                    columnName,
                    columnId,
                });
            },
            createColumns(rowIndex, rowColumns, rowId) {
                this.eventHub.$emit("create-columns", {
                    rowIndex,
                    rowColumns,
                    rowId,
                });
            },

            // Whenever a user drags a card
            getChangeData(event, columnIndex, rowIndex) {
                var eventName = Object.keys(event)[0];
                let employeeCardData = this.kanban.rows[rowIndex].columns[columnIndex].employee_cards
                let columnId = this.kanban.rows[rowIndex].columns[columnIndex].id
                this.isDraggableDisabled = true

                switch (eventName) {
                    case "moved":
                        this.asyncUpdateEmployeeCardIndexes(employeeCardData).then(() => {this.isDraggableDisabled = false});
                        break;
                    case "added":
                        this.asyncUpdateEmployeeCardColumnId(columnId, event.added.element.id).then(() => {
                                this.asyncUpdateEmployeeCardIndexes(employeeCardData).then(() => {this.isDraggableDisabled = false});
                            }
                        );
                        break;
                    case "removed":
                        this.asyncUpdateEmployeeCardIndexes(employeeCardData).then(() => {this.isDraggableDisabled = false});
                        break;
                    default:
                        alert('event "' + eventName + '" not handled: ');
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

            saveEmployeeCards(cardData) {
                const cloneCardData = {...cardData};
                this.loadingCards = {columnId: cloneCardData.columnId, isLoading: true}
                this.asyncCreateEmployeeCards(cloneCardData).then(() => {
                    this.asyncGetEmployeeCardsByColumn(cloneCardData.columnId).then((data) => {
                        this.kanban.rows[cloneCardData.selectedRowIndex].columns[cloneCardData.selectedColumnIndex].employee_cards = data.data;
                        this.loadingCards = {columnId: null, isLoading: false}
                    }).catch(res => {console.log(res)});
                }).catch(res => {console.log(res)});
            },

            deleteEmployeeCard(cardData) {
                const cloneCardData = {...cardData};
                this.loadingCards = {columnId: cloneCardData.selectedCardData.column_id, isLoading: true}
                this.asyncDeleteEmployeeCard(cloneCardData.selectedCardData.id).then(() => {
                    this.asyncGetEmployeeCardsByColumn(cloneCardData.selectedCardData.column_id).then((data) => {
                        this.kanban.rows[cloneCardData.selectedRowIndex].columns[cloneCardData.selectedColumnIndex].employee_cards = data.data;
                        this.loadingCards = {columnId: null, isLoading: false}
                    }).catch(res => {console.log(res)});
                }).catch(res => {console.log(res)});
            },

            saveColumns(columnData) {
                const cloneColumnData = {...columnData};
                var rowIndex = cloneColumnData.rowIndex;
                this.loadingColumn = {rowId: this.kanban.rows[rowIndex].id, isLoading: true}

                this.asyncCreateColumns(cloneColumnData).then((data) => {
                    this.kanban.rows[rowIndex].columns = data.data;
                    this.loadingColumn = {rowId: null, isLoading: false}

                }).catch(res => {console.log(res)});
            },

            getKanban(kanbanID) {
                this.eventHub.$emit("set-loading-state", true);

                this.asyncGetPhoneLineData(kanbanID).then((data) => {
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
