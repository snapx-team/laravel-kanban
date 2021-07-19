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


            <div class="pb-4 w-60">
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
                <div class="relative mb-5 border-b">
                    <div class="flex mb-3 items-center justify-between">
                        <div>
                            <span
                                class="text-xs font-semibold inline-block py-1 px-2 rounded-full text-indigo-600 bg-indigo-200">
                                checklist items: {{ checklistData.done }}/{{ checklistData.total }}
                            </span>
                        </div>
                        <div class="text-right">
                            <span
                                class="text-xs font-semibold inline-block text-indigo-600">{{
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
                    <avatar :name="cardData.reporter.full_name" :size="6" :tooltip="true"
                            class="border border-white max-w-md"></avatar>
                    <span class="text-xs text-gray-600 px-1"> {{ cardData.reporter.full_name }}</span>
                </div>

                <div class="flex">
                    <div class="w-32 flex text-gray-700">
                        <i class="fas fa-people-arrows mr-2"></i>
                        <p class="font-semibold font-sans tracking-wide text-sm"> Assigned To: </p>
                    </div>
                    <div class="space-y-2">
                        <span v-if=" cardData.assigned_to.length === 0"
                              class="text-xs text-gray-600 px-1"> unnasigned</span>
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

                        <div v-if="cardData.erp_employee_id !== null" class="text-sm flex  items-center">
                            <div class="w-32 flex items-center text-gray-700">
                                <i class="fas fa-user mr-2"></i>
                                <p class="font-semibold font-sans tracking-wide text-sm">Employee: </p>
                            </div>
                            <avatar :name="cardData.erp_employee.full_name" :size="6" :tooltip="true"
                                    class="border border-white max-w-md"></avatar>
                            <span class="text-xs text-gray-600 px-1"> {{ cardData.erp_employee.full_name }}</span>
                        </div>
                        <div v-if="cardData.erp_job_site_id !== null" class="text-sm text-gray-700 flex">
                            <div class="w-32 flex items-center">
                                <i class="fas fa-building mr-2"></i>
                                <p class=" font-semibold font-sans tracking-wide text-sm">Job Site: </p>
                            </div>
                            <p class="max-w-md text-xs">{{ cardData.erp_job_site.name }}</p>
                        </div>
                        <div v-if="cardData.deadline !== null" class="text-sm text-gray-700 flex">
                            <div class="w-32 flex items-center">
                                <i class="fas fa-stopwatch mr-2"></i>
                                <p class="font-semibold font-sans tracking-wide text-sm">Deadline: </p>
                            </div>
                            <p class="max-w-md text-xs">{{ cardData.deadline | moment("DD MMM, YYYY") }}</p>
                        </div>
                    </div>

                </div>

                <p class="text-gray-700 font-semibold font-sans tracking-wide text-lg"
                   :class="{'animate-pulse': loadingRelatedTasks}"> Related Tasks: </p>

                <p v-if="relatedTasks.length === 0" class="tracking-wide text-sm">No Related Tasks</p>

                <task-card
                    :key="task_card.id"
                    :task_card="task_card"
                    class="mt-3 cursor-move"
                    v-for="task_card in relatedTasks"
                    v-on:click.native="updateTask(task_card)"></task-card>

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
            relatedTasks: [],
            formatted: null,
            checklistData: {
                total: 0,
                done: 0,
            },
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
            ]
        };
    },

    created() {
        this.cloneCardData = {...this.cardData};
        this.handleChecklist();
        this.getRelatedTasks();
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
        setStatus() {
            console.log(this.cloneCardData.id);
            console.log(this.selectedStatus);
            this.asyncSetStatus(this.cloneCardData.id, this.selectedStatus.name).then(() => {
                this.cardData.status = this.selectedStatus.name;
            }).catch(res => {
                console.log(res)
            });
        },
        handleChecklist() {

            let parser = new DOMParser();
            let doc = parser.parseFromString(this.cloneCardData.description, 'text/html');
            let uls = doc.querySelectorAll('ul[data-checked]');

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
                            let toggle = el.currentTarget.getAttribute("data-checked") === 'true' ? "false" : "true";
                            el.currentTarget.setAttribute('data-checked', toggle);
                            this.cardData.description = (document.getElementById('task-description').innerHTML);

                            let done = document.querySelectorAll('#task-description ul[data-checked="true"] li');
                            let total = document.querySelectorAll('#task-description ul[data-checked] li');

                            this.checklistData.done = done.length;
                            this.checklistData.total = total.length;

                            this.asyncUpdateDescription({
                                'description': this.cardData.description,
                                'id': this.cardData.id
                            });


                        }, true);
                    }
                })
            }
        },
    },

    computed: {
        priorityHue() {
            if (this.cardData.hours_to_deadline === null) {
                return 0
            }
            if (this.cardData.hours_to_deadline <= 0) {
                return 0
            } else if (this.cardData.hours_to_deadline >= 200) {
                return 100
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
