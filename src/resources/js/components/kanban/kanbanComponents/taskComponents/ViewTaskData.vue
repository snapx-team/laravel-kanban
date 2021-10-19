<template>
    <div>

        <div class="inline-flex space-x-1">
            <badge :name="cardData.badge.name" v-if="cardData.badge !== []"></badge>

            <div
                class="px-3 py-1 rounded text-xs font-semibold flex items-center"
                :class="`bg-${statusColor}-200 text-${statusColor}-800`">
                <span class="w-2 h-2 rounded-full mr-1"
                      :class="`bg-${statusColor}-800`"
                ></span>
                <span> status: {{ cardData.status }}</span>
            </div>

            <div :style="`background-color: hsl( ${priorityHue}, 45%, 90%); color: hsl( ${priorityHue}, 50%, 45%)`"
                 class="px-3 py-1 rounded text-xs font-semibold flex items-center">
                <span class="w-2 h-2 rounded-full mr-1"
                      :style="`background-color: hsl( ${priorityHue}, 45%, 40%)`"></span>
                <span>{{ timeRemaining }}</span>
            </div>
        </div>

        <div class="flex justify-end">


            <div class="py-2 flex-1">
                <p class="text-gray-700 font-semibold font-sans tracking-wide text-2xl py-1"> {{ cardData.name }} </p>
                <p class="text-gray-500 text-sm py-1" v-if="cardData.row_id !== null"> Row:
                    <i class="font-semibold">'{{ cardData.row.name }}'</i> Column:
                    <i class="font-semibold">'{{ cardData.column.name }}'</i>
                </p>
                <p class="text-gray-500 text-sm py-1" v-else> In Backlog</p>
            </div>


            <div v-if="$role === 'admin'"
                 class="pb-4 w-60">
                <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">
                    Status
                </span>
                <vSelect
                    v-model="selectedStatus"
                    :options="this.statuses"
                    label="name"
                    style="margin-top: 7px"
                    class="text-gray-700"
                    @input="setStatus()">>
                    <template slot="option" slot-scope="option">
                        <i class="fas fa-circle mr-2" :class="`text-${option.color}-400 `"></i>
                        {{ option.name }}
                    </template>
                </vSelect>
            </div>
        </div>

        <div class="flex pt-3 border-t space-x-20">

            <div class="flex-grow">

                <div class="space-y-5">
                    <p class="text-gray-700 font-semibold font-sans tracking-wide text-lg"> Description: </p>
                    <div class="rounded-lg p-5 bg-gray-50">
                        <div class="ql-editor h-auto" id="task-description" v-html="formatted"></div>
                    </div>
                </div>


                <div class="space-y-5 pt-5">
                    <p class="text-gray-700 font-semibold font-sans tracking-wide text-lg"> Comments: </p>
                    <task-comments :cardData="cardData"></task-comments>
                </div>
            </div>

            <div class="space-y-5 whitespace-nowrap">

                <p class="text-gray-700 font-semibold font-sans tracking-wide text-lg"> Info: </p>
                <div class="relative mb-5 border-b" v-if="checklistData.total > 0">
                    <div class="flex mb-3 items-center justify-between">
                        <div>
                            <span
                                class="text-xs font-semibold inline-block py-1 px-2 rounded-full text-indigo-600 bg-indigo-200">
                                checklist items: {{ checklistData.done }}/{{ checklistData.total }}
                            </span>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-semibold inline-block text-indigo-600">{{
                                    checklistPercentage
                                }}%</span>
                        </div>
                    </div>
                    <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-indigo-200 ">
                        <div :style="`width:${checklistPercentage}%`"
                             class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-500"></div>
                    </div>
                </div>

                <div class="flex items-center">
                    <div class="w-32 flex text-gray-700">
                        <i class="fas fa-bullhorn mr-2"></i>
                        <p class="font-semibold font-sans tracking-wide text-sm"> Reporter: </p>
                    </div>
                    <avatar :name="cardData.reporter.user.full_name" :size="6" :tooltip="true"
                            class="border border-white max-w-md"></avatar>
                    <span class="text-xs text-gray-600 px-1"> {{ cardData.reporter.user.full_name }}</span>
                </div>

                <div class="flex">
                    <div class="w-32 flex text-gray-700">
                        <i class="fas fa-people-arrows mr-2"></i>
                        <p class="font-semibold font-sans tracking-wide text-sm"> Assigned To: </p>
                    </div>
                    <div class="space-y-2">
                        <span v-if=" cardData.assigned_to.length === 0"
                              class="text-xs text-red-800 px-2 py-1 bg-red-200 rounded"> unnasigned</span>
                        <template v-for="(employee, employeeIndex) in cardData.assigned_to">
                            <div class="flex items-center">
                                <avatar class="border border-white max-w-md"
                                        :key="employeeIndex"
                                        :name="employee.employee.user.full_name"
                                        :size="6"
                                        :tooltip="false"></avatar>
                                <span class="text-xs text-gray-600 px-1"> {{ employee.employee.user.full_name }}</span>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="flex my-4 border-t border-b items-center">

                    <div class="py-4 space-y-4">

                        <div v-if="cardData.shared_task_data.erp_employees.length" class="text-sm flex items-start">
                            <div class="w-32 flex items-center text-gray-700">
                                <i class="fas fa-user mr-2"></i>
                                <p class="font-semibold font-sans tracking-wide text-sm">
                                    <span v-if="cardData.shared_task_data.erp_employees.length > 1">Employees:</span>
                                    <span v-else>Employee:</span>
                                </p>
                            </div>
                            <div>
                                <div class="flex" v-for="(employee, index) in cardData.shared_task_data.erp_employees">
                                    <avatar :name="employee.full_name" :size="6" :tooltip="true"
                                            class="border border-white max-w-md"></avatar>
                                    <a :href="`/app/users/${employee.id}/edit`" target="_blank" class="text-xs px-1 text-indigo-500 underline"> {{ employee.full_name }}</a>
                                </div>
                            </div>
                        </div>

                        <div v-if="cardData.shared_task_data.erp_contracts.length" class="text-sm text-gray-700 flex items-start">
                            <div class="w-32 flex items-center text-gray-700">
                                <i class="fas fa-building mr-2"></i>
                                <p class="font-semibold font-sans tracking-wide text-sm">
                                    <span v-if="cardData.shared_task_data.erp_contracts.length > 1">Contracts:</span>
                                    <span v-else>Contracts:</span>
                                </p>
                            </div>
                            <div>
                                <div class="flex" v-for="(contract, index) in cardData.shared_task_data.erp_contracts">
                                    <avatar :name="contract.contract_identifier" :size="6" :tooltip="true"
                                            class="border border-white max-w-md"></avatar>
                                    <a :href="`/app/contracts/${contract.id}/edit`" target="_blank" class="text-xs  px-1 text-indigo-500 underline">{{ contract.contract_identifier }}</a>
                                </div>
                            </div>
                        </div>

                        <div v-if="cardData.deadline !== null" class="text-sm text-gray-700 flex">
                            <div class="w-32 flex items-center">
                                <i class="fas fa-stopwatch mr-2"></i>
                                <p class="font-semibold font-sans tracking-wide text-sm">Deadline: </p>
                            </div>
                            <a class="max-w-md text-xs">{{ cardData.deadline | moment("DD MMM, YYYY") }}</a>
                        </div>
                    </div>
                </div>

                <div class="text-gray-700 font-semibold font-sans tracking-wide text-lg"
                     :class="{'animate-pulse': loadingRelatedTasks}">
                    <p v-if="loadingRelatedTasks">Loading Related Tasks</p>
                    <p v-else>Related Tasks:</p>

                </div>

                <div v-if="!loadingRelatedTasks" class="space-y-4 whitespace-nowrap">
                    <p v-if="relatedTasks.length === 0 && !selectGroupIsVisible" class="tracking-wide text-sm">No Related Tasks</p>
                    <button @click="selectGroupIsVisible = true"
                            class="py-2 text-sm text-indigo-600 hover:text-indigo-800 transition duration-300 ease-in-out focus:outline-none"
                            v-if="!selectGroupIsVisible && $role === 'admin'">
                        <i class="fas fa-th-list mr-2"></i>
                        Click To Add or Change Group
                    </button>

                    <div v-if="selectGroupIsVisible">
                        <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">Group with task</span>
                        <div class="flex flex-row">
                            <div class="flex-auto w-44">
                                <vSelect :options="tasks"
                                         class="text-gray-400"
                                         label="name"
                                         placeholder="Select task"
                                         style="margin-top: 7px"
                                         v-model="associatedTask">
                                    <template slot="selected-option" slot-scope="option">
                                        <p>
                                        <span class="font-bold">{{ option.task_simple_name}}: </span>
                                            <span class="italic">{{ option.name }}</span>
                                        </p>
                                    </template>
                                    <template slot="option" slot-scope="option">
                                        <p>
                                        <span class="font-bold">{{ option.task_simple_name}}: </span>
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
            </div>
        </div>
    </div>
