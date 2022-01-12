<template>

    <div>
        <div v-if="!loadingRelatedTasks" class="space-y-4 whitespace-nowrap">
            <p v-if="relatedTasks.length === 0 && !selectGroupIsVisible"
               class="text-gray-700 font-semibold font-sans tracking-wide">
                No Related Tasks
            </p>
            <button @click="selectGroupIsVisible = true"
                    class="py-2 text-sm text-indigo-600 hover:text-indigo-800 transition duration-300 ease-in-out focus:outline-none"
                    v-if="!selectGroupIsVisible && $role === 'admin'">
                <i class="fas fa-th-list mr-2"></i>
                Click To Add or Change Group
            </button>

            <div v-if="selectGroupIsVisible">
                <span
                    class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Group with task</span>
                <div class="flex flex-row">
                    <div class="flex-auto w-44">
                        <vSelect @search="onTypeTask"
                                 :appendToBody=appendSelectToBody
                                 :options="tasks"
                                 class="text-gray-400"
                                 label="name"
                                 placeholder="Select task"
                                 style="margin-top: 7px"
                                 :filter="filterTasks"
                                 v-model="associatedTask">
                            <template slot="selected-option" slot-scope="option">
                                <p>
                                    <span class="font-bold">{{ option.task_simple_name }}: </span>
                                    <span class="italic">{{ option.name }}</span>
                                </p>
                            </template>
                            <template slot="option" slot-scope="option">
                                <p>
                                    <span class="font-bold">{{ option.task_simple_name }}: </span>
                                    <span class="italic">{{ option.name }}</span>
                                </p>
                            </template>
                            <template #no-options="{ search, searching, loading }">
                                No result .
                            </template>
                        </vSelect>
                    </div>
                    <div class="flex-auto mt-2 pl-2">
                        <button @click="updateGroup()"
                                class="px-4 py-3 border border-transparent rounded text-white bg-indigo-600 hover:bg-indigo-500 transition duration-300 ease-in-out"
                                type="button">
                            <span>Save</span>
                        </button>
                    </div>
                </div>
            </div>

            <template v-for="task_card in relatedTasks">
                <badge :name="task_card.board.name"></badge>
                <task-card
                    :key="task_card.id"
                    :task_card="task_card"
                    class="cursor-pointer"
                    v-on:click.native="updateTask(task_card)"></task-card>
            </template>
            <button @click="removeGroup()"
                    v-if="relatedTasks.length > 0 && $role === 'admin'"
                    class="px-2 py-2 border border-transparent rounded text-white bg-indigo-600 hover:bg-indigo-500 transition duration-300 ease-in-out"
                    type="button">
                <span>Remove Group</span>
            </button>
        </div>
        <div v-else
             class="animate-pulse text-gray-700 font-semibold font-sans tracking-wide"
             :class="{'animate-pulse': loadingRelatedTasks}">
            <p>Loading Related Tasks</p>
        </div>
    </div>

</template>


<script>
import Avatar from "../Avatar.vue";
import Badge from "../Badge";
import TaskCard from "../../kanban/kanbanComponents/TaskCard";
import {ajaxCalls} from "../../../mixins/ajaxCallsMixin";
import vSelect from "vue-select";
import {componentNames} from "../../../enums/componentNames";

export default {
    inject: ["eventHub"],
    components: {
        Avatar,
        Badge,
        TaskCard,
        vSelect,
    },
    mixins: [ajaxCalls],
    props: {
        cardData: Object,
        appendSelectToBody: {
            type: Boolean,
            default: false
        },
        sourceFrom: String,
    },
    data() {
        return {
            relatedTasks: [],
            loadingRelatedTasks: false,
            selectGroupIsVisible: false,
            associatedTask: null,
            tasks: []
        };
    },

    mounted() {
        this.getRelatedTasks();
        this.getTasks();
    },

    computed: {
        filtered() {
            const regex = new RegExp(this.filter, "i");
            return this.comments.filter((e) => {
                this.paginationIndex = 0;
                return !this.filter || e.employee.user.full_name.match(regex);
            });
        },
    },
    methods: {

        getRelatedTasks() {
            this.loadingRelatedTasks = true;
            this.asyncGetRelatedTasks(this.cardData.id).then((data) => {
                this.relatedTasks = data.data;
                this.loadingRelatedTasks = false;
            })
        },

        updateTask(task) {
            this.eventHub.$emit("update-kanban-task-cards", task);
        },

        onTypeTask(search, loading) {
            if (search.length) {
                loading(true);
                this.typeTask(search, loading, this);
            }
        },
        typeTask: _.debounce(function (search, loading) {
            this.asyncGetSomeTasks(search).then((data) => {
                this.tasks = data.data;
            }).then(function () {
                setTimeout(500);
                loading(false);
            });
        }, 500),

        getTasks() {
            this.asyncGetAllTasks().then((data) => {
                this.tasks = data.data;
            })
        },

        filterTasks(options, search) {
            return this.tasks;
        },

        removeGroup() {
            this.$swal({
                icon: 'info',
                title: 'Are you sure?',
                text: 'This task will no longer be related to any other task. The information on the card will remain the same.',
                showCancelButton: true,
                confirmButtonText: `Continue`,
            }).then((result) => {
                if (result.isConfirmed) {
                    this.asyncRemoveGroup(this.cardData.id).then(() => {
                        this.updateParentWithChange();
                    })
                }
            })
        },

        updateGroup() {
            this.$swal({
                icon: 'warning',
                title: 'Warning',
                text: 'Updating group will remove the task description and related data and replace it with the new group information',
                showCancelButton: true,
                confirmButtonText: `Continue`,
            }).then((result) => {
                if (result.isConfirmed) {
                    this.asyncUpdateGroup(this.cardData.id, this.associatedTask.shared_task_data_id).then(() => {
                        this.updateParentWithChange();
                    });
                }
            })
        },

        updateParentWithChange(){
            this.eventHub.$emit("fetch-and-replace-task-data");
            if (this.sourceFrom === componentNames.ViewTaskData) {
                this.eventHub.$emit("fetch-and-set-column-tasks", this.cardData);
            } else if (this.sourceFrom === componentNames.TaskPane) {
                this.eventHub.$emit("reload-backlog-data");
            }
            this.getRelatedTasks(this.cardData.id);
            this.associatedTask = null;
            this.selectGroupIsVisible = false;
        }
    },
};
</script>

