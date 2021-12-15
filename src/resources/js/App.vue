<template>
    <div>
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
        <vue-footer></vue-footer>
    </div>
</template>

<script>
import LoadingAnimation from "./components/global/LoadingAnimation.vue";
import SideBar from "./components/global/SideBar.vue";
import Vue from "vue";
import Footer from "./components/global/VueFooter";
import VueFooter from "./components/global/VueFooter";
import axios from "axios";

export default {
    name: "App",
    components: {
        VueFooter,
        Footer,
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

    methods: {
        setSessions() {
            axios.get('set-sessions').then((data) => {
                if (data.status === 200){
                    if(!data.data['is_logged_in']){

                        this.triggerErrorToast('IS NOT LOGGED IN');
                        console.log('IS NOT LOGGED IN')
                        console.log(data.data);

                        // location.reload();
                    }
                }
            });
        }
    },
    provide() {
        return {
            eventHub: this.eventHub,
        };
    },
    mounted(){
        this.$crontab.addJob({
            name: 'setSessions',
            interval: {
                seconds: '/10',
            },
            job: this.setSessions
        });

    },
    created() {
        this.eventHub.$on("set-loading-state", (state) => {
            this.kanbanIsLoading = state;
        });
    },

    beforeDestroy() {
        this.eventHub.$off('set-loading-state');
    },
};
</script>
