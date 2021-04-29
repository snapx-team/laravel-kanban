<template>
    <div class="flex" id="app">
        <div class="flex min-h-screen">
            <side-bar></side-bar>
        </div>
        <div class="relative min-w-screen border-r flex-grow overflow-x-auto"
             v-bind:class="{ 'overflow-hidden': kanbanIsLoading }">
            <transition enter-active-class="transition duration-500 ease-out transform"
                        enter-class=" opacity-0 bg-blue-200"
                        leave-active-class="transition duration-300 ease-in transform"
                        leave-to-class="opacity-0 bg-blue-200">
                <div class="z-50 overflow-auto h-screen absolute inset-0 bg-gray-400 bg-opacity-75 flex"
                     v-if="kanbanIsLoading">
                    <loading-animation :size="100" class="m-auto"></loading-animation>
                </div>
            </transition>
            <router-view></router-view>
        </div>

    </div>
</template>

<script>
    import LoadingAnimation from "./components/global/LoadingAnimation.vue";
    import SideBar from "./components/global/SideBar.vue";
    import Vue from "vue";

    export default {
        name: "App",
        components: {
            LoadingAnimation,
            SideBar,
        },
        data() {
            return {
                eventHub: new Vue(),
                kanbanIsLoading: false,
                kanban: {},
            };
        },
        provide() {
            return {
                eventHub: this.eventHub,
            };
        },
        created() {
            this.eventHub.$on("set-loading-state", (state) => {
                this.kanbanIsLoading = state;
            });
        },

        beforeDestroy(){
            this.eventHub.$off('set-loading-state');
        },
    };
</script>
