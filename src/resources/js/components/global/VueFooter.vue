<template>
    <footer class="w-full bg-gray-700 pb-6">
        <div class="container mx-auto px-4">
            <hr class="mb-6 border-b-1 border-gray-500"/>
            <div class="flex space-x-8 text-center">
                    <div class="flex-1 text-gray-100 font-semibold py-1">
                        Copyright © {{year}}
                        <a href="/" class="text-white hover:text-gray-400 text-sm font-semibold py-1">{{ parent_name }}</a>
                        • {{ version }}
                    </div>
            </div>
        </div>
    </footer>
</template>
<script>
import axios from "axios";

export default {
    data() {
        return {
            year: null,
            version: null,
            parent_name: null
        }
    },

    mounted() {
        axios.get('get-footer-info').then((data) => {
            this.parent_name = data.data['parent_name']
            this.year = data.data['year'];
            this.version = data.data['version'];

            console.log('test');
            console.log(data);


        }).catch(res => {
            console.log(res);
        });
    },
}
</script>
