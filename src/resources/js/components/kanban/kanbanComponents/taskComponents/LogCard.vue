<template>
    <div class="py-3 px-4 mb-2 border-l-4 bg-gray-50 shadow-sm" :class="`border-${formattedLog.color}-400 `">
        <div class="flex justify-between">
            <div class="flex">
                <div class="pr-5 flex items-center">
                    <div
                        class="text-white p-3 text-center inline-flex items-center justify-center w-12 h-12 shadow rounded-full"
                        :class="`bg-${formattedLog.color}-400 `">
                        <i class="fas" :class="formattedLog.icon"></i></div>
                </div>
                <div class="flex-1 ">
                    <p>
                        <span class="font-bold text-l">{{ log.user.full_name }}</span>
                        <span> {{ formattedLog.type }} </span>
                    </p>
                    <p class="text-gray-600">{{ formattedLog.desc }}</p>
                    <p class="text-gray-700 text-xs mt-1">{{ moment(log.created_at).format("MMMM Do h:mm a") }}</p>
                </div>
            </div>
            <div v-if="log.loggable && log.loggable_type === 'Xguard\\LaravelKanban\\Models\\Task'"
                 class="flex flex-col justify-between items-end">

                <!--BOARD LINK-->
                <router-link v-if="log.loggable.board && !log.loggable.board.deleted_at"
                             :to="{ name: 'board', query: { id: log.loggable.board.id } }">
                    <badge class="cursor-pointer" :name="log.loggable.board.name"></badge>
                </router-link>
                <p v-else class="text-red-200">Board Deleted</p>

                <!--TASK LINK-->
                <router-link v-if="!log.loggable.deleted_at"
                             :to="{ name: 'board', query: { name: 'board', id: log.loggable.board.id, task: log.loggable.id} }">
                    <h2 class="cursor-pointer text-gray-500 hover:text-gray-700 transition duration-300 ease-in-out focus:outline-none">
                        <span class="ml-1 font-bold">{{ log.loggable.task_simple_name }}</span>
                        <i class="fas fa-arrow-right"></i>
                    </h2>
                </router-link>
                <p v-else class="text-red-200">Task Deleted</p>
            </div>
            <div v-else-if="log.loggable && log.loggable_type === 'Xguard\\LaravelKanban\\Models\\Board'"
                 class="flex flex-col justify-between items-end">
                <!--BOARD LINK-->
                <router-link v-if="!log.loggable.deleted_at" :to="{ name: 'board', query: { id: log.loggable.id} }">
                    <badge class="cursor-pointer" :name="log.loggable.name"></badge>
                </router-link>
                <p v-else class="text-red-200">Board Deleted</p>

            </div>
            <div v-else-if="log.loggable && log.loggable_type === 'Xguard\\LaravelKanban\\Models\\Comment'"
                 class="flex flex-col justify-between items-end">

                <!--BOARD LINK-->
                <router-link v-if="log.loggable.task && log.loggable.task.board && !log.loggable.task.board.deleted_at"
                             :to="{ name: 'board', query: { id: log.loggable.task.board.id} }">
                    <badge class="cursor-pointer inline-flex" :name="log.loggable.task.board.name"></badge>
                </router-link>
                <p v-else class="text-red-200">board Deleted</p>


                <!--TASK LINK-->
                <router-link v-if="log.loggable.task && !log.loggable.task.deleted_at"
                             :to="{ name: 'board', query: { id: log.loggable.task.board.id, task: log.loggable.task.id} }">
                    <h2 class="cursor-pointer text-gray-500 hover:text-gray-700 transition duration-300 ease-in-out focus:outline-none">
                        <span class="ml-1 font-bold">{{ log.loggable.task.task_simple_name }}</span>
                        <i class="fas fa-arrow-right"></i>
                    </h2>
                </router-link>
                <p v-else class="text-red-200">Task Deleted</p>
            </div>
        </div>
    </div>
</template>

<script>

/**
 * @property {string} loggable_type
 * @property {date} created_at
 * @property {string} task_simple_name
 * @property {string} deleted_at
 */

import moment from 'moment'
import Badge from "../../../global/Badge.vue";
import {formattedLog} from "../../../../mixins/logFormatterMixin";

export default {
    inject: ["eventHub"],

    mixins: [formattedLog],

    components: {
        Badge
    },
    created: function () {
        this.moment = moment;
        this.formattedLog = this.getFormattedLog(this.log);

    },
    props: {
        log: {},
    },
    data() {
        return {
            formattedLog: null
        }
    },
};
</script>
