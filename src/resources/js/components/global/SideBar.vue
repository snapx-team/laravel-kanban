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
            <div class="my-2 -mx-3 pb-2">
                <router-link :to="{ path: '/kanban' }"
                             class="flex justify-between items-center px-3 py-2 hover:bg-gray-200 rounded-lg">
                    <span class="text-sm font-medium text-gray-900">Dashboard</span>
                </router-link>
            </div>

            <hr/>

            <h3 class="mt-8 text-sm font-semibold text-gray-600 uppercase tracking-wide text-left">
                Kanbans </h3>

            <div class="my-2 -mx-3 animate-pulse" v-if="loadingPhoneLine">
                <span class="text-sm font-medium text-gray-700  px-3  py-2 ">Loading ...</span>
            </div>

            <div class="my-2 -mx-3 pb-2 ">
                <template v-for="(phoneLine, phoneLineIndex) in phoneLines">
                    <router-link :key="phoneLineIndex"
                                 :to="{ path: '/kanban/phoneline', query: { id: phoneLine.id } }"
                                 class="flex justify-between items-center px-3 py-2 hover:bg-gray-200 rounded-lg">
                        <span class="text-sm font-medium text-gray-700">{{ phoneLine.name }}</span>
                    </router-link>
                </template>
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
    import {getBoards} from "../../api";

    export default {
        inject: ["eventHub"],

        data() {
            return {
                phoneLines: {},
                loadingPhoneLine: false
            };
        }, mounted() {
            this.getBoards();
        },

        created() {
            this.eventHub.$on("update-side-bar", () => {
                this.getBoards();
            });
        },

        methods: {
            getBoards() {
                this.loadingPhoneLine = true;
                getBoards().then((data) => {
                    this.phoneLines = data.data;
                    this.loadingPhoneLine = false;
                }).catch(res => {console.log(res)});
            },
        },
    };
</script>
