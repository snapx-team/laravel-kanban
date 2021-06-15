<template>
    <div>
        
        <pie-chart v-if="badgeData != null" :series="badgeData.hits" :labels="badgeData.names"></pie-chart>
        <pie-chart v-if="ticketByEmployeeData != null" :series="ticketByEmployeeData.hits" :labels="ticketByEmployeeData.names"></pie-chart>

    </div>
</template>

<script>  
import {ajaxCalls} from "../../mixins/ajaxCallsMixin";
import PieChart from  "./metricsComponents/PieChart.vue";

export default {
    inject: ["eventHub"],
    components: {
        PieChart
    },
    data() {
        return {
            badgeData: null,
            ticketByEmployeeData: null
        }
    },

    mixins: [ajaxCalls],

    mounted() {
        this.getBadgeData();
        this.getTicketsByEmployee();
    },
    methods: {
        getBadgeData() {
            this.asyncGetBadgeData().then((data) => {
                console.log(data.data);
                this.badgeData = data.data;
            }).catch(res => {console.log(res)});
        },
        getTicketsByEmployee() {
            this.asyncGetTicketsByEmployee().then((data) => {
                console.log(data.data);
                this.ticketByEmployeeData = data.data
            })
        }
    }
}
</script>
