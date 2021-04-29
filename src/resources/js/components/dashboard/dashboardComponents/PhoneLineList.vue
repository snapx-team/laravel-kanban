<template>
    <div class="phoneLines">
        <h2 class="text-2xl font-semibold py-5">Phone Lines</h2>

        <div class="bg-gray-100 py-3">
            <template v-for="(phoneLineGroup, phoneLinesGroupIndex) in phoneLinesGroup">
                <div :key="phoneLinesGroupIndex" >
                    <p class="pl-5 pt-4 font-semibold  text-gray-500 leading-5">{{phoneLinesGroupIndex}}</p>

                    <div class="p-2 flex flex-wrap">

                        <template v-for="(phoneLine, phoneLineIndex) in phoneLineGroup">

                            <div :key="phoneLineIndex" class="flex-auto rounded shadow-lg m-2" :class="phoneLine.is_active ?  'bg-white' :'bg-red-50 border-2 border-red-300'">
                                <div class="flex p-4">
                                    <div class="pr-1 flex-1">
                                        <h5 class="font-semibold text-md uppercase text-gray-800">{{ phoneLine.name }}
                                            <a @click="editPhoneLine(phoneLine)"
                                               class="cursor-pointer px-2 text-gray-400 hover:text-gray-600 transition duration-300 ease-in-out focus:outline-none">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </h5>

                                        <span class="text-gray-500 font-bold text-sm">{{ phoneLine.phone }}</span>

                                        <p class="text-sm text-gray-500 mt-4 font-medium">
                                    <span class="mr-2 text-green-700">
                                        <i class="fas fa-user"></i>
                                        {{ phoneLine.members.length }}
                                    </span>

                                            <router-link :to="{path: '/kanban/phoneline',query: { id: phoneLine.id }}">
                                                <span class="whitespace-no-wrap underline text-blue-600 hover:text-blue-800">Navigate to phone line</span>
                                            </router-link>
                                        </p>

                                    </div>
                                    <div class="relative w-auto pl-4 flex-initial">
                                        <avatar :name="phoneLine.name" :size="12" class="mr-3"></avatar>
                                    </div>
                                </div>
                            </div>
                        </template>

                    </div>

                </div>
            </template>

        </div>
    </div>
</template>

<script>
    import Avatar from "../../global/Avatar.vue";

    export default {
        inject: ["eventHub"],
        components: {
            Avatar,
        },

        props: {
            phoneLines: {
                type: Array,
                default: null,
            },
        },

        computed: {
            phoneLinesGroup() {
                return this.phoneLines.reduce((res, curr) => {
                    if (res[curr.tag])
                        res[curr.tag].push(curr);
                    else
                        Object.assign(res, {[curr.tag]: [curr]});
                    return res;
                }, {});
            },
        },

        methods: {
            editPhoneLine(phoneLine) {
                this.eventHub.$emit("create-phone-line", phoneLine);
            },
        },
    };
</script>


