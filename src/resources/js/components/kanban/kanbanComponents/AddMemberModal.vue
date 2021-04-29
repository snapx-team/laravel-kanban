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
                     style="width: 700px; min-height: 300px; max-height: 80%">
                    <!-- Task heading -->
                    <div class="flex justify-between p-5 bg-indigo-800 border-b">
                        <div class="space-y-1">
                            <h1 class="text-2xl text-white pb-2">Add Members</h1>
                            <p class="text-sm font-medium leading-5 text-gray-500">
                                Adding employees to this phone line </p>
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
                    <!-- Task container -->
                    <form class="space-y-6 overflow-auto px-8 py-6">
                        <!-- Flow options -->
                        <div class="space-y-6">
                            <div class="space-y-4">
                                <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">
                                    Employees
                                </span>
                                <vSelect :options="allUsers"
                                         class="text-gray-400"
                                         label="name"
                                         multiple
                                         placeholder="Select Members"
                                         style="margin-top: 7px"
                                         v-model="selectedMembers">
                                    <template slot="option" slot-scope="option">
                                        <avatar :name="option.name" :size="4" class="mr-3 m-1 float-left"></avatar>
                                        <p class="inline">{{ option.name }}</p>
                                    </template>
                                    <template #no-options="{ search, searching, loading }">
                                        No result .
                                    </template>
                                </vSelect>
                            </div>
                        </div>
                        <div class="w-full grid sm:grid-cols-2 gap-3 sm:gap-3">
                            <button @click="modalOpen = false"
                                    class="px-4 py-3 border border-gray-200 rounded text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-600 transition duration-300 ease-in-out"
                                    type="button">
                                Cancel
                            </button>
                            <button @click="saveMember()"
                                    class="px-4 py-3 border border-transparent rounded text-white bg-indigo-600 hover:bg-indigo-500 transition duration-300 ease-in-out"
                                    type="button">
                                Add Members
                            </button>
                        </div>
                        <figure class="bg-gray-100 rounded-xl overflow-y-auto">
                            <div class="pt-6 md:p-8 text-center md:text-left space-y-4">
                                <figcaption class="font-medium">
                                    <p class="pb-5 text-gray-500">
                                        There are a total of {{ kanbanData.members.length }} members on this phone
                                        line </p>

                                    <label class="block pb-5">
                                        <input class="px-3 py-3 placeholder-gray-400 text-gray-700 rounded border border-gray-400 w-full pr-10 outline-none text-md leading-4"
                                               placeholder="Filter members"
                                               type="text"
                                               v-model="filter"/> </label>

                                    <p class="text-gray-900" v-if="filtered.length === 0">
                                        No matching results </p>

                                    <template v-for="(member, memberIndex) in filtered">
                                        <div :key="memberIndex"
                                             class="flex justify-between items-center border-b p-1">
                                            <div class="flex items-center">
                                                <avatar :name="member.employee.name" :size="6" class="mr-3"></avatar>
                                                <span class="py-2 mr-3 text-gray-600">{{member.employee.name}}</span>
                                            </div>
                                            <a @click="removeMember(member)" class="cursor-pointer text-gray-400 text-sm hover:text-gray-600 transition duration-300 ease-in-out">remove</a>
                                        </div>
                                    </template>
                                </figcaption>
                            </div>
                        </figure>
                    </form>
                </div>
            </div>
        </transition>
    </div>
</template>
<script>
    import vSelect from "vue-select";
    import Avatar from "../../global/Avatar.vue";
    import {ajaxCalls} from "../../../mixins/ajaxCallsMixin";

    export default {
        inject: ["eventHub"],
        components: {
            vSelect,
            Avatar,
        },
        mixins: [ajaxCalls],

        props: {
            kanbanData: Object,
        },
        data() {
            return {
                filter: "",
                modalOpen: false,
                isSavingMember: false,
                kanbanID: null,
                selectedMembers: null,
                allUsers: [],
            };
        },
        created() {
            this.eventHub.$on("add-member", () => {
                this.modalOpen = true;
            });
        },

        beforeDestroy(){
            this.eventHub.$off('add-member');
        },

        mounted(){
            this.asyncGetEmployees().then((data) => {
                this.allUsers = data.data;
            }).catch(res => {console.log(res)});
        },

        computed: {
            filtered() {
                const regex = new RegExp(this.filter, "i");
                return this.kanbanData.members.filter((e) => {
                    return !this.filter || e.name.match(regex);
                });
            },
        },
        methods: {
            saveMember() {
                this.eventHub.$emit("save-members", this.selectedMembers);
                this.modalOpen = false;
                this.selectedMembers = null;
            },
            removeMember($memberData) {
                this.eventHub.$emit("remove-member", $memberData);
            },
        },
    };
</script>