</template>
<script>
import Avatar from "../../../global/Avatar.vue";
import Badge from "../../../global/Badge.vue";
import {helperFunctions} from "../../../../mixins/helperFunctionsMixin";
import {ajaxCalls} from "../../../../mixins/ajaxCallsMixin";
import TaskComments from "./TaskComments";
import TaskCard from "../TaskCard";
import vSelect from "vue-select";


export default {
    inject: ["eventHub"],

    mixins: [helperFunctions, ajaxCalls],

    components: {
        TaskCard,
        TaskComments,
        Badge,
        Avatar,
        vSelect,
    },

    props: {
        cardData: Object,
    },
    data() {
        return {
            loadingRelatedTasks: false,
            loadingSharedData: false,
            relatedTasks: [],
            tasks: [],
            associatedTask: null,
            formatted: null,
            checklistData: {
                total: 0,
                done: 0,
            },
            selectGroupIsVisible: false,
            selectedGroup: {},
            cloneCardData: null,
            selectedStatus: null,
            statuses: [
                {
                    name: 'active',
                    color: 'green',
                },
                {
                    name: 'completed',
                    color: 'blue',
                },
                {
                    name: 'cancelled',
                    color: 'red',
                }
            ],
            clickedCheckboxEl: null
        };
    },

    created() {
        this.cloneCardData = {...this.cardData};
        this.handleChecklist();
        this.getRelatedTasks();
        this.getTasks();
        this.selectedStatus = this.cardData.status;
    },

    methods: {
        updateTask(task) {
            this.eventHub.$emit("update-kanban-task-cards", task);
        },
        getRelatedTasks() {
            this.loadingRelatedTasks = true;
            this.asyncGetRelatedTasks(this.cardData.id).then((data) => {
                this.relatedTasks = data.data;
                this.loadingRelatedTasks = false;
            }).catch(res => {
                console.log(res)
            });
        },

        fetchAndReplaceTaskData() {
            this.loadingSharedData = true;
            this.asyncGetTaskData(this.cardData.id).then((data) => {
                this.cloneCardData = data.data;
                this.handleChecklist();
                this.getRelatedTasks();
                this.eventHub.$emit("update-add-task-data-with-group-data", data.data);
                this.loadingSharedData = false;
            }).catch(res => {
                console.log(res)
            });
        },

        getTasks() {
            this.asyncGetAllTasks().then((data) => {
                this.tasks = data.data;
            }).catch(res => {
                console.log(res)
            });
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
                        this.fetchAndReplaceTaskData();
                    });
                }
            })

        },
        removeGroup() {
            this.asyncRemoveGroup(this.cardData.id).then(() => {
                this.fetchAndReplaceTaskData();
            }).catch(res => {
                console.log(res)
            });
        },
        setStatus() {

            if(this.selectedStatus.name !==  this.cardData.status){
                this.$swal({
                    icon: 'info',
                    title: 'Set status to ' + this.selectedStatus.name,
                    text: 'This will archive the task. You can find it in the backlog.',
                    showCancelButton: true,
                    confirmButtonText: `Continue`,
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.asyncSetStatus(this.cloneCardData.id, this.selectedStatus.name).then(() => {
                            this.eventHub.$emit("fetch-and-set-column-tasks", this.cloneCardData);
                            this.eventHub.$emit("close-task-modal");
                            this.triggerSuccessToast('Status Updated')
                        }).catch(res => {
                            console.log(res)
                        });
                    } else {
                        this.selectedStatus = this.cardData.status;
                    }
                })
            }
            else{
                this.triggerInfoToast('You current status is already set to \'' + this.selectedStatus.name + '\'');
            }


        },
        handleChecklist() {

            let parser = new DOMParser();
            let doc = parser.parseFromString(this.cloneCardData.shared_task_data.description, 'text/html');
            let uls = doc.querySelectorAll('ul[data-checked]');

            if (uls.length === 0) {
                this.formatted = this.cloneCardData.shared_task_data.description;
            }

            for (let i = 0; i < uls.length; i++) {

                let div = document.createElement('div');

                for (let x = 0; x < uls[i].childNodes.length; x++) {
                    let ul = document.createElement('ul');
                    let li = document.createElement('li');
                    li.onclick = () => {
                        alert(x);
                    };

                    ul.setAttribute('data-checked', uls[i].attributes[0].value);
                    li.innerHTML = (uls[i].childNodes[x].innerText);
                    ul.appendChild(li);
                    div.appendChild(ul);
                }

                uls[i].replaceWith(div);
                this.formatted = new XMLSerializer().serializeToString(doc);

                this.$nextTick(() => {
                    let ul = document.querySelectorAll('ul[data-checked]');
                    for (let i = 0; i < ul.length; i++) {
                        ul[i].replaceWith(ul[i].cloneNode(true));
                    }
                });

                this.$nextTick(() => {

                    let ul = document.querySelectorAll('#task-description ul[data-checked]');
                    this.checklistData.total = document.querySelectorAll('#task-description ul[data-checked] li');

                    let done = document.querySelectorAll('#task-description ul[data-checked="true"] li');
                    let total = document.querySelectorAll('#task-description ul[data-checked] li');

                    this.checklistData.done = done.length;
                    this.checklistData.total = total.length;


                    for (let i = 0; i < ul.length; i++) {
                        ul[i].addEventListener("click", el => {

                            this.clickedCheckboxEl = el.currentTarget;

                            let toggle = this.clickedCheckboxEl.getAttribute("data-checked") === 'true' ? "false" : "true";
                            this.clickedCheckboxEl.setAttribute('data-checked', toggle);
                            this.cardData.shared_task_data.description = (document.getElementById('task-description').innerHTML);

                            let done = document.querySelectorAll('#task-description ul[data-checked="true"] li');
                            let total = document.querySelectorAll('#task-description ul[data-checked] li');

                            this.checklistData.done = done.length;
                            this.checklistData.total = total.length;

                            this.asyncUpdateDescription({
                                'description': this.cardData.shared_task_data.description,
                                'id': this.cardData.id,
                                'isChecked': this.clickedCheckboxEl.getAttribute("data-checked"),
                                'checkboxContent': this.clickedCheckboxEl.querySelector('li').innerHTML
                            }).then(() => {
                                this.asyncGetTaskData(this.cardData.id).then((data) => {
                                    this.cloneCardData = data.data;
                                    this.eventHub.$emit("update-add-task-data-with-group-data", data.data);
                                }).catch(res => {
                                    console.log(res)
                                });
                            })

                        }, true);
                    }
                })
            }
        },
    },

    computed: {
        priorityHue() {
            if (this.cardData.hours_to_deadline === null) {
                return 0;
            } else if (this.cardData.hours_to_deadline <= 0) {
                return 0;
            } else if (this.cardData.hours_to_deadline >= 200) {
                return 100;
            } else {
                return this.cardData.hours_to_deadline / 2;
            }
        },

        timeRemaining() {
            if (this.cardData.hours_to_deadline <= 0) {
                return ('Time Remaining: 0 days, 0 hours');
            }
            if (this.cardData.hours_to_deadline) {
                let totalHours = this.cardData.hours_to_deadline;
                let days = Math.floor(this.cardData.hours_to_deadline / 24);
                let hours = totalHours - days * 24
                return ('Time Remaining: days: ' + days + ', hours: ' + hours);
            } else {
                return ('No set deadline');
            }
        },

        checklistPercentage() {
            return Math.floor(this.checklistData.done * 100 / this.checklistData.total)
        },

        statusColor() {
            switch (this.cardData.status) {
                case 'active':
                    return 'green'
                case 'cancelled':
                    return 'red'
                case 'completed':
                    return 'blue'
                default:
                    return 'blue'
            }
        }
    },
};
</script>
