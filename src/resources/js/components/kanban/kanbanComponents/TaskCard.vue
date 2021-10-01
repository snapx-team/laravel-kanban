<template>
    <div class="bg-white hover:shadow-md shadow rounded px-3 pt-3 pb-5 border-l-4"
         v-bind:class="`border-indigo-500`" :style=priorityHue
         v-if="showCard">
        <div class="flex justify-between">
            <p class="text-gray-700 font-semibold font-sans tracking-wide text-sm mr-3">
                <span class="font-bold">{{ task_card.task_simple_name }}: </span>
                <span class="italic">{{ task_card.name }}</span>
            </p>


            <div class="flex justify-end">
                <template v-for="(employee, employeeIndex) in task_card.assigned_to">
                    <template v-if="employeeIndex < 3">
                        <avatar :borderColor="'white'"
                                :borderSize="0"
                                :class="{ '-ml-2': employeeIndex > 0 }"
                                :key="employeeIndex"
                                :name="employee.employee.user.full_name"
                                :size="6"
                                :tooltip="true"></avatar>
                    </template>
                </template>

                <div class="flex" v-if="task_card.assigned_to">
                  <span
                      class="z-10 flex items-center justify-center font-semibold text-gray-800 text-xs w-6 h-6 rounded-full bg-gray-300 border-white border -ml-2 pr-1"
                      v-if="task_card.assigned_to.length > 3">+{{ task_card.assigned_to.length - 3 }}
                  </span>
                </div>
            </div>
        </div>

        <div class="space-y-1 pt-3 pb-2">

            <div v-if="task_card.shared_task_data.erp_employees.length" class="text-xs text-gray-600 flex space-x-1">
                <div class="font-bold leading-4 tracking-wide text-gray-600 w-20">
                    <span v-if="task_card.shared_task_data.erp_employees.length > 1"> Employees: </span>
                    <span v-else>Employee: </span>
                </div>
                <div class="flex flex-wrap flex-1">
                    <p class="pr-1" v-for="(employee, index) in task_card.shared_task_data.erp_employees">{{ employee.full_name }}
                        <span v-if="index+1 !== task_card.shared_task_data.erp_employees.length">,</span>
                    </p>
                </div>
            </div>
            <div v-if="task_card.shared_task_data.erp_contracts.length" class="text-xs text-gray-600 flex space-x-1">
                <div class="font-bold leading-4 tracking-wide text-gray-600 w-20">
                    <span v-if="task_card.shared_task_data.erp_contracts.length > 1"> Contracts: </span>
                    <span v-else>Contract: </span>
                </div>
                <div class="flex flex-wrap">
                    <p class="pr-1" v-for="(contract, index) in task_card.shared_task_data.erp_contracts">{{contract.contract_identifier }}
                        <span v-if="index+1 !== task_card.shared_task_data.erp_contracts.length">,</span>
                    </p>
                </div>

            </div>
            <div v-if="task_card.deadline !== null" class="text-xs text-gray-600 flex space-x-1">
                <div class="font-bold leading-4 tracking-wide text-gray-600 w-20">
                    <span >Deadline: </span>
                </div>
                <div>
                    <p>{{ task_card.deadline | moment("DD MMM, YYYY") }}</p>
                </div>
            </div>
        </div>
        <div class="flex mt-2 justify-between items-center">
            <div class="flex flex-wrap items-center">
                <avatar :name="task_card.reporter.user.full_name" :size="6" :tooltip="true"
                        class="border border-white"></avatar>
                <span class="text-xs text-gray-600 px-1"> • {{ task_card.created_at | moment("DD MMM, YYYY") }}</span>
            </div>
            <badge :name="task_card.badge.name" v-if="task_card.badge !== []"></badge>
        </div>
    </div>


</template>
<script>
import Avatar from "../../global/Avatar.vue";
import Badge from "../../global/Badge.vue";
import {helperFunctions} from "../../../mixins/helperFunctionsMixin";

export default {
    inject: ["eventHub"],
    mixins: [helperFunctions],

    components: {
        Badge,
        Avatar,
    },
    data() {
        return {
            allCurrentIDs: [],
        };
    },
    props: {
        task_card: Object,
    },
    created() {
        this.eventHub.$on("show-employee-tasks", (idArray) => {
            this.allCurrentIDs = idArray;
        });
    },
    computed: {
        showCard() {
            if (this.allCurrentIDs.length === 0) {
                return true;
            }
            for (let i = 0; i < this.task_card.assigned_to.length; i++) {
                for (let j = 0; j < this.allCurrentIDs.length; j++) {
                    if (this.task_card.assigned_to[i].employee.id === this.allCurrentIDs[j]) {
                        return true;
                    }
                }
            }
            return false;
        },
        priorityHue() {
            if (this.task_card.hours_to_deadline === null) {
                return 'border-color: hsl( 0 , 0%, 40%);'
            } else if (this.task_card.hours_to_deadline <= 0) {
                return 'border-color: hsl( 0 , 90%, 40%); background-color:hsl(0, 100%, 97%)'
            } else if (this.task_card.hours_to_deadline >= 200) {
                return 'border-color: hsl( 100 , 90%, 40%)'
            } else {
                return 'border-color: hsl( ' + this.task_card.hours_to_deadline / 2 + ' , 100%, 50%)'
            }
        },
    },
};
</script>
