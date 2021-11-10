<template>
    <div class="rounded border-l-4"
         v-bind:class="`border-indigo-500`" :style=priorityHue
         @click="onClick(task)">
        <hsc-menu-style-white>
            <hsc-menu-context-menu :menuZIndex="3" class="w-full">
                <div class="border-gray-300 border px-3 py-3">

                    <div class="flex flex-no-wrap">

                        <div class="min-w-0 flex">
                            <div class="w-32 mr-3 -mt-1 flex-none">
                                <badge :name="task.badge.name" class="inline-flex" v-if="task.badge !== []"></badge>
                            </div>
                            <p class="truncate text-gray-700 font-semibold font-sans tracking-wide text-sm mr-3">
                                <span class="font-bold">{{ task.task_simple_name }}: </span>
                                <span class="italic">{{ task.name }}</span>
                            </p>
                        </div>

                        <div class="flex-1 justify-end hidden xl:flex">
                            <div class="flex mx-3 -mt-1">
                                <badge :name="task.board.name"></badge>
                            </div>
                            <div class="flex">
                                <div class="flex w-32">
                                    <div class="flex flex-wrap items-center">
                                        <avatar
                                            class="border border-white"
                                            :name="task.reporter.user.full_name"
                                            :size="6"
                                            :tooltip="true"></avatar>
                                        <span class="text-sm text-gray-600 px-1">• {{
                                                moment(task.created_at).format("MMM Do")
                                            }}</span>
                                    </div>
                                </div>

                                <div class="flex w-20 items-center">
                                    <template v-for="(assignedEmployee, employeeIndex) in task.assigned_to">
                                        <template v-if="employeeIndex < 3">
                                            <avatar
                                                :key="employeeIndex"
                                                :class="{ '-ml-2': employeeIndex > 0 }"
                                                :name="assignedEmployee.employee.user.full_name"
                                                :size="6"
                                                :borderSize="0"
                                                :borderColor="'white'"
                                                :tooltip="true"
                                            ></avatar>
                                        </template>
                                    </template>

                                    <div v-if="task.assigned_to" class="flex">
                                        <span v-if="task.assigned_to.length > 3"
                                              class="z-10 flex items-center justify-center font-semibold text-gray-800 text-xs w-6 h-6 rounded-full bg-gray-300 border-white border -ml-2 pr-1">
                                            +{{ task.assigned_to.length - 3 }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pl-2 border-gray-400 border-l-2 my-1"
                         v-if="task.shared_task_data.erp_employees.length || task.shared_task_data.erp_contracts.length || task.deadline !== null">

                        <div v-if="task.shared_task_data.erp_employees.length"
                             class="text-xs text-gray-600 flex space-x-1">
                            <div class="font-bold leading-4 tracking-wide text-gray-600 w-32 flex-none">
                                <span v-if="task.shared_task_data.erp_employees.length > 1"> Employees: </span>
                                <span v-else>Employee: </span>
                            </div>
                            <div class="flex flex-wrap flex-1">
                                <p class="pr-1" v-for="(employee, index) in task.shared_task_data.erp_employees">{{
                                    employee.full_name }}
                                    <span v-if="index+1 !== task.shared_task_data.erp_employees.length">,</span>
                                </p>
                            </div>
                        </div>
                        <div v-if="task.shared_task_data.erp_contracts.length"
                             class="text-xs text-gray-600 flex space-x-1">
                            <div class="font-bold leading-4 tracking-wide text-gray-600 w-32 flex-none">
                                <span v-if="task.shared_task_data.erp_contracts.length > 1"> Contracts: </span>
                                <span v-else>Contract: </span>
                            </div>
                            <div class="flex flex-wrap">
                                <p class="pr-1" v-for="(contract, index) in task.shared_task_data.erp_contracts">
                                    {{contract.contract_identifier }}
                                    <span v-if="index+1 !== task.shared_task_data.erp_contracts.length">,</span>
                                </p>
                            </div>

                        </div>
                        <div v-if="task.deadline !== null" class="text-xs text-gray-600 flex space-x-1">
                            <div class="font-bold leading-4 tracking-wide text-gray-600 w-32 flex-none">
                                <span>Deadline: </span>
                            </div>
                            <div>
                                <p>{{ task.deadline | moment("DD MMM, YYYY") }}</p>
                            </div>
                        </div>
                    </div>


                </div>
                <template slot="contextmenu">
                    <hsc-menu-item label="status">
                        <hsc-menu-item label="Active" v-model="selectedStatus" value="active"
                                       :disabled="this.task.status === 'active'" @click="updateStatus('active')"/>
                        <hsc-menu-item label="Completed" v-model="selectedStatus" value="completed"
                                       :disabled="this.task.status === 'completed'" @click="updateStatus('completed')"/>
                        <hsc-menu-item label="Cancelled" v-model="selectedStatus" value="cancelled"
                                       :disabled="this.task.status === 'cancelled'" @click="updateStatus('cancelled')"/>
                    </hsc-menu-item>
                </template>

            </hsc-menu-context-menu>
        </hsc-menu-style-white>
    </div>
</template>


<script>
import Avatar from "../../global/Avatar.vue";
import Badge from "../../global/Badge.vue";
import moment from 'moment'
import {ajaxCalls} from "../../../mixins/ajaxCallsMixin";


export default {
    inject: ["eventHub"],
    components: {
        Avatar,
        Badge
    },

    mixins: [ajaxCalls],

    created: function () {
        this.moment = moment;
        this.selectedStatus = [this.task.status];
    },
    data() {
        return {
            selectedStatus: ['active'],
        }
    },
    props: {
        task: {
            badge: {
                color: {
                    type: String,
                    default: "gray",
                },
            },
            priority: {
                color: {
                    type: String,
                    default: "gray",
                },
            },
            creator: Object,
            assignedTo: Object,
            required: true,
            default: () => ({}),
        },
    },
    methods: {
        onClick(task) {
            this.eventHub.$emit("open-task-view", task);
        },
        updateStatus(status) {
            this.$swal({
                icon: 'info',
                title: 'Set status to ' + status,
                text: 'This will ' + (status === 'active' ? 'un-' : '') + 'archive the task',
                showCancelButton: true,
                confirmButtonText: `Continue`,
            }).then((result) => {
                if (result.isConfirmed) {
                    this.asyncSetStatus(this.task.id, status).then(() => {
                        this.triggerSuccessToast('Status Updated');
                        this.eventHub.$emit("reload-backlog-data");
                    }).catch(res => {
                        console.log(res)
                    });
                } else {
                    this.selectedStatus = [this.task.status];
                }
            })
        },
    },
    computed: {
        priorityHue() {
            if (this.task.hours_to_deadline === null) {
                return 'border-color: hsl( 0 , 0%, 40%);'
            } else if (this.task.hours_to_deadline <= 0) {
                return 'border-color: hsl( 0 , 90%, 40%); background-color:hsl(0, 100%, 97%)'
            } else if (this.task.hours_to_deadline >= 200) {
                return 'border-color: hsl( 100 , 90%, 40%)'
            } else {
                return 'border-color: hsl( ' + this.task.hours_to_deadline / 2 + ' , 100%, 50%)'
            }
        },
    },
};
</script>
