<template>
    <div>
        <transition enter-active-class="transition duration-500 ease-out transform"
                    enter-class=" opacity-0 bg-blue-200"
                    leave-active-class="transition duration-300 ease-in transform"
                    leave-to-class="opacity-0 bg-blue-200">
            <div class="overflow-auto fixed inset-0 bg-gray-700 bg-opacity-50 z-30" v-if="modalOpen"></div>
        </transition>

        <transition enter-active-class="transition duration-300 ease-out transform "
                    enter-class="scale-95 opacity-0 -translate-y-10"
                    enter-to-class="scale-100 opacity-100"
                    leave-active-class="transition duration-150 ease-in transform"
                    leave-class="scale-100 opacity-100"
                    leave-to-class="scale-95 opacity-0">
            <!-- Modal container -->

            <div class="fixed inset-0 z-40 flex items-start justify-center" v-if="modalOpen">
                <!-- Close when clicked outside -->
                <div @click="modalOpen = false" class="overflow-auto fixed h-full w-full"></div>
                <div class="flex flex-col overflow-auto z-50 w-100 bg-white rounded-md shadow-2xl m-10"
                     style="width: 900px; min-height: 300px; max-height: 80%">
                    <!-- Heading -->
                    <div class="flex justify-between p-5 bg-indigo-800 border-b">
                        <div class="space-y-1">

                            <div>
                                <h1 class="text-2xl text-white pb-2">Notification Settings</h1>
                                <p class="text-sm font-medium leading-5 text-gray-400">
                                    <em>Alter which types of notifications you want to be notified with for each
                                        board</em>
                                </p>
                            </div>
                        </div>
                        <div>
                            <button @click="modalOpen = false"
                                    class="focus:outline-none flex flex-col items-center text-gray-400 hover:text-gray-500 transition duration-150 ease-in-out pl-8"
                                    type="button">
                                <i class="fas fa-times"></i>
                                <span class="text-xs font-semibold text-center leading-3 uppercase">Esc</span>
                            </button>
                        </div>
                    </div>
                    <!-- Container -->
                    <div v-if="loadingBoards" class="flex flex-auto">
                        <loading-animation :size="60" class="m-auto"></loading-animation>
                    </div>
                    <div v-else class="flex">
                        <div class="px-8 py-6 space-y-2 border-r mt-5 mb-5">

                            <div class="pb-2">
                                <p class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600 pb-2">
                                    General</p>
                                <div @click="selectedBoard = null"
                                     class="flex items-center px-3 rounded-lg my-2 bg-gray-100 hover:bg-gray-300 py-2 cursor-pointer">
                                    <span class="text-sm font-medium text-gray-900 pl-2">Information</span>
                                </div>
                            </div>

                            <div class="pb-2">
                                <p class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600 pb-2">
                                    Boards (member of)</p>
                                <div v-for="(board, boardIndex) in boardsWithEmployeeNotificationSettings">
                                    <div :key="boardIndex"
                                         @click="selectedBoard = board"
                                         class="flex items-center px-3 rounded-lg my-2 bg-gray-100 hover:bg-gray-300 py-2 cursor-pointer">
                                        <avatar :name="board.name" :size="5" :tooltip="false"></avatar>
                                        <span class="text-sm font-medium text-gray-900 pl-2">{{ board.name }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3 overflow-auto px-8 py-6 flex-1 mt-5 mb-5">
                            <div v-if="selectedBoard === null">
                                <div>
                                    <h3 class="text-2xl mb-2 font-semibold leading-normal">Customize Your Notification
                                        Settings</h3>
                                    <p class="text-lg font-light leading-relaxed mt-4 mb-4 text-blueGray-600">When you
                                        click on a board in the left column, you can set your notification preference
                                        for each of the notification types below</p>
                                    <div class="block pb-6">
                                        <p v-for="notification in notificationTypeList"
                                           class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-gray-700 bg-gray-100 uppercase mr-2 mt-2">
                                            {{ notification.name }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="space-y-2 ">
                                <h3 class="text-2xl mb-4 font-semibold leading-normal">{{ selectedBoard.name }}</h3>
                                <div v-for="(notification, notificationIndex) in notificationTypeList"
                                     class="flex justify-between border rounded border-gray-300 p-2">

                                    <div class="mr-3 text-gray-700 font-medium flex-grow">
                                        <p>{{ notification.name }}</p>
                                        <small v-if="!checkIsChecked(notification.group)"
                                               class="text-green-700">You will be notified</small>
                                        <small v-else class="text-red-700">You won't be notified</small>

                                    </div>
                                    <label :key="notificationIndex" class="flex items-start cursor-pointer"
                                           :for="'ignore-type'+notificationIndex">
                                        <div class="relative mt-1">
                                            <input class="hidden"
                                                   :id="'ignore-type'+notificationIndex"
                                                   type="checkbox"
                                                   :checked="!checkIsChecked(notification.group)"
                                                   @click="setSelectedNotificationGroup(notification.group)"
                                            />
                                            <div
                                                class="toggle__dot absolute w-5 h-5 bg-red-600 rounded-full shadow inset-y-0 left-0"></div>
                                            <div
                                                class="toggle__line w-10 h-4 bg-gray-400 rounded-full shadow-inner"></div>
                                        </div>
                                        <div class="w-20">
                                            <div class="ml-3 text-gray-700 font-medium"
                                                 v-if="!checkIsChecked(notification.group)">
                                                <p>Enabled</p>
                                            </div>
                                            <div class="ml-3 text-gray-700 font-medium" v-else>
                                                <p>Disabled</p>
                                            </div>
                                        </div>
                                    </label>
                                </div>

                            </div>


                            <div v-if="selectedBoard" class="w-full grid sm:grid-cols-2 gap-3 sm:gap-3">
                                <button @click="modalOpen = false"
                                        class="px-4 py-3 border border-gray-200 rounded text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-600 transition duration-300 ease-in-out"
                                        type="button">
                                    Cancel
                                </button>
                                <button @click="saveSettings($event)"
                                        class="px-4 py-3 border border-transparent rounded text-white bg-indigo-600 hover:bg-indigo-500 transition duration-300 ease-in-out"
                                        type="button">

                                    <span>Save Settings</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>
<script>

import vSelect from "vue-select";
import Avatar from "../../global/Avatar";

import {ajaxCalls} from "../../../mixins/ajaxCallsMixin";
import {helperFunctions} from "../../../mixins/helperFunctionsMixin";
import _ from "lodash";
import LoadingAnimation from "../../global/LoadingAnimation";

export default {
    inject: ["eventHub"],

    components: {
        LoadingAnimation,
        vSelect,
        Avatar
    },

    mixins: [ajaxCalls, helperFunctions],

    props: {
        notificationTypeList: Array,
    },

    data() {
        return {
            boardsWithEmployeeNotificationSettings: [],
            selectedBoard: null,
            modalOpen: false,
            loadingBoards: false,
            selectedNotificationGroups: {}
        };
    },

    created() {
        this.eventHub.$on("update-notification-settings", () => {
            this.modalOpen = true;
            this.selectedBoard = null;
            this.selectedNotificationGroups = {}
            this.getBoardsWithEmployeeNotificationSettings();
        });
    },

    beforeDestroy() {
        this.eventHub.$off('update-notification-settings');
    },

    methods: {

        checkIsChecked(group) {
            return this.selectedNotificationGroups.hasOwnProperty(this.selectedBoard.id) && this.selectedNotificationGroups[this.selectedBoard.id].includes(group);
        },

        setSelectedNotificationGroup(group, board = this.selectedBoard) {

            if (!this.selectedNotificationGroups.hasOwnProperty(board.id)) {
                this.$set(this.selectedNotificationGroups, board.id, [group]);  //we have to use $set to make new property in object reactive
            } else {
                if (!this.selectedNotificationGroups[board.id].includes(group)) {        //checking weather array contain the id
                    this.selectedNotificationGroups[board.id].push(group);               //adding to array because value doesnt exists
                } else {
                    this.selectedNotificationGroups[board.id].splice(this.selectedNotificationGroups[board.id].indexOf(group), 1);  //deleting
                }
            }
        },

        saveSettings() {
            this.asyncFirstOrCreateNotificationSettings(this.selectedNotificationGroups);
        },

        getBoardsWithEmployeeNotificationSettings() {
            this.loadingBoards = true;
            this.asyncGetBoardsWithEmployeeNotificationSettings().then((data) => {
                this.boardsWithEmployeeNotificationSettings = data.data;
                this.formatExistingIgnoreTypes()
                this.loadingBoards = false;

            })
        },

        formatExistingIgnoreTypes() {

            this.boardsWithEmployeeNotificationSettings.forEach((board) => {
                if (board['employee_notification_settings']) {
                    board['employee_notification_settings']['unserialized_options'].forEach((notificationType) => {
                            this.setSelectedNotificationGroup(notificationType, board)
                        }
                    );
                }
            });

        }
    },
};

</script>

<style scoped>
.toggle__dot {
    top: -0.1rem;
    transition: all 0.1s ease-in-out;
}

input:checked ~ .toggle__dot {
    transform: translateX(100%);
    background-color: #059669
}
</style>
