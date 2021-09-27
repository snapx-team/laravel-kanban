<template>
    <div class="w-64 px-8 py-4 bg-gray-100 overflow-auto shadow-lg">
        <div class="text-center">
            <i class="fas fa-th fa-3x p-3 text-indigo-800"></i>
            <h1 class="text-3xl tracking-wide text-indigo-800 font-bold my-0">
                XKANBAN </h1>
            <p class="text-xs text-indigo-800">
                Xguard Scheduler Kanban Solution </p>
        </div>

        <nav class="mt-8">
            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide text-left">
                General </h3>
            <div class="mt-2 -mx-3 pb-2">
                <router-link :to="{ path: '/kanban' }"
                             class="flex justify-between items-center px-3 py-2 hover:bg-gray-200 rounded-lg">
                    <span class="text-sm font-medium text-gray-900">Dashboard</span>
                </router-link>
            </div>
            <div class=" -mx-3 pb-2">
                <router-link :to="{ path: '/kanban/notifications' }"
                             class="flex justify-between items-center px-3 py-2 hover:bg-gray-200 rounded-lg">
                    <span class="text-sm font-medium text-gray-900">Notifications</span>
                    <div v-if="this.notifNumber > 0" class="py-4 pr-4 relative border-2 border-transparent text-gray-800 rounded-full hover:text-gray-400 focus:outline-none focus:text-gray-500 transition duration-150 ease-in-out">
                        <span class="absolute inset-0 object-right-top pt-0.5">
                            <div class="inline-flex items-center px-2 py-0.5 border-2 rounded-full text-xs font-semibold leading-4 bg-red-500 text-white">
                            {{ notifNumber }}
                            </div>
                        </span>
                    </div>
                </router-link>
                
            </div>
            
            <div class=" -mx-3 pb-2">
                <router-link :to="{ path: '/kanban/backlog' }"
                             class="flex justify-between items-center px-3 py-2 hover:bg-gray-200 rounded-lg">
                    <span class="text-sm font-medium text-gray-900">Backlog</span>
                </router-link>
            </div>
            <div class=" -mx-3 pb-2" v-if="$role === 'admin'">
                <router-link :to="{ path: '/kanban/metrics' }"
                             class="flex justify-between items-center px-3 py-2 hover:bg-gray-200 rounded-lg">
                    <span class="text-sm font-medium text-gray-900">Metrics</span>
                </router-link>
            </div>

            <hr/>

            <h3 class="mt-8 text-sm font-semibold text-gray-600 uppercase tracking-wide text-left">
                Kanbans </h3>

            <div class="my-2 -mx-3 animate-pulse" v-if="loadingBoard">
                <span class="text-sm font-medium text-gray-700  px-3  py-2 ">Loading ...</span>
            </div>

            <div class="my-2 -mx-3 pb-2 ">
                <template v-for="(board, boardIndex) in boards">
                    <router-link :key="boardIndex"
                                 :to="{ path: '/kanban/board', query: { id: board.id } }"
                                 class="flex justify-between items-center px-3 py-2 hover:bg-gray-200 rounded-lg">
                        <span class="text-sm font-medium text-gray-700">{{ board.name }}</span>
                    </router-link>
                </template>
            </div>

            <hr/>

            <h3 class="mt-8 text-sm font-semibold text-gray-600 uppercase tracking-wide text-left">
                User </h3>

            <div class=" -mx-3 pb-2">
                <router-link :to="{ path: '/kanban/user-profile' }"
                             class="flex justify-between items-center px-3 py-2 hover:bg-gray-200 rounded-lg">
                    <span class="text-sm font-medium text-gray-900">User Profile</span>
                </router-link>
            </div>

            <hr/>
            
            <div class="space-y-2 mt-8 ">
                <a href="/"
                   class="text-sm font-medium text-gray-600 hover:text-gray-800 transition duration-300 ease-in-out focus:outline-none">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="ml-1 font-bold">Exit XKANBAN</span>
                </a>
            </div>
        </nav>
    </div>

</template>
<script>
import {ajaxCalls} from "../../mixins/ajaxCallsMixin";

export default {
    inject: ["eventHub"],
    mixins: [ajaxCalls],

    data() {
        return {
            boards: {},
            loadingBoard: false,
            notifNumber: 0,
        };
    }, mounted() {
        this.getBoards();
        this.getNotificationCount();
    },

    created() {
        this.eventHub.$on("update-side-bar", () => {
            this.getBoards();
        });
        this.eventHub.$on("update-notif-count", () => {
            this.getNotificationCount();
        });
    },

    methods: {
        getBoards() {
            this.loadingBoard = true;
            this.asyncGetBoards().then((data) => {
                this.boards = data.data;
                this.loadingBoard = false;
            }).catch(res => {
                console.log(res)
            });
        },
        getNotificationCount() {
            this.asyncGetNotificationCount().then((data) => {
                this.notifNumber = data.data;
            }).catch(res => {
                console.log(res)
            });
        }
    },
};
</script>
