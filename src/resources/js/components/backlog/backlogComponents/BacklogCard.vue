<template>
    <div
        class="rounded border-l-4"
        v-bind:class="`border-indigo-500`" :style=priorityHue
        @click="onClick(task)"
    >
        <div class="border-gray-300 border px-3 py-3">
            <div class="flex flex-no-wrap">
                <div class="min-w-0 flex">
                    <div class="w-auto mr-3 -mt-1">
                        <badge :name="task.badge.name" class="inline-flex" v-if="task.badge !== []"></badge>
                    </div>
                    <p class="truncate flex-shrink text-gray-700 font-semibold font-sans tracking-wide text-sm mr-3">
                        {{ task.name }}
                    </p>

                </div>

                <div class="flex flex-1 justify-end">
                    <div class="flex mx-3 -mt-1">
                        <badge :name="task.board.name"></badge>
                    </div>
                    <div class="flex">
                        <div class="flex w-32">
                            <div class="flex flex-wrap items-center">
                                <avatar
                                    class="border border-white"
                                    :name="task.reporter.full_name"
                                    :size="6"
                                    :tooltip="true"
                                ></avatar>
                                <span class="text-sm text-gray-600 px-1">
                  • {{ moment(task.deadline).format("MMM Do") }}
                </span>
                            </div>
                        </div>

                        <div class="flex w-20">
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
                                <span
                                    v-if="task.assigned_to.length > 3"
                                    class="z-10 flex items-center justify-center font-semibold text-gray-800 text-xs w-6 h-6 rounded-full bg-gray-300 border-white border -ml-2 pr-1"
                                >+{{ task.assigned_to.length - 3 }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Avatar from "../../global/Avatar.vue";
import Badge from "../../global/Badge.vue";
import moment from 'moment'

export default {
    inject: ["eventHub"],
    components: {
        Avatar,
        Badge
    },
    created: function () {
        this.moment = moment;
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
    },
    computed: {
        priorityHue() {
            if (this.task.hours_to_deadline <= 0) {
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
