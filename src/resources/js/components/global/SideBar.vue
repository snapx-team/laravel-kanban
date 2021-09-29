<template>
    <div class="bg-gray-100 overflow-hidden shadow-lg h-full transition duration-150 ease-in-out "
         :class=" !isSideBarOpen ? 'w-20': 'w-64' ">

        <div class="text-center text-white font-semibold flex justify-between items-center relative z-10"
             :class="`bg-${erpVersion.color}-700`">
            <p v-if="isSideBarOpen" class="px-8"> {{ erpVersion.name }}</p>
            <div @click="toggleSidebar()"
                 class="p-4 cursor-pointer transition duration-300 ease-in-out rounded"
                 :class="[`hover:bg-${erpVersion.color}-800 hover:text-${erpVersion.color}-200`, { 'flex-auto': !isSideBarOpen  }]">
                <i class="fas fa-bars"></i>
            </div>
        </div>

        <div class="transition duration-300 ease-in-out transform absolute z-0"
             :class=" isErpLinksOpen ? '': '-translate-y-44' "
             style="width: inherit;">
            <div v-for="version in erpVersions"
                 v-if="version.subdomain !== subdomain && version.subdomain !== 'schedule-qa'"
                 class="text-center text-white font-semibold flex justify-between items-center"
                 :class="`bg-${version.color}-700`">
                <p v-if="isSideBarOpen" class="px-8"> {{ version.name }}</p>
                <a :href="version.url"
                     class="p-4  cursor-pointer transition duration-300 ease-in-out rounded"
                     :class="[`hover:bg-${version.color}-800 hover:text-${version.color}-200`, { 'flex-auto': !isSideBarOpen  }]">
                    <div v-if="isSideBarOpen"><i class="fas fa-angle-double-right"></i></div>
                    <p v-else>{{ version.shortName }}</p>
                </a>
            </div>

            <div
                @click="isErpLinksOpen = !isErpLinksOpen"
                class=" w-16 m-auto rounded-b-xl border-t-2 shadow text-center text-white text-lg cursor-pointer "
                :class="`border-${erpVersion.color}-500 bg-${erpVersion.color}-400 hover:bg-${erpVersion.color}-500 hover:text-${erpVersion.color}-100`">
                <div class=" transition duration-300 ease-in-out "
                     :class=" { 'transform rotate-180 ' : isErpLinksOpen }"><i class="fas fa-angle-double-down"></i>
                </div>
            </div>
        </div>

        <div class="py-4 transition duration-150 ease-in-out mt-3"
             :class=" !isSideBarOpen ? 'px-0': 'px-8' ">
            <div class="text-center">
                <i class="fas fa-th fa-3x p-3 text-indigo-800"></i>
                <div v-if="isSideBarOpen">
                    <h1 class="text-3xl tracking-wide text-indigo-800 font-bold my-0">
                        KANYEBAN </h1>
                    <p class="text-xs text-indigo-800">
                        The Greatest Kanban Of All Time {{ msg }}</p>

                </div>
            </div>

            <nav class="mt-8">
                <h3 class="font-semibold text-gray-600 uppercase tracking-wide"
                    :class=" !isSideBarOpen ? 'text-center text-xs': 'text-left text-sm' ">General </h3>
                <div class="mt-2 -mx-3 pb-2">
                    <router-link :to="{ path: '/kanban' }"
                                 class="flex items-center px-3 py-2 rounded-lg"
                                 :class="!isSideBarOpen ? 'mx-6 bg-gray-600 hover:bg-gray-500 justify-center': 'justify-between hover:bg-gray-200' ">
                        <span v-if="isSideBarOpen" class="text-sm font-medium text-gray-900"><i
                            class="fas fa-briefcase mr-2"></i>Dashboard</span>
                        <div v-else>
                            <i class="text-white m-2 fas fa-briefcase"></i>
                        </div>
                    </router-link>
                </div>
                <div class=" -mx-3 pb-2">
                    <router-link :to="{ path: '/kanban/notifications' }"
                                 class="flex items-center px-3 py-2 rounded-lg"
                                 :class="!isSideBarOpen ? 'mx-6 bg-gray-600 hover:bg-gray-500 justify-center' : 'justify-between hover:bg-gray-200' ">
                        <span v-if="isSideBarOpen" class="text-sm font-medium text-gray-900"><i
                            class="mr-2 fas fa-bell"></i>Notifications</span>
                        <div v-else><i class="text-white m-2 fas fa-bell"></i></div>
                        <div v-if="this.notifNumber > 0"
                             class="py-4 pr-4 relative border-2 border-transparent text-gray-800 rounded-full hover:text-gray-400 focus:outline-none focus:text-gray-500 transition duration-150 ease-in-out">
                        <span class="absolute inset-0 object-right-top pt-0.5">
                            <div
                                class="inline-flex items-center px-2 py-0.5 border-2 rounded-full text-xs font-semibold leading-4 bg-red-500 text-white">
                            {{ notifNumber }}
                            </div>
                        </span>
                        </div>
                    </router-link>

                </div>

                <div class=" -mx-3 pb-2">
                    <router-link :to="{ path: '/kanban/backlog' }"
                                 class="flex items-center px-3 py-2 rounded-lg"
                                 :class="!isSideBarOpen ? 'mx-6 bg-gray-600 hover:bg-gray-500 justify-center' : 'justify-between hover:bg-gray-200' ">
                        <span v-if="isSideBarOpen" class="text-sm font-medium text-gray-900"><i
                            class="mr-2 fas fa-archive"></i>Backlog</span>
                        <div v-else><i class="text-white m-2 fas fa-archive"></i></div>
                    </router-link>
                </div>

                <div class=" -mx-3 pb-2" v-if="$role === 'admin'">
                    <router-link :to="{ path: '/kanban/metrics' }"
                                 class="flex items-center px-3 py-2 rounded-lg"
                                 :class="!isSideBarOpen ? 'mx-6 bg-gray-600 hover:bg-gray-500 justify-center' : 'justify-between hover:bg-gray-200' ">
                        <span v-if="isSideBarOpen" class="text-sm font-medium text-gray-900"><i
                            class="mr-2 fas fa-chart-pie"></i>Metics</span>
                        <div v-else><i class="text-white m-2 fas fa-chart-pie"></i></div>
                    </router-link>
                </div>

                <div class="-mx-3 pb-2">
                    <router-link :to="{ path: '/kanban/user-profile' }"
                                 class="flex items-center px-3 py-2 rounded-lg"
                                 :class="!isSideBarOpen ? 'mx-6 bg-gray-600 hover:bg-gray-500 justify-center' : 'justify-between hover:bg-gray-200' ">
                        <span v-if="isSideBarOpen" class="text-sm font-medium text-gray-900"><i
                            class="mr-2 fas fa-user-cog"></i>User Profile</span>
                        <div v-else><i class="text-white m-2 fas fa-user-cog"></i></div>
                    </router-link>
                </div>

                <hr/>

                <h3 class="mt-8 font-semibold text-gray-600 uppercase tracking-wide "
                    :class=" !isSideBarOpen ? 'text-center text-xs': 'text-left text-sm' ">
                    Kanbans </h3>

                <div class="my-2 -mx-3 animate-pulse" v-if="loadingBoard">
                    <span class="text-sm font-medium text-gray-700  px-3  py-2 ">Loading ...</span>
                </div>

                <div class="my-2 -mx-3 pb-2 ">
                    <template v-for="(board, boardIndex) in boards">
                        <router-link :key="boardIndex"
                                     :to="{ path: '/kanban/board', query: { id: board.id } }"
                                     class="flex items-center px-3 rounded-lg my-2"
                                     :class="!isSideBarOpen ? 'justify-center': 'justify-between  hover:bg-gray-200 py-2' ">
                            <span v-if="isSideBarOpen" class="text-sm font-medium text-gray-900">{{ board.name }}</span>
                            <avatar v-else :name="board.name" :size="12" :tooltip="true"></avatar>
                        </router-link>
                    </template>
                </div>

                <hr/>

                <div class="space-y-2 mt-8 " :class=" !isSideBarOpen ? 'text-center text-xl': 'text-left text-sm' ">
                    <a href="/"
                       class=" font-medium text-gray-600 hover:text-gray-800 transition duration-300 ease-in-out focus:outline-none">
                        <i class="fas fa-sign-out-alt"></i>
                        <span v-if="isSideBarOpen" class="ml-1 font-bold">EXIT KANYEBAN</span>
                    </a>
                </div>
            </nav>
        </div>
    </div>
