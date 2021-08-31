<template>

    <div class="py-3 px-4 mb-2 border-l-4 bg-gray-50 shadow-sm" :class="`border-${computedLogInfo.color}-400 `">

        <div class="flex justify-between">

            <div class="flex">
                <div class="pr-5 flex items-center">
                    <div
                        class="text-white p-3 text-center inline-flex items-center justify-center w-12 h-12 shadow rounded-full"
                        :class="`bg-${computedLogInfo.color}-400 `">
                        <i class="fas" :class="computedLogInfo.icon"></i></div>
                </div>
                <div class="flex-1 ">
                    <p>
                        <span class="font-bold text-l">{{ log.user.full_name }}</span>
                        <span> {{ computedLogInfo.type }} </span>
                    </p>
                    <p class="text-gray-600">{{ log.description }}</p>
                    <p class="text-gray-700 text-xs mt-1">{{ moment(log.created_at).format("MMMM Do h:mm a") }}</p>
                </div>
            </div>
            <div v-if="log.board">
                <badge :name="log.board.name"></badge>
            </div>
        </div>
    </div>
</template>

<script>
import moment from 'moment'
import Badge from "../../../global/Badge.vue";

export default {
    inject: ["eventHub"],
    components: {
        Badge
    },
    created: function () {
        this.moment = moment;
    },
    props: {
        log: {},
    },
    data() {
        return {
            title: '',
            subtitle: '',
        }
    },
    computed: {
        computedLogInfo() {
            switch (this.log.log_type) {
                case 10:
                    return {
                        "type": "created a task",
                        "icon": "fa-tasks",
                        "color": "blue",
                    };
                case 11:
                    return {
                        "type": "cancelled a task",
                        "icon": "fa-tasks",
                        "color": "red",
                    };
                case 12:
                    return {
                        "type": "closed a task",
                        "icon": "fa-archive",
                        "color": "green",
                    };
                case 13:
                    return {
                        "type": "assigned a task a user",
                        "icon": "fa-people-arrows",
                        "color": "purple",
                    };
                case 14:
                    return {
                        "type": "moved a task",
                        "icon": "fa-arrows-alt",
                        "color": "indigo",
                    };
                case 15:
                    return {
                        "type": "assigned a task to a row and column",
                        "icon": "fa-vote-yea",
                        "color": "purple",
                    };
                case 16:
                    return {
                        "type": "updated a task",
                        "icon": "fa-tasks",
                        "color": "yellow",
                    };
                case 17:
                    return {
                        "type": "upodated group",
                        "icon": "fa-layer-group",
                        "color": "purple",
                    };
                case 19:
                    return {
                        "type": "updated assignees",
                        "icon": "fa-people-arrows",
                        "color": "yellow",
                    };
                case 20:
                    return {
                        "type": "checked a checklist item",
                        "icon": "fa-check-square",
                        "color": "green",
                    };
                case 21:
                    return {
                        "type": "unchecked a checklist item",
                        "icon": "fa-square",
                        "color": "yellow",
                    };
                case 22:
                    return {
                        "type": "assigned card to new user",
                        "icon": "fa-user-plus",
                        "color": "green",
                    };
                case 23:
                    return {
                        "type": "unassigned card from a user",
                        "icon": "fa-user-minus",
                        "color": "red",
                    };
                case 70:
                    return {
                        "type": "wrote a comment",
                        "icon": "fa-comment",
                        "color": "blue",
                    };
                case 90:
                    return {
                        "type": "created a new badge",
                        "icon": "fa-award",
                        "color": "purple",
                    };
                default:
                    return {
                        "type": "undefined",
                        "icon": "fa-award",
                        "color": "gray",
                    };
            }
        }
    },
};
</script>
