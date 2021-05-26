<template>
    <div class="bg-white hover:shadow-md shadow rounded px-3 pt-3 pb-5 border-l-4"
         v-bind:class="`border-${task_card.employee.is_active}-400`">
        <div class="flex justify-between">
            <p class="text-gray-700 font-semibold font-sans tracking-wide text-sm mr-3">
                TASK TITLE </p>

            <div class="flex justify-end">
                <template v-for="(user, userIndex) in task_card.assignedTo">
                    <template v-if="userIndex < 3">
                        <avatar :borderColor="'white'"
                                :borderSize="0"
                                :class="{ '-ml-2': userIndex > 0 }"
                                :key="userIndex"
                                :name="user.name"
                                :size="6"
                                :tooltip="true"></avatar>
                    </template>
                </template>

                <div class="flex" v-if="task_card.assignedTo">
          <span class="z-10 flex items-center justify-center font-semibold text-gray-800 text-xs w-6 h-6 rounded-full bg-gray-300 border-white border -ml-2 pr-1"
                v-if="task_card.assignedTo.length > 3">+{{ task_card.assignedTo.length - 3 }}
          </span>
                </div>
            </div>
        </div>
        <div class="flex mt-4 justify-between items-center">
            <div class="flex flex-wrap items-center">
                <avatar :name="task_card.creator" :size="6" :tooltip="true" class="border border-white"></avatar>
                <span class="text-sm text-gray-600 px-1"> • {{ task_card.date }} </span>
            </div>
            <badge :color="task_card.badge.color" v-if="task_card.badge">{{ task_card.badge.title }}</badge>
        </div>

    </div>

    <!--        <div class="flex justify-between items-center">-->
    <!--            <div class="flex flex-wrap items-center">-->
    <!--                <span class="text-sm text-gray-600"> {{ task_card.employee.user.full_name }} </span>-->
    <!--            </div>-->
    <!--            <avatar :name="task_card.employee.user.full_name"-->
    <!--                    :size="6"-->
    <!--                    :tooltip="false"-->
    <!--                    class="border border-white"></avatar>-->
    <!--        </div>-->
</template>
<script>
    import Avatar from "../../global/Avatar.vue";
    import Badge from "./Badge.vue";

    export default {
        components: {
            Badge,
            Avatar
        },
        props: {
            task_card: Object,
        },
    };
</script>