</template>
<script>
import {ajaxCalls} from "../../mixins/ajaxCallsMixin";
import Avatar from "../global/Avatar.vue";

export default {
    inject: ["eventHub"],
    mixins: [ajaxCalls],

    components: {
        Avatar,
    },

    data() {
        return {
            boards: {},
            loadingBoard: false,
            notifNumber: 0,
            isSideBarOpen: true,
            subdomain: null,
            isErpLinksOpen: false,
            erpVersions: [
                {
                    color: 'gray',
                    subdomain: 'schedule',
                    name: 'Xguard Security',
                    shortName: 'XG',
                    url: 'https://schedule.xguard.ca/kanban'
                },
                {

                    color: 'blue',
                    subdomain: 'ontario',
                    name: 'Xguard Ontario',
                    shortName: 'ON',
                    url: 'https://ontario.xguard.ca/kanban'
                },
                {
                    color: 'yellow',
                    subdomain: 'signaleurs',
                    name: 'Xguard Signaleurs',
                    shortName: 'XS',
                    url: 'https://signaleurs.xguard.ca/kanban'
                },
                {
                    color: 'purple',
                    subdomain: 'schedule-qa',
                    name: 'Quality Assurance',
                    shortName: 'QA',
                    url: 'https://schedule-qa.xguard.ca/kanban'
                },

            ]
        };
    }, mounted() {
        this.getBoards();
        this.getNotificationCount();
        this.getSubDomain();

        if(this.$device.mobile || this.$device.ios || this.$device.android || window.innerWidth < 1200){
            this.isSideBarOpen = false;
        }
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
        },

        toggleSidebar() {
            this.isSideBarOpen = !this.isSideBarOpen;
        },

        getSubDomain() {
            let host = window.location.host
            this.subdomain = host.split('.')[0]
        }
    },

    computed: {
        erpVersion() {

            this.erpVersions.forEach(version => {
                if (version.subdomain === this.subdomain) {
                    return version;
                }
            });

            return {
                color: 'red',
                subdomain: 'none',
                name: 'Xguard Default',
                shortNmae: 'XD',
                url: '/kanban'
            }
        }
    }
};
</script>
